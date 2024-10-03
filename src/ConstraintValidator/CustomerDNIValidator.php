<?php
declare(strict_types = 1);

namespace CustomerDNI\ConstraintValidator;

use CustomerDNI\Interface\CustomValidator;
use CustomerDNI\Repository\CustomerDNIRepository;

use PrestaShop\PrestaShop\Adapter\ContainerFinder;
use PrestaShop\PrestaShop\Core\Exception\ContainerNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Exception;
use Configuration;
use Context;
use Customer;

/**
 * Class CustomerDNIValidator, responsible for validating customer DNI according to the settings of the module.
 */
class CustomerDNIValidator extends ConstraintValidator
{
    /**
     * The error message to be displayed when the DNI is not valid.
     *
     * @var string
     */
    private string $errorMessage = '';

    /**
     * {@inheritdoc}
     *
     * @throws ContainerNotFoundException
     */
    public function validate($value, Constraint $constraint): void
    {
        if ( ! $constraint instanceof CustomerDNI) {
            throw new UnexpectedTypeException($constraint, CustomerDNI::class);
        }

        if ( ! is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if ( ! $this->isDNIValid($value, $constraint->customerID)) {
            $this->context->buildViolation($this->errorMessage)
                ->addViolation();
        }
    }

    /**
     * Checks if the DNI is valid, according to the settings of the module.
     *
     * @param string $dni The DNI to be validated.
     * @param int|null $customerID The ID of the customer whose DNI is being validated.
     * If the DNI is being validated during the creation of a new customer, this parameter is null.
     *
     * @return bool Whether the DNI is valid.
     * @throws ContainerNotFoundException
     * @throws Exception
     */
    private function isDNIValid(string $dni, int|null $customerID): bool
    {
        $context = Context::getContext();
        $container = (new ContainerFinder($context))->getContainer();

        // Check if the DNI is required
        if (Configuration::get('CUSTOMER_DNI_REQUIRED') && empty(trim($dni))) {
            $this->errorMessage = $context->getTranslator()->trans('The DNI is required.', [], 'Modules.Customerdni.Admin');

            return false;
        }

        // Check the DNI against the stored regular expression
        if (Configuration::get('CUSTOMER_DNI_REGEXP') && ! preg_match(Configuration::get('CUSTOMER_DNI_REGEXP'), $dni)) {
            $this->errorMessage = $context->getTranslator()->trans('The DNI is not valid.', [], 'Modules.Customerdni.Admin');

            return false;
        }

        // Check if the DNI must be unique (i.e., no two customers can have the same DNI)
        if (Configuration::get('CUSTOMER_DNI_UNIQUE')) {
            /** @var CustomerDNIRepository $customerDNIRepository */
            $customerDNIRepository = $container->get('customer_dni.repository.customer_dni_repository');
            $existingCustomerIDs = $customerDNIRepository->getAllCustomerIDsByDNI($dni);

            if ($existingCustomerIDs && ! in_array($customerID, $existingCustomerIDs)) {
                foreach ($existingCustomerIDs as $existingCustomerID) {
                    $existingCustomer = new Customer($existingCustomerID);

                    /*
                     * If the existing customer is a guest, we ignore the uniqueness check.
                     * Otherwise, a guest customer that performs a purchase would not later be able to create an account
                     * with its own DNI used in said purchase
                     */
                    if ($existingCustomer->is_guest) {
                        continue;
                    } else {
                        $this->errorMessage = $context->getTranslator()->trans(
                            'The DNI is already assigned to another customer.', [], 'Modules.Customerdni.Admin');

                        return false;
                    }
                }
            }
        }

        // Check the DNI against custom validators
        if (Configuration::get('CUSTOMER_DNI_CUSTOM_VALIDATORS')) {
            return $this->validateDNIUsingCustomValidators($dni);
        }

        return true;
    }

    /**
     * Validates the DNI using custom validators.
     *
     * @param string $dni The DNI to be validated.
     * @return bool Whether the DNI is valid.
     */
    private function validateDNIUsingCustomValidators(string $dni): bool
    {
        // Get all the PHP files in the `custom_validators` directory, except for the `index.php` file
        $moduleDir = dirname(__DIR__, 2);
        $validationClasses = glob($moduleDir . '/custom_validators/*.php');
        $validationClasses = array_filter($validationClasses, function ($file) {
            return basename($file) !== 'index.php';
        });

        // Check if there are any validation classes
        if (empty($validationClasses)) {
            return true;
        }

        // Check the DNI against each validation class
        foreach ($validationClasses as $validationClass) {
            // Include the validation class
            require_once $validationClass;

            // Get the class name
            $className = pathinfo($validationClass, PATHINFO_FILENAME);

            // Create an instance of the validation class
            $validationInstance = new $className();

            if ( ! $validationInstance instanceof CustomValidator) {
                continue;
            }

            if ( ! $validationInstance->validateDNI($dni)) {
                $this->errorMessage = $validationInstance->getErrorMessage();

                return false;
            }
        }

        return true;
    }
}