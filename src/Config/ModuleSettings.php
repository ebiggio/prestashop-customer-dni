<?php
declare(strict_types = 1);

namespace CustomerDNI\Config;

class ModuleSettings
{
    /**
     * Module settings, with their default values.
     *
     * @var array
     */
    public const SETTINGS = [
        'CUSTOMER_DNI_DISPLAY'              => true,
        'CUSTOMER_DNI_REQUIRED'             => false,
        'CUSTOMER_DNI_UNIQUE'               => false,
        'CUSTOMER_DNI_OVERRIDE_ADDRESS_DNI' => false,
        'CUSTOMER_DNI_REGEXP'               => '',
        'CUSTOMER_DNI_CUSTOM_VALIDATORS'    => false
    ];
}