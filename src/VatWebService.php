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
use DragonBe\Vies\ViesServiceException;
use Module;
use PrestaShopLogger;

/**
 * Used to check VatNumber using a web service
 */
class VatWebService
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
     * @return bool
     */
    public function isAvailable()
    {
        return $this->vies->getHeartBeat()->isAlive();
    }

    /**
     * Validates a given country code and VAT number
     *
     * @param string $countryCode The two-character country code of a European member country
     * @param string $vatNumber The VAT number (without the country identification) of a registered company
     *
     * @return bool
     */
    public function validate($countryCode, $vatNumber)
    {
        if (false === $this->isAvailable()) {
            $this->logWebServiceUnavailable($countryCode, $vatNumber);

            return true;
        }

        try {
            $vatResult = $this->vies->validateVat($countryCode, $vatNumber);
        } catch (ViesException $viesException) {
            return false;
        } catch (ViesServiceException $viesServiceException) {
            $this->logWebServiceUnavailable($countryCode, $vatNumber);

            return true;
        }

        return $vatResult->isValid();
    }

    /**
     * @param string $countryCode
     * @param string $vatNumber
     */
    private function logWebServiceUnavailable($countryCode, $vatNumber)
    {
        PrestaShopLogger::addLog(
            sprintf(
                'VAT number %s%s cannot be validated through web service due to temporary unavailability.',
                $countryCode,
                $vatNumber
            ),
            2,
            0,
            'Module',
            Module::getModuleIdByName('vatnumber'),
            true
        );
    }
}
