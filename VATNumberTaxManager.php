<?php
/*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class VATNumberTaxManager implements TaxManagerInterface
{
	public static function isAvailableForThisAddress(Address $address)
	{
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

		static $cached_address = NULL;

		if ($address->id_customer != NULL) {
			$cached_address = $address;
		}

		// Now, check on the cached address object
		return (!empty($cached_address->vat_number)
		    && !empty($cached_address->id_country)
		    && $cached_address->id_country != Configuration::get('VATNUMBER_COUNTRY')
		    && Configuration::get('VATNUMBER_MANAGEMENT')
		);
	}

	public function getTaxCalculator()
	{
		// If the address matches the european vat number criterias no taxes are applied
		$tax = new Tax();
		$tax->rate = 0;

		return new TaxCalculator(array($tax));
	}
}
