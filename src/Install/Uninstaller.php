<?php
declare(strict_types = 1);

namespace Ebiggio\CustomerDNI\Install;

use Ebiggio\CustomerDNI\Config\ModuleSettings;

use Configuration;

class Uninstaller
{
    /**
     * Performs the uninstallation process, deleting the module's configuration settings.
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        foreach (ModuleSettings::SETTINGS as $settingName => $settingValue) {
            if ( ! Configuration::deleteByName($settingName)) {
                return false;
            }
        }

        return true;
    }
}