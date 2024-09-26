<?php
declare(strict_types = 1);

namespace CustomerDNI\Controller;

use CustomerDNI\Repository\CustomerDNIRepository;

use PrestaShop\PrestaShop\Adapter\ContainerFinder;
use Context;
use Configuration;
use Exception;
use FormField;

/**
 * Handles front office hooks.
 */
class FrontOfficeHooks
{
    /**
     * The value of the DNI field from the customer registration form.
     *
     * @var string
     */
    public string $dni_value = '';

    /*
     * Singleton instance.
     */
    private static ?FrontOfficeHooks $instance = null;

    /**
     * Returns the singleton instance of this class.
     *
     * @return FrontOfficeHooks The singleton instance.
     */
    public static function getInstance(): FrontOfficeHooks
    {
        if (is_null(self::$instance)) {
            self::$instance = new FrontOfficeHooks();
        }

        return self::$instance;
    }

    /**
     * Adds the custom DNI field to the customer registration and personal information forms of the front office.
     *
     * @param array $form_fields The form fields of the customer registration or personal information form.
     *
     * @return array The modified form fields.
     * @throws Exception
     */
    public static function AdditionalCustomerFormFields(array $form_fields): array
    {
        $context = Context::getContext();
        $container = (new ContainerFinder($context))->getContainer();

        /** @var CustomerDNIRepository $customerDNIRepository */
        $customerDNIRepository = $container->get('customer_dni.repository.customer_dni_repository');
        $customerDNI = is_null($context->customer->id) ? '' : $customerDNIRepository->getDNIByCustomerId($context->customer->id);

        $currentFields = $form_fields;
        $reorderedFields = [];

        $customer_dni_form_field = (new FormField())
            ->setName('customer_dni')
            ->setType('text')
            ->setLabel($context->getTranslator()->trans('Customer DNI', [], 'Modules.Customerdni.Shop'))
            ->setAvailableValues(['comment' => $context->getTranslator()->trans('Only numbers are allowed.', [], 'Modules.Customerdni.Shop')])
            ->setRequired((bool)Configuration::get('CUSTOMER_DNI_REQUIRED'))
            ->setValue($customerDNI);

        // Required for the hook "validateCustomerFormFields"
        // That hooks only receives the fields that have defined the "moduleName" property with the name of the module
        $customer_dni_form_field->moduleName = 'customer_dni';

        // Iterate over the current fields to insert the DNI field after the email field
        foreach ($currentFields as $fieldName => $fieldForm) {
            $reorderedFields[$fieldName] = $fieldForm;
            if ($fieldName === 'email') {
                $reorderedFields['customer_dni'] = $customer_dni_form_field;
            }
        }

        return $reorderedFields;
    }

    /**
     * Adds the DNI of a new customer that is being registered.
     *
     * @param int $customerID The ID of the new customer.
     * @param string $dni The DNI of the customer.
     *
     * @return void
     * @throws Exception
     */
    public static function actionCustomerAccountAdd(int $customerID, string $dni): void
    {
        $context = Context::getContext();
        $container = (new ContainerFinder($context))->getContainer();

        /** @var CustomerDNIRepository $customerDNIRepository */
        $customerDNIRepository = $container->get('customer_dni.repository.customer_dni_repository');
        $customerDNIRepository->addDNI($customerID, $dni);
    }
}