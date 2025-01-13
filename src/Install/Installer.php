<?php
declare(strict_types = 1);

namespace Ebiggio\CustomerDNI\Install;

use Ebiggio\CustomerDNI\Database\Installer as DatabaseInstaller;
use Ebiggio\CustomerDNI\Config\ModuleSettings;

use Module;
use Configuration;

class Installer
{
    public function install(Module $module): bool
    {
        if ( ! $this->registerPrestaShopHooks($module)) {
            return false;
        }

        if ( ! $this->registerCustomHooks($module)) {
            return false;
        }

        if ( ! (new DatabaseInstaller())->install()) {
            return false;
        }

        if ( ! $this->saveDefaultSettings()) {
            return false;
        }

        return true;
    }

    /**
     * Registers the PrestaShop hooks that this module uses.
     *
     * @param Module $module The module instance.
     *
     * @return bool Whether the hooks were registered successfully.
     */
    private function registerPrestaShopHooks(Module $module): bool
    {
        $hooks = [
            'actionCustomerGridDefinitionModifier',
            'actionCustomerGridQueryBuilderModifier',
            'actionCustomerFormBuilderModifier',
            'actionAfterCreateCustomerFormHandler',
            'actionAfterUpdateCustomerFormHandler',
            'actionObjectCustomerDeleteAfter',
            'actionObjectAddressAddBefore', // See note 1.
            'actionObjectAddressUpdateBefore', // See note 1.
            'additionalCustomerFormFields',
            'validateCustomerFormFields',
            'actionCustomerAccountAdd',
        ];

        /*
         * 1. We hook into this action instead of the FormHandler because we don't need to validate the DNI field at this point,
         * and because an address can be added or updated from multiple places (e.g., front office, back office, API, another module, etc.).
         * While this is also true for when a customer is created or updated from a place that doesn't involve the customer form handler,
         * (for example, through the API), those use cases are out of the scope of this module.
         */

        return $module->registerHook($hooks);
    }

    /**
     * Registers the custom hooks that this module provides.
     *
     * @param Module $module The module instance.
     *
     * @return bool Whether the hooks were registered successfully.
     */
    private function registerCustomHooks(Module $module): bool
    {
        $hooks = [
            'actionCustomerDNIAddAfter', // Fires after a DNI is associated with a customer, whether it's added or updated.
            'actionCustomerDNIDeleteAfter', // Fires after a DNI is deleted from the database (i.e., when a customer is deleted).
        ];

        return $module->registerHook($hooks);
    }

    /**
     * Saves the default settings for the module.
     *
     * @return bool Whether the default settings were saved successfully.
     */
    private function saveDefaultSettings(): bool
    {
        foreach (ModuleSettings::SETTINGS as $settingName => $settingValue) {
            if ( ! Configuration::updateValue($settingName, $settingValue)) {
                return false;
            }
        }

        return true;
    }
}