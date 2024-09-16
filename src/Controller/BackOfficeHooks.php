<?php
declare(strict_types = 1);

namespace CustomerDNI\Controller;

use CustomerDNI\Repository\CustomerDNIRepository;

use PrestaShop\PrestaShop\Adapter\ContainerFinder;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinition;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Search\Filters\CustomerFilters;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use Configuration;
use Customer;
use Address;
use Exception;

/**
 * Handles back office hooks.
 */
class BackOfficeHooks
{
    /**
     * Adds the custom DNI column to the customer grid definition used in the back office.
     *
     * @param GridDefinition $definition The grid definition.
     *
     * @return GridDefinition The modified grid definition.
     */
    public static function actionCustomerGridDefinitionModifier(GridDefinition $definition): GridDefinition
    {
        $context = \Context::getContext();

        $definition->getColumns()->addAfter('email',
            (new DataColumn('customer_dni'))
                ->setName($context->getTranslator()->trans('Customer DNI', [], 'Modules.CustomerDNI.Admin'))
                ->setOptions([
                    'field' => 'customer_dni'
                ])
        );

        $definition->getFilters()->add(
            (new Filter('customer_dni', TextType::class))
                ->setAssociatedColumn('customer_dni')
        );

        return $definition;
    }

    /**
     * Modifies the customer grid query builder to include the custom DNI field when querying customers.
     *
     * @param QueryBuilder $searchQueryBuilder The query builder used to fetch customers.
     * @param CustomerFilters $searchCriteria The search criteria used to filter customers.
     *
     * @return void
     */
    public static function actionCustomerGridQueryBuilderModifier(QueryBuilder $searchQueryBuilder, CustomerFilters $searchCriteria): void
    {
        $searchQueryBuilder->addSelect('cdni.`dni` AS `customer_dni`');
        $searchQueryBuilder->leftJoin('c', '`' . pSQL(_DB_PREFIX_) . 'customer_dni`', 'cdni', 'cdni.`id_customer` = c.`id_customer`');

        if ('customer_dni' === $searchCriteria->getOrderBy()) {
            $searchQueryBuilder->orderBy('cdni.`dni`', $searchCriteria->getOrderWay());
        }

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ('customer_dni' === $filterName) {
                $searchQueryBuilder->andWhere('cdni.`dni` LIKE :customer_dni');
                $searchQueryBuilder->setParameter('customer_dni', '%' . $filterValue . '%');
            }
        }
    }

    /**
     * Adds the custom DNI field to the customer form in the back office.
     *
     * @param FormBuilder $formBuilder The form builder used to build the customer form.
     * @param array $formData The data of the form.
     * @param int|null $customerID The ID of the customer. If null, the form is being used to create a new customer.
     *
     * @throws Exception
     */
    public static function actionCustomerFormBuilderModifier(FormBuilder $formBuilder, array $formData, int|null $customerID): void
    {
        $required = (bool)Configuration::get('CUSTOMER_DNI_REQUIRED');
        $context = \Context::getContext();
        $container = (new ContainerFinder($context))->getContainer();

        $formBuilder->add('customer_dni', TextType::class, [
            'label'    => $context->GetTranslator()->trans('Customer DNI', [], 'Modules.CustomerDNI.Admin'),
            'required' => $required,
        ]);

        $customer_dni = '';
        if (null !== $customerID) {
            /** @var CustomerDNIRepository $customerDNIRepository */
            $customerDNIRepository = $container->get('customer_dni.repository.customer_dni_repository');
            $customer_dni = $customerDNIRepository->getDNIByCustomerID($customerID);
        }

        $formData['customer_dni'] = $customer_dni;
        $formBuilder->setData($formData);
    }

    /**
     * Handles the form submission of the customer form in the back office for a new customer, saving the DNI field value.
     *
     * @param int $customer_id The ID of the customer.
     * @param string $dni The DNI of the customer.
     *
     * @throws Exception
     */
    public static function actionAfterCreateCustomerFormHandler(int $customer_id, string $dni): void
    {
        $context = \Context::getContext();
        $container = (new ContainerFinder($context))->getContainer();

        /** @var CustomerDNIRepository $customerDNIRepository */
        $customerDNIRepository = $container->get('customer_dni.repository.customer_dni_repository');
        $customerDNIRepository->addDNI($customer_id, $dni);
    }

    /**
     * Handles the form submission of the customer form in the back office for an existing customer, updating the DNI field value.
     *
     * @param int $customer_id The ID of the customer.
     * @param string $dni The DNI of the customer.
     *
     * @throws Exception
     */
    public static function actionAfterUpdateCustomerFormHandler(int $customer_id, string $dni): void
    {
        $context = \Context::getContext();
        $container = (new ContainerFinder($context))->getContainer();

        /** @var CustomerDNIRepository $customerDNIRepository */
        $customerDNIRepository = $container->get('customer_dni.repository.customer_dni_repository');
        $customerDNIRepository->addDNI($customer_id, $dni);

        // Check if we must override the DNI in the address
        if (Configuration::get('CUSTOMER_DNI_OVERRIDE_ADDRESS_DNI')) {
            // Get all the addresses of the customer
            $customer = new Customer($customer_id);
            $customerAddresses = $customer->getAddresses($context->language->id);
            $truncatedDNI = substr($dni, 0, 16); // Truncate the DNI to 16 characters, as the DNI field in the address table is a VARCHAR(16)

            // Update the DNI in all the addresses
            foreach ($customerAddresses as $address) {
                $updatedAddress = new Address($address['id_address']);
                $updatedAddress->dni = $truncatedDNI;
                $updatedAddress->save();
            }
        }
    }

    /**
     * Deletes the DNI value of a customer from the `customer_dni` table after the customer is deleted.
     *
     * @param int $customerID The ID of the customer that was deleted.
     *
     * @throws Exception
     */
    public static function actionObjectCustomerDeleteAfter(int $customerID): void
    {
        $context = \Context::getContext();
        $container = (new ContainerFinder($context))->getContainer();

        /** @var CustomerDNIRepository $customerDNIRepository */
        $customerDNIRepository = $container->get('customer_dni.repository.customer_dni_repository');
        $customerDNIRepository->deleteDNIByCustomerId($customerID);
    }
}