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

    public function registerHooks(Module $module): bool
    {
        $hooks = [
            'actionCustomerGridDefinitionModifier',
            'actionCustomerGridQueryBuilderModifier',
            'actionCustomerFormBuilderModifier',
            'actionAfterCreateCustomerFormHandler',
            'actionAfterUpdateCustomerFormHandler'
        ];
//         && $this->registerHook('hookActionObjectCustomerDeleteAfter');

        return $module->registerHook($hooks);
    }

    public function prepareDatabase(): bool
    {
        return (new Install())->run();
    }
}