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

require_once __DIR__ . '/src/Autoload.php';

use CustomerDNI\Install\InstallerFactory;
use CustomerDNI\Repository\CustomerDNIRepository;

use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

if ( ! defined('_PS_VERSION_')) {
    exit;
}

class Customer_DNI extends Module
{
    public function __construct()
    {
        $this->name = 'customer_dni';
        $this->author = 'Enzo Biggio';
        $this->version = '1.0.0';
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
     * Hook that modifies the customer grid definition, adding the `customer_dni` field to the grid.
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

        $definition = $params['definition'];

        $definition->getColumns()->addAfter('email',
            (new DataColumn('customer_dni'))
                ->setName($this->getTranslator()->trans('Customer DNI', [], 'Modules.CustomerDNI.Admin'))
                ->setOptions([
                    'field' => 'customer_dni'
                ])
        );

        $definition->getFilters()->add(
            (new Filter('customer_dni', TextType::class))
                ->setAssociatedColumn('customer_dni')
        );
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

        $searchQueryBuilder->addSelect('cdni.`dni` AS `customer_dni`');
        $searchQueryBuilder->leftJoin('c', '`' . pSQL(_DB_PREFIX_) . 'customer_dni`', 'cdni', 'cdni.`id_customer` = c.`id_customer`');

        $searchCriteria = $params['search_criteria'];

        if ('customer_dni' === $searchCriteria->getOrderBy()) {
            $searchQueryBuilder->orderBy('cdni.`customer_dni`', $searchCriteria->getOrderWay());
        }

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ('customer_dni' === $filterName) {
                $searchQueryBuilder->andWhere('cdni.`dni` LIKE :customer_dni');
                $searchQueryBuilder->setParameter('customer_dni', '%' . $filterValue . '%');
            }
        }
    }

    /**
     * Hook that modifies the customer form builder of the back office, adding the `customer_dni` field to the form.
     *
     * @param array $params
     *
     * @return void
     * @throws Exception If the `customer_dni.repository.customer_dni_repository` service is not available.
     */
    public function hookActionCustomerFormBuilderModifier(array $params): void
    {
        $required = (bool)Configuration::get('CUSTOMER_DNI_REQUIRED');

        $formBuilder = $params['form_builder'];
        $formBuilder->add('customer_dni', TextType::class, [
            'label'    => $this->GetTranslator()->trans('Customer DNI', [], 'Modules.CustomerDNI.Admin'),
            'required' => $required,
        ]);

        $customer_dni = '';
        if (null !== $params['id']) {
            /** @var CustomerDNIRepository $customerDNIRepository */
            $customerDNIRepository = $this->get('customer_dni.repository.customer_dni_repository');
            $customer_dni = $customerDNIRepository->getDNIByCustomerId((int)$params['id']);
        }

        $params['data']['customer_dni'] = $customer_dni;
        $formBuilder->setData($params['data']);
    }

    /**
     * Hook that handles the customer form submission of the back office for a new customer, saving the DNI field value.
     *
     * @param array $params
     *
     * @return void
     * @throws PrestaShopException
     */
    public function hookActionAfterCreateCustomerFormHandler(array $params): void
    {
        $dni = $params['form_data']['customer_dni'];

        // Check if the DNI is required
        if (Configuration::get('CUSTOMER_DNI_REQUIRED') && empty($dni)) {
            throw new PrestaShopException($this->getTranslator()->trans('The DNI is required.', [], 'Modules.CustomerDNI.Admin'));
        }

        // Check the DNI against the stored regular expression
        if (Configuration::get('CUSTOMER_DNI_REGEXP') && ! preg_match(Configuration::get('CUSTOMER_DNI_REGEXP'), $dni)) {
            throw new PrestaShopException($this->getTranslator()->trans('The DNI is not valid.', [], 'Modules.CustomerDNI.Admin'));
        }

        /** @var CustomerDNIRepository $customerDNIRepository */
        $customerDNIRepository = $this->get('customer_dni.repository.customer_dni_repository');

        // Check if the DNI must be unique (i.e., no two customers can have the same DNI)
        if (Configuration::get('CUSTOMER_DNI_UNIQUE')) {
            if ($customerDNIRepository->getCustomerIDByDNI($dni)) {
                throw new PrestaShopException($this->getTranslator()->trans('The DNI is already assigned to another customer.', [], 'Modules.CustomerDNI.Admin'));
            }
        }

        $customerDNIRepository->addDNI((int)$params['id'], $dni);
    }

    /**
     * Hook that handles the customer form submission of the back office for an existing customer, updating the DNI field value.
     *
     * @param array $params
     *
     * @return void
     * @throws PrestaShopException
     */
    public function hookActionAfterUpdateCustomerFormHandler(array $params): void
    {
        $dni = $params['form_data']['customer_dni'];
        $translator = $this->getTranslator();

        // Check if the DNI is required
        if (Configuration::get('CUSTOMER_DNI_REQUIRED') && empty($dni)) {
            throw new PrestaShopException($translator->trans('The DNI is required.', [], 'Modules.CustomerDNI.Admin'));
        }

        // Check the DNI against the stored regular expression
        if (Configuration::get('CUSTOMER_DNI_REGEXP') && ! preg_match(Configuration::get('CUSTOMER_DNI_REGEXP'), $dni)) {
            throw new PrestaShopException($translator->trans('The DNI is not valid.', [], 'Modules.CustomerDNI.Admin'));
        }

        /** @var CustomerDNIRepository $customerDNIRepository */
        $customerDNIRepository = $this->get('customer_dni.repository.customer_dni_repository');

        // Check if the DNI must be unique (i.e., no two customers can have the same DNI)
        if (Configuration::get('CUSTOMER_DNI_UNIQUE')) {
            $existingCustomerID = $customerDNIRepository->getCustomerIDByDNI($dni);

            if ($existingCustomerID && $existingCustomerID !== (int)$params['id']) {
                throw new PrestaShopException($translator->trans('The DNI is already assigned to another customer.', [], 'Modules.CustomerDNI.Admin'));
            }
        }

        $customerDNIRepository->addDNI((int)$params['id'], $dni);

        // Check if we must override the DNI in the address
        if (Configuration::get('CUSTOMER_DNI_OVERRIDE_ADDRESS_DNI')) {
            // Get all the addresses of the customer
            $customer = new Customer((int)$params['id']);
            $customerAddresses = $customer->getAddresses($this->context->language->id);
            $truncatedDNI = substr($dni, 0, 16); // Truncate the DNI to 16 characters, as the DNI field in the address table is a VARCHAR(16)

            // Update the DNI in all the addresses
            foreach ($customerAddresses as $address) {
                $updatedAddress = new Address($address['id_address']);
                $updatedAddress->dni = $truncatedDNI;
                $updatedAddress->save();
            }
        }
    }
}