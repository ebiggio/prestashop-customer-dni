<?php
declare(strict_types = 1);

namespace CustomerDNI\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CustomerDNI\Repository\CustomerDNIRepository")
 */
class CustomerDNI
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_customer", type="integer")
     */
    private int $id_customer;

    /**
     * @var string
     *
     * @ORM\Column(name="dni", type="string", length=255)
     */
    private string $dni;

    public function getIdCustomer(): ?int
    {
        return $this->id_customer;
    }

    public function setIdCustomer(int $id_customer): CustomerDNI
    {
        $this->id_customer = $id_customer;

        return $this;
    }

    public function getDNI(): ?string
    {
        return $this->dni;
    }

    public function setDNI(string $dni): CustomerDNI
    {
        $this->dni = $dni;

        return $this;
    }
}