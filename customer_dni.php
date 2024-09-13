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
}