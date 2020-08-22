<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
class VATNumberTaxManager implements TaxManagerInterface
{
    /**
     * @param Address $address
     *
     * @return bool
     */
    public static function isAvailableForThisAddress(Address $address)
    {
        if (!Configuration::get(VatNumber::CONFIGURATION_KEY_VATNUMBER_ENABLED)) {
            return false;
        }

        /*
        HOTFIX

        For some reason, this check is called 6 times (?)

        1 w. the real address
        2 w.o. the real address

        1 w. the real address
        2 w.o. the real address

        => [1 0 0 1 0 0]

        So we need to filter out the weird calls...

        We do this by caching the correct calls between calls;
        by creating a static variable, which we save the address to,
        if it does not contain NULL in some of the other fields.
        */

        static $cached_address = null;

        if (null !== $address->id_customer) {
            $cached_address = $address;
        }

        // Now, check on the cached address object
        return !empty($cached_address->vat_number)
            && !empty($cached_address->id_country)
            && $cached_address->id_country != Configuration::get(VatNumber::CONFIGURATION_KEY_VATNUMBER_COUNTRY)
        ;
    }

    /**
     * @return TaxCalculator
     */
    public function getTaxCalculator()
    {
        // If the address matches the european vat number criterias no taxes are applied
        $tax = new Tax();
        $tax->rate = 0;

        return new TaxCalculator([$tax]);
    }
}
