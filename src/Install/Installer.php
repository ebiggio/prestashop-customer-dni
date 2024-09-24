<?php
declare(strict_types = 1);

namespace CustomerDNI\Install;

use CustomerDNI\Database\Install;
use CustomerDNI\Database\Uninstall;

use Module;

class Installer
{
    public function install(Module $module): bool
    {
        if ( ! $this->registerHooks($module)) {
            return false;
        }

        if ( ! $this->prepareDatabase()) {
            return false;
        }

        return true;
    }

    public function uninstall(): bool
    {
        return (new Uninstall())->run();
    }

    /**
     * Registers the module's hooks.
     *
     * @param Module $module
     *
     * @return bool
     */
    public function registerHooks(Module $module): bool
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

    public function prepareDatabase(): bool
    {
        return (new Install())->run();
    }
}