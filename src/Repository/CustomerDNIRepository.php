<?php
declare(strict_types = 1);

namespace CustomerDNI\Repository;

use CustomerDNI\Entity\CustomerDNI;
use Doctrine\ORM\EntityRepository;

use Hook;
use PrestaShopException;

/**
 * Repository for the CustomerDNI entity.
 */
class CustomerDNIRepository extends EntityRepository
{
    /**
     * Get the DNI of a customer by the customer ID.
     *
     * @param int $customerID The ID of the customer.
     *
     * @return string|null The DNI of the customer. If the customer does not have a DNI, null is returned.
     */
    public function getDNIByCustomerID(int $customerID): ?string
    {
        $customerDNI = $this->findOneBy(['id_customer' => $customerID]);

        return $customerDNI ? $customerDNI->getDNI() : null;
    }

    /**
     * Get all the IDs of the customers that have a specific DNI.
     *
     * @param string $dni The DNI of the customers.
     *
     * @return array|null The IDs of the customers. If no customers have the DNI, null is returned.
     */
    public function getAllCustomerIDsByDNI(string $dni): ?array
    {
        $customerIDs = $this->findBy(['dni' => $dni]);

        if ( ! $customerIDs) {
            return null;
        }

        return array_map(function (CustomerDNI $customerDNI) {
            return $customerDNI->getIDCustomer();
        }, $customerIDs);
    }

    /**
     * Add or update the DNI of a customer.
     *
     * @param int $customerID The ID of the customer.
     * @param string $dni The DNI of the customer.
     *
     * @return void
     * @throws PrestaShopException
     */
    public function addOrUpdateDNI(int $customerID, string $dni): void
    {
        $customerDNI = $this->findOneBy(['id_customer' => $customerID]);

        if ( ! $customerDNI) {
            $customerDNI = new CustomerDNI();
            $customerDNI->setIDCustomer($customerID);
        }

        $customerDNI->setDNI($dni);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($customerDNI);
        $entityManager->flush();
    }

    /**
     * Delete the DNI of a customer.
     *
     * @param int $customerID The ID of the customer.
     *
     * @return void
     * @throws PrestaShopException
     */
    public function deleteDNIByCustomerID(int $customerID): void
    {
        $customerDNI = $this->findOneBy(['id_customer' => $customerID]) ?? '';

        if ($customerDNI) {
            $entityManager = $this->getEntityManager();
            $entityManager->remove($customerDNI);
            $entityManager->flush();
        }
    }
}