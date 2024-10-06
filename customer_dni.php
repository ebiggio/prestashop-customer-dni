<?php
/**
 * Customer DNI module
 *
 * Adds a custom DNI field to customer registration and personal information forms.
 *
 * @author Enzo Biggio <ebiggio@gmail.com>
 * @version 1.0.0
 * @license GNU General Public License 3.0
 */
declare(strict_types = 1);

use CustomerDNI\Install\InstallerFactory;
use CustomerDNI\ConstraintValidator\CustomerDNI;
use CustomerDNI\ConstraintValidator\Factory\CustomerDNIValidatorFactory;
use CustomerDNI\Controller\BackOfficeHooks;
use CustomerDNI\Controller\FrontOfficeHooks;

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Symfony\Component\Validator\Validation;

if ( ! defined('_PS_VERSION_')) exit;

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

/**
 * Customer DNI module main class.
 */
class Customer_DNI extends Module
{
    public function __construct()
    {
        $this->name = 'customer_dni';
        $this->author = 'Enzo Biggio';
        $this->version = '1.0.0';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->ps_versions_compliancy = [
            'min' => '1.7.7.0',
            'max' => _PS_VERSION_,
        ];

        $this->displayName = $this->trans('Customer DNI', [], 'Modules.Customerdni.Admin');
        $this->description = $this->trans('Adds a custom DNI field to customer registration and personal information forms.', [], 'Modules.Customerdni.Admin');
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
     * @inerhitDoc
     */
    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    /**
     * Redirects the user to the module configuration page.
     *
     * @return void
     */
    public function getContent(): void
    {
        Tools::redirectAdmin(SymfonyContainer::getInstance()->get('router')->generate('customer_dni_settings'));
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
        BackOfficeHooks::actionAfterCreateCustomerFormHandler((int)$params['id'], (string)$params['form_data']['customer_dni']);
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
        BackOfficeHooks::actionAfterUpdateCustomerFormHandler((int)$params['id'], (string)$params['form_data']['customer_dni']);
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
     * Hook that handles the addition of a new customer address from the front and back office.
     *
     * If the configuration setting `CUSTOMER_DNI_OVERWRITE_ADDRESS_DNI` is set to `true`,
     * the DNI field of the address will be overwritten with the DNI of the customer.
     *
     * @param array $params
     *
     * @return void
     * @throws Exception
     */
    public function hookActionObjectAddressAddBefore(array $params): void
    {
        if (Configuration::get('CUSTOMER_DNI_OVERWRITE_ADDRESS_DNI')) {
            BackOfficeHooks::actionObjectAddressAddBefore($params['object'], $params['object']->id_customer);
        }
    }

    /**
     * Hook that handles the update of an existing customer address from the front and back office.
     *
     * If the configuration setting `CUSTOMER_DNI_OVERWRITE_ADDRESS_DNI` is set to `true`,
     * the DNI field of the address will be overwritten with the DNI of the customer before saving the address.
     *
     * @param array $params
     *
     * @return void
     * @throws Exception
     */
    public function hookActionObjectAddressUpdateBefore(array $params): void
    {
        if (Configuration::get('CUSTOMER_DNI_OVERWRITE_ADDRESS_DNI')) {
            BackOfficeHooks::actionObjectAddressAddBefore($params['object'], $params['object']->id_customer);
        }
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
     * Hook that allows validating custom form fields "belonging" to the module,
     * from the customer registration and personal information forms of the front office.
     *
     * This hooks only receives the fields that belong to the module, which are identified by the module's name.
     *
     * @param array $params
     *
     * @return void
     * @throws Exception
     */
    public function hookValidateCustomerFormFields(array $params): void
    {
        // We only added one field, so we know the DNI field is at index 0
        $customerDNIFormField = $params['fields'][0];
        $dni = (string)$customerDNIFormField->getValue();

        if ($errorMessage = $this->getValidationErrorsForDNI($dni, $this->context->customer->id)) {
            $params['fields']['0']->addError($errorMessage);
        } else {
            // We check if in the context, the customer ID is set to determine if the customer is being edited or created
            if ($this->context->customer->id) {
                // If the customer is being edited, we call the hook to update the DNI
                // Since the logic is the same that the one used in the back office, we reuse the same method
                BackOfficeHooks::actionAfterUpdateCustomerFormHandler($this->context->customer->id, $dni);
            } else {
                // If the customer is being created, we can't directly save the DNI because the customer ID is not set yet.
                // Instead, we store the DNI in FrontOfficeHooks singleton instance to be used later in the hook "actionCustomerAccountAdd"
                FrontOfficeHooks::getInstance()->dni_value = $dni;
            }
        }
    }

    /**
     * Hook that handles the creation of a customer account in the front office, saving the DNI field value.
     *
     * @param array $params
     *
     * @return void
     * @throws Exception
     */
    public function hookActionCustomerAccountAdd(array $params): void
    {
        // We check if the DNI value was saved in the FrontOfficeHooks singleton instance
        $front_office_hooks = FrontOfficeHooks::getInstance();
        if ($front_office_hooks->dni_value) {
            FrontOfficeHooks::actionCustomerAccountAdd((int)$params['newCustomer']->id, $front_office_hooks->dni_value);
        }
    }

    /**
     * Custom hook that allows other modules to perform operations after a DNI is associated with a customer.
     *
     * This hook is triggered after a DNI is processed in the database, whether it's a new entry or an update to an existing one.
     *
     * @param array $params The parameters passed to the hook. The following keys are available:
     * - `customerID` (int): The ID of the customer whose DNI was added or updated.
     * - `dni` (string): The DNI that was added or updated.
     *
     * @return void
     */
    public function hookActionCustomerDNIAddAfter(array $params): void
    {
        // This hook is intentionally left blank for other modules to implement their own logic
    }

    /**
     * Custom hook that allows other modules to perform operations after a DNI is deleted from the database.
     *
     * This hook is triggered after a DNI is deleted from the database, which happens when a customer is deleted.
     * The hook will trigger even if no actual record is deleted from the database (for example, if a customer is deleted but no DNI was associated with them).
     *
     * @param array $params The parameters passed to the hook. The following keys are available:
     * - `customerID` (int): The ID of the customer whose DNI was deleted.
     * - `dni` (string): The DNI that was deleted.
     *
     * @return void
     */
    public function hookActionCustomerDNIDeleteAfter(array $params): void
    {
        // This hook is intentionally left blank for other modules to implement their own logic
    }

    /**
     * Executes the validator for the DNI field and returns the error message if any validation fails.
     *
     * @param string $dni The DNI to validate.
     * @param int|null $currentCustomerID The ID of the customer whose DNI is being validated.
     *
     * @return string An error message if any validation fails. Otherwise, an empty string.
     */
    private function getValidationErrorsForDNI(string $dni, int|null $currentCustomerID): string
    {
        $validatorBuilder = Validation::createValidatorBuilder();
        $validatorBuilder->setConstraintValidatorFactory(new CustomerDNIValidatorFactory);
        $validator = $validatorBuilder->getValidator();
        $violations = $validator->validate($dni, new CustomerDNI(['customerID' => $currentCustomerID]));

        return $violations->count() ? $violations[0]->getMessage() : '';
    }
}