<?php

namespace App\Libraries;

use App\Exceptions\AmountException;

class Amount
{
    /**
     * default scale used for BCMath calculations
     */
    const DEFAULT_PRECISION = 12;

    const ROUNDING_PRECISION = 2;

    /**
     * @var string
     */
    private $amount;

    /**
     * @param mixed $amount
     *
     * @return Amount
     */
    public function __construct($amount = 0)
    {
        if (empty($amount)) {
            $this->setValue($this->amendPrecision('0'));
        } else {
            $this->setValue($this->validateValue($amount));
        }
    }

    /**
     * @param mixed $value
     *
     * @return Amount
     */
    public function add($value)
    {
        $value = $this->validateValue($value);
        $result = bcadd($this->amount, $value, self::DEFAULT_PRECISION);
        return $this->produceResult($result);
    }

    /**
     * @param mixed $value
     *
     * @return Amount
     */
    public function subtract($value)
    {
        $value = $this->validateValue($value);
        $result = bcsub($this->amount, $value, self::DEFAULT_PRECISION);
        return $this->produceResult($result);
    }

    /**
     * @param mixed $value
     *
     * @return Amount
     */
    public function multiply($value)
    {
        $value = $this->validateValue($value);
        $result = bcmul($this->amount, $value, self::DEFAULT_PRECISION);
        return $this->produceResult($result);
    }

    /**
     * @param mixed $value
     *
     * @return Amount
     *
     * @throws AmountException
     */
    public function divide($value)
    {
        $value = $this->validateValue($value);
        if (bccomp($value, '0', 0) === 0) {  // check if divisor is zero
            $this->throwException('ERROR_AMOUNT_DIVISION_BY_ZERO');
        }
        $result = bcdiv($this->amount, $value, self::DEFAULT_PRECISION);
        return $this->produceResult($result);
    }

    /**
     * @param mixed $value
     *
     * @return Boolean
     */
    public function isEqualTo($value)
    {
        $value = $this->validateValue($value);
        return bccomp($this->amount, $value, self::DEFAULT_PRECISION) === 0;
    }

    /**
     * @param mixed $value
     *
     * @return Boolean
     */
    public function isLessThan($value)
    {
        $value = $this->validateValue($value);
        return bccomp($this->amount, $value, self::DEFAULT_PRECISION) === -1;
    }

    /**
     * @param mixed $value
     *
     * @return Boolean
     */
    public function isMoreThan($value)
    {
        $value = $this->validateValue($value);
        return bccomp($this->amount, $value, self::DEFAULT_PRECISION) === 1;
    }

    /**
     * Rounds amount up
     *
     * @param int $value
     * @param int $roundingPrecision
     * @return int
     */
    private function ceil($value, $roundingPrecision = self::ROUNDING_PRECISION)
    {
        $factor = bcpow(10, $roundingPrecision);
        $withDecimals = bcmul($value, $factor, self::DEFAULT_PRECISION);
        $noDecimals = bcadd($withDecimals, 0, 0);

        if (bccomp($withDecimals, $noDecimals, self::DEFAULT_PRECISION) === 0) {
            return bcdiv(
                bcadd($withDecimals, '0', 0),
                $factor,
                self::DEFAULT_PRECISION
            );
        }

        return bcdiv(
            bcadd($withDecimals, '1', 0),
            $factor,
            self::DEFAULT_PRECISION
        );
    }

    /**
     * @param mixed $precision
     * @param bool $roundUp
     *
     * @return string
     */
    public function getValue($precision = null, $roundUp = false)
    {
        if (is_numeric($precision)) {
            return $this->round($this->amount, (int) $precision);
        }

        if ($roundUp) {
            return $this->ceil($this->amount);
        }

        return $this->amount;
    }

    /**
     * @param string $amount
     */
    private function setValue($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Rounds a given value to a specified relative precision.
     *
     * @param int $precision
     *
     * @return string
     */
    private function round($valueToRound, $relativePrecision)
    {
        // calculate how many powers of 10 for the relative precision.
        $power = bcpow('10', (string) abs((int) $relativePrecision + 1));
        // get whole number for rounding by repositioning the decimal point.
        if ($relativePrecision >= 0) {
            $wholeNumber = bcmul($valueToRound, $power, 0);  // drop the decimal fraction.
        }
        if ($relativePrecision < 0) {
            $wholeNumber = bcdiv($valueToRound, $power, 0);  // drop the decimal fraction.
        }
        // if whole number is not zero, it can be rounded.
        if ($wholeNumber != '0') {
            $onesDigit = bcmod($wholeNumber, '10');  // get single digit from ones position.
            $wholeNumber = bcsub($wholeNumber, $onesDigit, 0);  // round down first.
            if ($onesDigit >= '5') {
                $wholeNumber = bcadd($wholeNumber, '10', 0);  // then round up by 10, if needed.
            }
        }
        // finally, restore decimal point to its original position.
        if ($relativePrecision >= 0) {
            $roundedValue = bcdiv($wholeNumber, $power, abs((int) $relativePrecision));
        }
        if ($relativePrecision < 0) {
            $roundedValue = bcmul($wholeNumber, $power, 0);
        }
        return $roundedValue;
    }

    /**
     * Checks if the passed value is a valid base-10 decimal number.
     *
     * @param mixed $value
     *
     * @return string
     *
     * @throws AmountException
     */
    private function validateValue($value)
    {
        if (is_scalar($value) && is_numeric($value)) {
            // check if its not a scientific or hexadecimal numbers.
            if (preg_match('/[a-fx]+/i', (string) $value) === 0) {
                return $this->amendPrecision((string) $value);
            }
        }
        // check if object is an Amount object before getting value.
        if (is_object($value) && $value instanceof Amount) {
            return $value->getValue();
        }
        $this->throwException('ERROR_AMOUNT_INVALID_VALUE');
    }

    /**
     * Normalizes precision by amending zeroes if decimal digits is
     * less than the DEFAULT_PRECISION or rounds off if it is more.
     *
     * @param string $amount
     *
     * @return string
     */
    private function amendPrecision($value)
    {
        $value = bcadd($value, '0', self::DEFAULT_PRECISION + 1);
        return $this->round($value, self::DEFAULT_PRECISION);
    }

    /**
     * Returns a new instance of Amount carrying an initial value.
     *
     * @param string $amount
     *
     * @return Amount
     */
    protected function produceResult($value)
    {
        return new Amount($value);
    }

    /**
     * Throws an exception with a given exception message.
     *
     * @throws AmountException
     */
    protected function throwException($errorCode)
    {
        AmountException::throw($errorCode);
    }
}
