<?php
declare(strict_types = 1);

namespace CustomerDNI\Database;

use CustomerDNI\Config\ModuleSettings;

use Db;
use Configuration;

class Install
{
    public function run(): bool
    {
        return $this->installTables()
            && $this->installConfiguration();
    }

    private function installTables(): bool
    {
        $query = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'customer_dni` (
            `id_customer` int(10) unsigned NOT NULL,
            `dni` varchar(255) NOT NULL,
            PRIMARY KEY  (`id_customer`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4';

        if ( ! Db::getInstance()->execute($query)) {
            return false;
        }

        return true;
    }

    private function installConfiguration(): bool
    {
        foreach (ModuleSettings::SETTINGS as $settingName => $settingValue) {
            if ( ! Configuration::updateValue($settingName, $settingValue)) {
                return false;
            }
        }

        return true;
    }
}

