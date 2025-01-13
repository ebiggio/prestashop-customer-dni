<?php
declare(strict_types = 1);

namespace Ebiggio\CustomerDNI\Database;

use Db;

class Installer
{
    /**
     * Perform the installation task for the module at the database level.
     *
     * @return bool
     */
    public function install(): bool
    {
        return $this->createTable();
    }

    /**
     * Create the module's database table.
     *
     * @return bool
     */
    private function createTable(): bool
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
}

