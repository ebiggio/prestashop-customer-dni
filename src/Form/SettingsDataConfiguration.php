<?php
declare(strict_types = 1);

namespace CustomerDNI\Form;

use CustomerDNI\Config\ModuleSettings;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use Context;

class SettingsDataConfiguration implements DataConfigurationInterface
{
    /**
     * @var ConfigurationInterface
     */
    private ConfigurationInterface $configuration;

    /**
     * Holds the errors that occurred while updating the configuration.
     *
     * @var array
     */
    private array $errors = [];

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): array
    {
        $configuration = [];

        foreach (ModuleSettings::SETTINGS as $settingName => $settingValue) {
            if ($settingName === 'CUSTOMER_DNI_REGEXP') {
                // This is the only setting that can be set to an empty string
                $configuration[strtolower($settingName)] = $this->configuration->get($settingName, '');
            } else {
                // The rest of the settings are boolean switches, so we cast them as boolean
                $configuration[strtolower($settingName)] = (bool)$this->configuration->get($settingName);
            }
        }

        return $configuration;
    }

    public function updateConfiguration(array $configuration): array
    {
        if ($this->validateConfiguration($configuration)) {
            foreach ($configuration as $key => $value) {
                $this->configuration->set(strtoupper($key), $value);
            }
        }

        return $this->errors;
    }

    /**
     * Validates the configuration values, setting the errors in the `$errors` property if any validation fails.
     *
     * @param array $configuration Configuration values to validate.
     * @return bool True if the configuration is valid, false otherwise.
     */
    public function validateConfiguration(array $configuration): bool
    {
        $context = Context::getContext();
        $isValidConfiguration = true;

        // Check if the value for the regex is a valid regular expression
        if (isset($configuration['customer_dni_regexp'])) {
            if (false === @preg_match($configuration['customer_dni_regexp'], '')) {
                $this->errors['customer_dni_regexp'] = $context->getTranslator()->trans('The regular expression is not valid.', [], 'Modules.CustomerDNI.Admin');

                $isValidConfiguration = false;
            }
        } else {
            // Check for the rest of the configuration values which, while optional, must exist in the configuration array
            $configurationKeys = array_keys($configuration);
            foreach ($configurationKeys as $key) {
                if ( ! isset(ModuleSettings::SETTINGS[strtoupper($key)])) {
                    $isValidConfiguration = false;
                }

                // The rest of the settings, except for `customer_dni_regexp`, behave like a "boolean switch", so their values can be either `true` or `false`
                if ( ! in_array($configuration[$key], [true, false], true) && $key !== 'customer_dni_regexp') {
                    $this->errors[$key] = $context->getTranslator()->trans('The value is not valid.', [], 'Modules.CustomerDNI.Admin');

                    $isValidConfiguration = false;
                }
            }
        }

        return $isValidConfiguration;
    }
}