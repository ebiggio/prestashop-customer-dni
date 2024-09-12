<?php

namespace CustomerDNI\Database;

use Configuration;


class Uninstall
{
    public function run(): bool
    {
        return $this->uninstallConfiguration();
    }

    public function uninstallConfiguration(): bool
    {
        return Configuration::deleteByName('CUSTOMER_DNI_DISPLAY')
            && Configuration::deleteByName('CUSTOMER_DNI_REQUIRED')
            && Configuration::deleteByName('CUSTOMER_DNI_UNIQUE')
            && Configuration::deleteByName('CUSTOMER_DNI_OVERRIDE_ADDRESS_DNI')
            && Configuration::deleteByName('CUSTOMER_DNI_REGEXP');
    }
}
