<?php
declare(strict_types = 1);

namespace CustomerDNI\Presenter;

use CustomerDNI\Entity\CustomerDNI;

class CustomerDNIPresenter
{
    public function present(CustomerDNI $customerDNI): array
    {
        return [
            'customer_id' => $customerDNI->getIDCustomer(),
            'dni'         => $customerDNI->getDNI(),
        ];
    }
}