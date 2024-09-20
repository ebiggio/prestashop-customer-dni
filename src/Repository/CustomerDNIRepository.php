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
        $customer_dni = $this->findOneBy(['id_customer' => $customerID]);

        return $customer_dni ? $customer_dni->getDNI() : null;
    }

    /**
     * Get the ID of a customer by its DNI.
     *
     * @param string $dni The DNI of the customer.
     * @return int|null The ID of the customer. If the customer does not exist, null is returned.
     */
    public function getCustomerIDByDNI(string $dni): ?int
    {
        $customer_id = $this->findOneBy(['dni' => $dni]);

        return $customer_id ? $customer_id->getIDCustomer() : null;
    }

    /**
     * Add a DNI to a customer.
     *
     * @param int $customerID The ID of the customer.
     * @param string $dni The DNI of the customer.
     */
    public function addDNI(int $customerID, string $dni): void
    {
        $customer_dni = $this->findOneBy(['id_customer' => $customerID]);

        if ( ! $customer_dni) {
            $customer_dni = new CustomerDNI();
            $customer_dni->setIDCustomer($customerID);
        }

        $customer_dni->setDNI($dni);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($customer_dni);
        $entityManager->flush();
    }

    /**
     * Delete the DNI of a customer.
     *
     * @param int $customerID The ID of the customer.
     */
    public function deleteDNIByCustomerID(int $customerID): void
    {
        $customer_dni = $this->findOneBy(['id_customer' => $customerID]);

        if ($customer_dni) {
            $entityManager = $this->getEntityManager();
            $entityManager->remove($customer_dni);
            $entityManager->flush();
        }
    }
}