<?php
declare(strict_types=1);

namespace CustomerDNI\Install;

class InstallerFactory
{
    public static function createInstaller(): Installer
    {
        return new Installer();
    }
}