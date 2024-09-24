<?php
declare(strict_types=1);

namespace CustomerDNI\Database;

use CustomerDNI\Config\ModuleSettings;

use Configuration;

class Uninstall
{
    public function run(): bool
    {
        return $this->uninstallConfiguration();
    }

    public function uninstallConfiguration(): bool
    {
        foreach (ModuleSettings::SETTINGS as $settingName => $settingValue) {
            if ( ! Configuration::deleteByName($settingName)) {
                return false;
            }
        }

        return true;
    }
}
