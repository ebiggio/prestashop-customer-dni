<?php
declare(strict_types = 1);

namespace CustomerDNI\Interface;

/**
 * Defines the methods that a class must implement to validate a DNI.
 */
interface CustomValidator
{
    /**
     * Defines the method that validates a DNI.
     *
     * @param string $dni The DNI to validate.
     * @return bool True if the DNI is valid, false otherwise.
     */
    public function validateDNI(string $dni): bool;

    /**
     * Returns the error message for when the DNI is invalid.
     *
     * @return string The error message.
     */
    public function getErrorMessage(): string;
}