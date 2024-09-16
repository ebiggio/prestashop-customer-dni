<?php
/**
 * Customer DNI module
 *
 * Adds a custom DNI field to customer registration and personal information forms.
 *
 * @author Enzo Biggio <ebiggio@gmail.com>
 * @version 0.6.0
 * @license GNU General Public License 3.0
 */
declare(strict_types = 1);

require_once __DIR__ . '/src/Autoload.php';

use CustomerDNI\Install\InstallerFactory;
use CustomerDNI\Repository\CustomerDNIRepository;
use CustomerDNI\Controller\BackOfficeHooks;
use CustomerDNI\Controller\FrontOfficeHooks;

if ( ! defined('_PS_VERSION_')) {
    exit;
}

class Customer_DNI extends Module
{
    public function __construct()
    {
        $this->name = 'customer_dni';
        $this->author = 'Enzo Biggio';
        $this->version = '0.6.0';
        $this->need_instance = 0;

        parent::__construct();

        $this->ps_versions_compliancy = [
            'min' => '1.7.6.0',
            'max' => _PS_VERSION_,
        ];

        $this->displayName = $this->trans('Customer DNI', [], 'Modules.CustomerDNI.Admin');
        $this->description = $this->trans('Adds a custom DNI field to customer registration and personal information forms.', [], 'Modules.CustomerDNI.Admin');
    }

    public function install(): bool
    {
        $this->_clearCache('*');

        if ( ! parent::install()) {
            return false;
        }

        $installer = InstallerFactory::createInstaller();

        return $installer->install($this);
    }

    public function uninstall(): bool
    {
        $this->_clearCache('*');

        $installer = InstallerFactory::createInstaller();

        return $installer->uninstall() && parent::uninstall();
    }

    /**
     * Hook that modifies the customer grid definition, adding the `customer_dni` field as a column.
     *
     * Won't display the DNI field if the configuration setting `CUSTOMER_DNI_DISPLAY` is set to `false`.
     * @param array $params
     *
     * @return void
     */
    public function hookActionCustomerGridDefinitionModifier(array $params): void
    {
        if ( ! Configuration::get('CUSTOMER_DNI_DISPLAY')) {
            return;
        }

        $params['definition'] = BackOfficeHooks::actionCustomerGridDefinitionModifier($params['definition']);
    }

    /**
     * Hook that modifies the customer grid query builder, adding the `customer_dni` field as a searchable and ordenable field.
     *
     * Won't display the DNI field if the configuration setting `CUSTOMER_DNI_DISPLAY` is set to `false`.
     * @param array $params
     *
     * @return void
     */
    public function hookActionCustomerGridQueryBuilderModifier(array $params): void
    {
        if ( ! Configuration::get('CUSTOMER_DNI_DISPLAY')) {
            return;
        }

        $searchQueryBuilder = $params['search_query_builder'];
        $searchCriteria = $params['search_criteria'];

        BackOfficeHooks::actionCustomerGridQueryBuilderModifier($searchQueryBuilder, $searchCriteria);
    }

    /**
     * Hook that modifies the customer form builder of the back office, adding the `customer_dni` field to the form.
     *
     * @param array $params
     *
     * @return void
     * @throws Exception
     */
    public function hookActionCustomerFormBuilderModifier(array $params): void
    {
        BackOfficeHooks::actionCustomerFormBuilderModifier($params['form_builder'], $params['data'], $params['id']);
    }

    /**
     * Hook that handles the customer form submission of the back office for a new customer, saving the DNI field value.
     *
     * @param array $params
     *
     * @return void
     * @throws Exception
     */
    public function hookActionAfterCreateCustomerFormHandler(array $params): void
    {
        if ($errorMessage = $this->getValidationErrorsForDNI($params['form_data']['customer_dni'], null)) {
            throw new PrestaShopException($errorMessage);
        }

        BackOfficeHooks::actionAfterCreateCustomerFormHandler((int)$params['id'], $params['form_data']['customer_dni']);
    }

    /**
     * Hook that handles the customer form submission of the back office for an existing customer, updating the DNI field value.
     *
     * @param array $params
     *
     * @return void
     * @throws Exception
     */
    public function hookActionAfterUpdateCustomerFormHandler(array $params): void
    {
        if ($errorMessage = $this->getValidationErrorsForDNI($params['form_data']['customer_dni'], (int)$params['id'])) {
            throw new PrestaShopException($errorMessage);
        }

        BackOfficeHooks::actionAfterUpdateCustomerFormHandler((int)$params['id'], $params['form_data']['customer_dni']);
    }

    /**
     * Hook that handles the deletion of a customer, removing the DNI from the database.
     *
     * @param array $params
     *
     * @return void
     * @throws Exception
     */
    public function hookActionObjectCustomerDeleteAfter(array $params): void
    {
        BackOfficeHooks::actionObjectCustomerDeleteAfter($params['object']->id);
    }

    /**
     * Hook that allows handling of the form fields from the customer registration and personal information forms of the front office.
     *
     * @param array $params
     *
     * @return void
     * @throws Exception
     */
    public function hookAdditionalCustomerFormFields(array $params): void
    {
        $params['fields'] = FrontOfficeHooks::AdditionalCustomerFormFields($params['fields']);
    }

    /**
     * Performs common validations on the DNI and returns an error message if any validation fails.
     *
     * @param string $dni The DNI to validate.
     * @param int|null $currentCustomerID The ID of the customer being edited. If null, a new customer is being created.
     *
     * @return string An error message if any validation fails. Otherwise, an empty string.
     * @throws Exception If the container of the module is not found.
     */
    private function getValidationErrorsForDNI(string $dni, int|null $currentCustomerID): string
    {
        // Check if the DNI is required
        if (Configuration::get('CUSTOMER_DNI_REQUIRED') && empty($dni)) {
            return $this->getTranslator()->trans('The DNI is required.', [], 'Modules.CustomerDNI.Admin');
        }

        // Check the DNI against the stored regular expression
        if (Configuration::get('CUSTOMER_DNI_REGEXP') && ! preg_match(Configuration::get('CUSTOMER_DNI_REGEXP'), $dni)) {
            return $this->getTranslator()->trans('The DNI is not valid.', [], 'Modules.CustomerDNI.Admin');
        }

        // Check if the DNI must be unique (i.e., no two customers can have the same DNI)
        if (Configuration::get('CUSTOMER_DNI_UNIQUE')) {
            /** @var CustomerDNIRepository $customerDNIRepository */
            $customerDNIRepository = $this->get('customer_dni.repository.customer_dni_repository');
            $existingCustomerID = $customerDNIRepository->getCustomerIDByDNI($dni);

            if ($existingCustomerID && $existingCustomerID !== $currentCustomerID) {
                return $this->getTranslator()->trans('The DNI is already assigned to another customer.', [], 'Modules.CustomerDNI.Admin');
            }
        }

        return '';
    }
}