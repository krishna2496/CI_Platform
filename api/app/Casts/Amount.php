<?php

namespace App\Casts;

use App\Libraries\Amount as CastAmount;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Amount implements CastsAttributes
{
    const AMOUNT_PRECISION = 4;

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return Amount
     */
    public function get($model, string $key, $value, array $attributes): CastAmount
    {
        if (!$value instanceof CastAmount) {
            return new CastAmount($value);
        }

        return $value;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return string
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value instanceof CastAmount) {
            return (new CastAmount($value))->getValue(self::AMOUNT_PRECISION);
        }

        return $value->getValue(self::AMOUNT_PRECISION);
    }
}
