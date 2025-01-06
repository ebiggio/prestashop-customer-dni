<?php
declare(strict_types=1);

namespace Ebiggio\CustomerDNI\Install;

class InstallerFactory
{
    public static function createInstaller(): Installer
    {
        return new Installer();
    }
}