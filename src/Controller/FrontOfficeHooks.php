<?php
declare(strict_types = 1);

namespace CustomerDNI\Controller;

use CustomerDNI\Repository\CustomerDNIRepository;

use PrestaShop\PrestaShop\Adapter\ContainerFinder;
use Configuration;
use Exception;
use FormField;

/**
 * Handles front office hooks.
 */
class FrontOfficeHooks
{
    /**
     * Adds the custom DNI field to the customer registration and personal information forms of the front office.
     *
     * @param array $form_fields The form fields of the customer registration or personal information form.
     *
     * @return array
     * @throws Exception
     */
    public static function AdditionalCustomerFormFields(array $form_fields): array
    {
        $context = \Context::getContext();
        $container = (new ContainerFinder($context))->getContainer();

        /** @var CustomerDNIRepository $customerDNIRepository */
        $customerDNIRepository = $container->get('customer_dni.repository.customer_dni_repository');
        $customerDNI = is_null($context->customer->id) ? '' : $customerDNIRepository->getDNIByCustomerId($context->customer->id);

        $currentFields = $form_fields;
        $reorderedFields = [];

        // Iterate over the current fields to insert the DNI field after the email field
        foreach ($currentFields as $fieldName => $fieldForm) {
            $reorderedFields[$fieldName] = $fieldForm;
            if ($fieldName === 'email') {
                $reorderedFields['customer_dni'] = (new FormField())
                    ->setName('customer_dni')
                    ->setType('text')
                    ->setLabel($context->getTranslator()->trans('Customer DNI', [], 'Modules.CustomerDNI.Admin'))
                    ->setAvailableValues(['comment' => $context->getTranslator()->trans('Only numbers are allowed.', [], 'Modules.CustomerDNI.Admin')])
                    ->setRequired((bool)Configuration::get('CUSTOMER_DNI_REQUIRED'))
                    ->setValue($customerDNI);
            }
        }

        return $reorderedFields;
    }
}