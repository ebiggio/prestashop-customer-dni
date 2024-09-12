<?php

namespace CustomerDNI\Database;

use Db;
use Configuration;

class Install
{
    public function run(): bool
    {
        return $this->installTables()
            && $this->installConfiguration();
    }

    public function installTables(): bool
    {
        $sql = [];

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'customer_dni` (
            `id_customer` int(10) unsigned NOT NULL,
            `dni` varchar(255) NOT NULL,
            PRIMARY KEY  (`id_customer`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4';

        foreach ($sql as $query) {
            if ( ! Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    public function installConfiguration(): bool
    {
        return Configuration::updateValue('CUSTOMER_DNI_DISPLAY', true)
            && Configuration::updateValue('CUSTOMER_DNI_REQUIRED', true)
            && Configuration::updateValue('CUSTOMER_DNI_UNIQUE', true)
            && Configuration::updateValue('CUSTOMER_DNI_OVERRIDE_ADDRESS_DNI', true)
            && Configuration::updateValue('CUSTOMER_DNI_REGEXP',  null);
    }
}

