<?php
declare(strict_types = 1);

namespace CustomerDNI\Presenter;

use CustomerDNI\Entity\CustomerDNI;

class CustomerDNIPresenter
{
    public function present(CustomerDNI $customerDNI): array
    {
        return [
            'id_customer' => $customerDNI->getIdCustomer(),
            'dni'         => $customerDNI->getDNI(),
        ];
    }
}