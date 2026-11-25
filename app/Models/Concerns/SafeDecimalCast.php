<?php

namespace App\Models\Concerns;

trait SafeDecimalCast
{
    /**
     * Cast a decimal value to a string with scale, preventing float deprecation warnings in BigNumber.
     *
     * @param mixed $value
     * @param int $decimals
     * @return string
     */
    protected function asDecimal($value, $decimals)
    {
        return parent::asDecimal(is_float($value) ? (string) $value : $value, $decimals);
    }
}
