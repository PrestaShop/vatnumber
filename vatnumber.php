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
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class VatNumber extends TaxManagerModule
{
    /**
     * List of hooks used
     */
    const HOOKS = [
        'actionValidateCustomerAddressForm',
    ];

    /**
     * Name of ModuleAdminController used for configuration
     */
    const MODULE_ADMIN_CONTROLLER = 'AdminVatNumberConfigure';

    /**
     * Configuration key used
     */
    const CONFIGURATION_KEY_VATNUMBER_COUNTRY = 'VATNUMBER_COUNTRY';
    const CONFIGURATION_KEY_VATNUMBER_WEBSERVICE_ENABLED = 'VATNUMBER_CHECKING';
    const CONFIGURATION_KEY_VATNUMBER_ENABLED = 'VATNUMBER_MANAGEMENT';

    public function __construct()
    {
        $this->name = 'vatnumber';
        $this->tab = 'billing_invoicing';
        $this->version = '3.0.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;
        $this->tax_manager_class = 'VATNumberTaxManager';

        parent::__construct();

        $this->displayName = $this->l('European VAT number');
        $this->description = $this->l('Enables you to enter the intra-community VAT number when creating the address. You must fill in the company field to allow entering the VAT number.');
        $this->ps_versions_compliancy = [
            'min' => '1.7.7.0', // Due to PHP 7.1 requirement for dependency
            'max' => _PS_VERSION_,
        ];
    }

    /**
     * @return bool
     */
    public function install()
    {
        return parent::install()
            && Configuration::updateValue(static::CONFIGURATION_KEY_VATNUMBER_ENABLED, 1)
            && Configuration::updateValue(static::CONFIGURATION_KEY_VATNUMBER_WEBSERVICE_ENABLED, 0)
            && Configuration::updateValue(static::CONFIGURATION_KEY_VATNUMBER_COUNTRY, (int) Configuration::get('PS_COUNTRY_DEFAULT'))
            && $this->installTabs()
            && $this->registerHook(static::HOOKS);
    }

    /**
     * @return bool
     */
    public function installTabs()
    {
        if (Tab::getIdFromClassName(static::MODULE_ADMIN_CONTROLLER)) {
            return true;
        }

        $tab = new Tab();
        $tab->class_name = static::MODULE_ADMIN_CONTROLLER;
        $tab->module = $this->name;
        $tab->active = true;
        $tab->id_parent = -1;
        $tab->name = array_fill_keys(
            Language::getIDs(false),
            $this->displayName
        );

        return $tab->add();
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallTabs()
            && Configuration::updateValue(static::CONFIGURATION_KEY_VATNUMBER_ENABLED, 0);
    }

    /**
     * @return bool
     */
    public function uninstallTabs()
    {
        $id_tab = (int) Tab::getIdFromClassName(static::MODULE_ADMIN_CONTROLLER);

        if ($id_tab) {
            $tab = new Tab($id_tab);

            return $tab->delete();
        }

        return true;
    }

    /**
     * @param bool $force_all
     *
     * @return bool
     */
    public function enable($force_all = false)
    {
        $success = parent::enable($force_all);

        Configuration::updateValue(static::CONFIGURATION_KEY_VATNUMBER_ENABLED, 1);

        return $success;
    }

    /**
     * @param bool $force_all
     *
     * @return bool
     */
    public function disable($force_all = false)
    {
        $success = parent::disable($force_all);

        Configuration::updateValue(static::CONFIGURATION_KEY_VATNUMBER_ENABLED, 0);

        return $success;
    }

    /**
     * Redirect to our ModuleAdminController when click on Configure button
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink(static::MODULE_ADMIN_CONTROLLER));
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    public function hookActionValidateCustomerAddressForm(array $params)
    {
        /** @var CustomerAddressFormCore $form */
        $form = $params['form'];
        $vatNumberField = $form->getField('vat_number');
        $vatNumber = $form->getValue('vat_number');

        if (empty($vatNumber)) {
            return true;
        }

        if (!Configuration::get(static::CONFIGURATION_KEY_VATNUMBER_ENABLED)) {
            return true;
        }

        try {
            /** @var \PrestaShop\Module\VatNumber\VatSumValidator $vatSumValidator */
            $vatSumValidator = $this->get('prestashop.module.vatnumber.vat_sum_validator');

            /** @var \PrestaShop\Module\VatNumber\VatWebService $vatWebService */
            $vatWebService = $this->get('prestashop.module.vatnumber.webservice');
        } catch (Exception $exception) {
            return true;
        }

        $vatCountry = substr($vatNumber, 0, 2);
        $vatIdentifier = substr($vatNumber, 2);

        if (false === $vatSumValidator->validate($vatCountry, $vatIdentifier)) {
            $vatNumberField->addError($this->l('This VAT number is invalid.'));

            return false;
        }

        if (!Configuration::get(static::CONFIGURATION_KEY_VATNUMBER_WEBSERVICE_ENABLED)) {
            return true;
        }

        if (false === $vatWebService->validate($vatCountry, $vatIdentifier)) {
            $vatNumberField->addError($this->l('This VAT number is invalid.'));

            return false;
        }

        return true;
    }
}
