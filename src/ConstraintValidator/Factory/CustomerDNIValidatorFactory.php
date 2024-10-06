<?php
namespace CustomerDNI\ConstraintValidator\Factory;

use CustomerDNI\ConstraintValidator\CustomerDNIValidator;

use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\Constraint;

class CustomerDNIValidatorFactory implements ConstraintValidatorFactoryInterface
{
    /**
     * @param Constraint $constraint
     *
     * @return CustomerDNIValidator
     */
    public function getInstance(Constraint $constraint): CustomerDNIValidator
    {
        return new CustomerDNIValidator();
    }
}