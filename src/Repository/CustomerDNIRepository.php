<?php
declare(strict_types = 1);

namespace CustomerDNI\Repository;

use CustomerDNI\Entity\CustomerDNI;
use Doctrine\ORM\EntityRepository;

class CustomerDNIRepository extends EntityRepository
{
    /**
     * Get the DNI of a customer by the customer ID.
     *
     * @param int $customerID The ID of the customer.
     * @return string|null The DNI of the customer. If the customer does not have a DNI, null is returned.
     */
    public function getDNIByCustomerID(int $customerID): ?string
    {
        $customerDNI = $this->findOneBy(['id_customer' => $customerID]);

        return $customerDNI ? $customerDNI->getDNI() : null;
    }

    /**
     * Get the ID of a customer by its DNI.
     *
     * @param string $dni The DNI of the customer.
     * @return int|null The ID of the customer. If the customer does not exist, null is returned.
     */
    public function getCustomerIDByDNI(string $dni): ?int
    {
        $customerID = $this->findOneBy(['dni' => $dni]);

        return $customerID ? $customerID->getIDCustomer() : null;
    }

    /**
     * Get all the IDs of the customers that have a specific DNI.
     *
     * @param string $dni The DNI of the customers.
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
     * Add a DNI to a customer.
     *
     * @param int $customerID The ID of the customer.
     * @param string $dni The DNI of the customer.
     */
    public function addDNI(int $customerID, string $dni): void
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
     */
    public function deleteDNIByCustomerID(int $customerID): void
    {
        $customerDNI = $this->findOneBy(['id_customer' => $customerID]) ?? null;

        if ($customerDNI) {
            $entityManager = $this->getEntityManager();
            $entityManager->remove($customerDNI);
            $entityManager->flush();
        }
    }
}