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

namespace PrestaShop\Module\VatNumber;

use DragonBe\Vies\Vies;
use DragonBe\Vies\ViesException;

/**
 * Used to check VatNumber control sum without external call
 */
class VatSumValidator
{
    /**
     * @var Vies
     */
    public $vies;

    /**
     * @param Vies $vies
     */
    public function __construct(Vies $vies)
    {
        $this->vies = $vies;
    }

    /**
     * Validate a VAT number control sum
     *
     * @param string $countryCode The two-character country code of a European member country
     * @param string $vatNumber The VAT number (without the country identification) of a registered company
     *
     * @return bool
     */
    public function validate($countryCode, $vatNumber)
    {
        try {
            return $this->vies->validateVatSum($countryCode, $vatNumber);
        } catch (ViesException $exception) {
            return false;
        }
    }
}
