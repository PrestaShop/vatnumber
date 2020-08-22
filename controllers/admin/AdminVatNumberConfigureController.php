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
class AdminVatNumberConfigureController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'Configuration';
        $this->table = 'configuration';

        parent::__construct();

        if (Configuration::get(VatNumber::CONFIGURATION_KEY_VATNUMBER_WEBSERVICE_ENABLED)) {
            $this->displayWebServiceAvailability();
        }

        $this->fields_options = [
            'products' => [
                'title' => $this->module->displayName,
                'fields' => [
                    VatNumber::CONFIGURATION_KEY_VATNUMBER_COUNTRY => [
                        'title' => $this->module->l('Always add VAT for customers from:'),
                        'desc' => $this->module->l('In EU legislation, this should be the country where the business is located, usually your own country.'),
                        'validation' => 'isInt',
                        'cast' => 'intval',
                        'required' => false,
                        'type' => 'select',
                        'defaultValue' => (int) Configuration::get('PS_COUNTRY_DEFAULT'),
                        'list' => $this->getCountries(),
                        'identifier' => 'value',
                    ],
                    VatNumber::CONFIGURATION_KEY_VATNUMBER_WEBSERVICE_ENABLED => [
                        'title' => $this->module->l('Validate through web service'),
                        'desc' => $this->module->l('Using the European Commission web service to validate VAT number when available. The SOAP extension must be enable on your server.'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'required' => false,
                        'type' => 'bool',
                        'disabled' => !extension_loaded('soap'),
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getCountries()
    {
        $countries = Country::getCountries($this->context->language->id);

        $list = [];

        foreach ($countries as $country) {
            $list[] = [
                'value' => $country['id_country'],
                'name' => $country['name'],
            ];
        }

        return $list;
    }

    /**
     * Display VatWebService status
     */
    private function displayWebServiceAvailability()
    {
        if ($this->isWebServiceAvailable()) {
            $this->confirmations[] = $this->l('VAT Number validation using webservice is currently available.');
        } else {
            $this->warnings[] = $this->l('VAT Number validation using webservice is currently unavailable.');
        }
    }

    /**
     * @return bool
     */
    private function isWebServiceAvailable()
    {
        try {
            /** @var \PrestaShop\Module\VatNumber\VatWebService $vatWebService */
            $vatWebService = $this->module->get('prestashop.module.vatnumber.webservice');
        } catch (Exception $exception) {
            return false;
        }

        return $vatWebService->isAvailable();
    }
}
