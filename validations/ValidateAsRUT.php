<?php
declare(strict_types = 1);

use CustomerDNI\Interface\CustomValidator;

/**
 * Validates a DNI as a RUT (Rol Único Tributario).
 */
class ValidateAsRUT implements CustomValidator
{
    /**
     * Stores the error message when the DNI is invalid.
     *
     * @var string
     */
    private string $error_message = '';

    /**
     * Validates a DNI as a RUT.
     *
     * Can receive a DNI with or without dots and dashes.
     *
     * @param string $dni The DNI to validate.
     * @return bool True if the DNI is valid as a RUT, false otherwise.
     */
    public function validateDNI(string $dni): bool
    {
        $context = Context::getContext();
        $translator = $context->getTranslator();

        $dni = strtoupper($dni);

        // Remove all characters except numbers and K
        $dni = preg_replace('/[^0-9K]/', '', $dni);

        if (strlen($dni) < 8 || strlen($dni) > 9) {
            $this->error_message = $translator->trans('The RUT is not valid.', [], 'Modules.Customerdni.Admin');

            return false;
        }

        // Check if the format is valid
        if ( ! preg_match('/^\d{7,8}[0-9Kk]$/', $dni)) {
            $this->error_message = $translator->trans('The RUT has an invalid format.', [], 'Modules.Customerdni.Admin');

            return false;
        }

        $rut = substr($dni, 0, -1);
        $vd = substr($dni, -1);

        if ($vd == 'K') {
            $vd = '10';
        }

        $x = 2;
        $s = 0;

        for ($i = strlen($rut) - 1; $i >= 0; $i--) {
            if ($x > 7) {
                $x = 2;
            }

            $s += $rut[$i] * $x;
            $x++;
        }

        $vdCalc = 11 - ($s % 11);

        if ($vdCalc == 11) {
            $vdCalc = 0;
        }

        if ($vdCalc == 10) {
            $vdCalc = 'K';
        }

        if ($vdCalc != $vd) {
            $this->error_message = $translator->trans('The RUT is not valid.', [], 'Modules.Customerdni.Admin');

            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @return string The error message.
     */
    public function getErrorMessage(): string
    {
        return $this->error_message;
    }
}