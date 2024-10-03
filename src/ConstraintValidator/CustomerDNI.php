<?php
declare(strict_types = 1);

namespace CustomerDNI\ConstraintValidator;

use Symfony\Component\Validator\Constraint;

/**
 * Class CustomerDNI constraint.
 */
final class CustomerDNI extends Constraint
{
    /**
     * The ID of the customer whose DNI is being validated.
     * If the DNI is being validated during the creation of a new customer, this parameter is null.
     *
     * @var int|null
     */
    public int|null $customerID;

    public function __construct($options = null)
    {
        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions(): array
    {
        return ['customerID'];
    }


    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return CustomerDNIValidator::class;
    }
}