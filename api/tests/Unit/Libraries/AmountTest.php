<?php

namespace Tests\Unit\Libraries;

use App\Exceptions\AmountException;
use App\Libraries\Amount;
use TestCase;

class AmountTest extends TestCase
{
    /*
     * @var Amount
     */
    private $amount;

    public function setUp(): void
    {
        $this->amount = new Amount(12345.12345);
    }

    private function zeroPadded($string, $length)
    {
        return $string . str_repeat('0', $length);
    }

    public function testNewAmountWithoutValue()
    {
        $amount = new Amount;
        $actual = $amount->getValue();
        $expected = $this->zeroPadded('0.0', 11);
        $this->assertSame($expected, $actual);
    }

    public function testNewAmountWithZeroValue()
    {
        $amount = new Amount(0);
        $actual = $amount->getValue();
        $expected = $this->zeroPadded('0.0', 11);
        $this->assertSame($expected, $actual);
    }

    public function testNewAmountWithFloatingPointValue()
    {
        $amount = new Amount(123456.123456);
        $actual = $amount->getValue();
        $expected = $this->zeroPadded('123456.123456', 6);
        $this->assertSame($expected, $actual);
    }

    public function testNewAmountWithLargeIntegerValue()
    {
        $amount = new Amount(12345678987654321);
        $actual = $amount->getValue();
        $expected = $this->zeroPadded('12345678987654321.0', 11);
        $this->assertSame($expected, $actual);
    }

    public function testNewAmountWithLargeNegativeInteger()
    {
        $amount = new Amount(-12345678987654321);
        $actual = $amount->getValue();
        $expected = $this->zeroPadded('-12345678987654321.0', 11);
        $this->assertSame($expected, $actual);
    }

    public function testNewAmountWithLongDecimalDigits()
    {
        $amount = new Amount('12345.0123456789876543210');
        $actual = $amount->getValue();
        $expected = '12345.012345678988';  // rounded to 12 significant digits.
        $this->assertSame($expected, $actual);
    }

    public function testNewAmountWithAnOctalNumber()
    {
        // numbers starting with zeroes are treated
        // not as octals but as regular decimal numbers.
        $amount = new Amount('0123');
        $actual = $amount->getValue();
        $expected = $this->zeroPadded('123.0', 11);
        $this->assertSame($expected, $actual);
    }

    public function testNewAmountWithAnotherAmountObject()
    {
        $amount = new Amount($this->amount);
        $actual = $amount->getValue();
        $expected = $this->zeroPadded('12345.12345', 7);
        $this->assertSame($expected, $actual);
    }

    public function testNewAmountWithScientificNotation()
    {
        $this->expectException(AmountException::class);
        $this->expectExceptionMessage('Invalid value.');
        $amount = new Amount('123456789e-6');  // 123.456789
    }

    public function testNewAmountWithHexadecimalNumber()
    {
        $this->expectException(AmountException::class);
        $this->expectExceptionMessage('Invalid value.');
        $amount = new Amount('0xfadebabe');  // 4208900798
    }

    public function testNewAmountNonDecimalValue()
    {
        $this->expectException(AmountException::class);
        $this->expectExceptionMessage('Invalid value.');
        $amount = new Amount('foobar');
    }

    public function testAdditionWithLargeValue()
    {
        $actual = $this->amount
                    ->add('1234567890987654321.987654321')
                    ->getValue();
        $expected = '1234567890987666667.111104321000';
        $this->assertSame($expected, $actual);
    }

    public function testAdditionWithLargeNegativeValue()
    {
        $actual = $this->amount
                    ->add('-1234567890987654321.987654321')
                    ->getValue();
        $expected = '-1234567890987641976.864204321000';
        $this->assertSame($expected, $actual);
    }

    public function testAdditionWithAnotherAmountObject()
    {
        $amount = new Amount('1234567890987654321.987654321');
        $actual = $this->amount->add($amount)->getValue();
        $expected = '1234567890987666667.111104321000';
        $this->assertSame($expected, $actual);
    }

    public function testAdditionNonDecimalValue()
    {
        $this->expectException(AmountException::class);
        $this->expectExceptionMessage('Invalid value.');
        $this->amount->add('foobar');
    }

    public function testSubtractionWithLargeValue()
    {
        $actual = $this->amount
                    ->subtract('1234567890987654321.987654321')
                    ->getValue();
        $expected = '-1234567890987641976.864204321000';
        $this->assertSame($expected, $actual);
    }

    public function testSubtractionWithLargeNegativeValue()
    {
        $actual = $this->amount
                    ->subtract('-1234567890987654321.987654321')
                    ->getValue();
        $expected = '1234567890987666667.111104321000';
        $this->assertSame($expected, $actual);
    }

    public function testSubtractionWithAnotherAmountObject()
    {
        $amount = new Amount('1234567890987654321.987654321');
        $actual = $this->amount->subtract($amount)->getValue();
        $expected = '-1234567890987641976.864204321000';
        $this->assertSame($expected, $actual);
    }

    public function testSubtractionNonDecimalValue()
    {
        $this->expectException(AmountException::class);
        $this->expectExceptionMessage('Invalid value.');
        $this->amount->subtract('foobar');
    }

    public function testMultiplicationWithLargeValue()
    {
        $actual = $this->amount
                    ->multiply('1234567890.987654321')
                    ->getValue();
        $expected = '15240893021648.735018670927';
        $this->assertSame($expected, $actual);
    }

    public function testMultiplicationWithLargeNegativeValue()
    {
        $actual = $this->amount
                    ->multiply('-1234567890.987654321')
                    ->getValue();
        $expected = '-15240893021648.735018670927';
        $this->assertSame($expected, $actual);
    }

    public function testMultiplicationWithAnotherAmountObject()
    {
        $amount = new Amount('1234567890.987654321');
        $actual = $this->amount->multiply($amount)->getValue();
        $expected = '15240893021648.735018670927';
        $this->assertSame($expected, $actual);
    }

    public function testMultiplicationNonDecimalValue()
    {
        $this->expectException(AmountException::class);
        $this->expectExceptionMessage('Invalid value.');
        $this->amount->multiply('foobar');
    }

    public function testDivisionWithLargeValue()
    {
        $actual = $this->amount
                    ->divide('1234567890.987654321')
                    ->getValue();
        $expected = '0.000009999550';
        $this->assertSame($expected, $actual);
    }

    public function testDivisionWithLargeNegativeValue()
    {
        $actual = $this->amount
                    ->divide('-1234567890.987654321')
                    ->getValue();
        $expected = '-0.000009999550';
        $this->assertSame($expected, $actual);
    }

    public function testDivisionWithAnotherAmountObject()
    {
        $amount = new Amount('1234567890.987654321');
        $actual = $this->amount->divide($amount)->getValue();
        $expected = '0.000009999550';
        $this->assertSame($expected, $actual);
    }

    public function testDivisionNonDecimalValue()
    {
        $this->expectException(AmountException::class);
        $this->expectExceptionMessage('Invalid value.');
        $this->amount->divide('foobar');
    }

    public function testDivisionByZeroError()
    {
        $this->expectException(AmountException::class);
        $this->expectExceptionMessage('Division by zero.');
        $this->amount->divide('0');
    }

    public function testAmountIsEqualToGivenValue()
    {
        $actual = $this->amount->isEqualTo(12345.12345);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    public function testAmountIsEqualToLargeValue()
    {
        $largeValue = '12345678987654321.12345678901';
        $amount = new Amount($largeValue);
        $actual = $amount->isEqualTo($largeValue);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    public function testAmountIsEqualToLargeNegativeValue()
    {
        $largeNegativeValue = '-12345678987654321.12345678901';
        $amount = new Amount($largeNegativeValue);
        $actual = $amount->isEqualTo($largeNegativeValue);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    public function testAmountIsEqualToAnotherAmountObject()
    {
        $largeNegativeValue = '-12345678987654321.12345678901';
        $amount1 = new Amount($largeNegativeValue);
        $amount2 = new Amount($largeNegativeValue);
        $actual = $amount1->isEqualTo($amount2);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    public function testAmountIsEqualToNonDecimalValue()
    {
        $this->expectException(AmountException::class);
        $this->expectExceptionMessage('Invalid value.');
        $this->amount->isEqualTo('foobar');
    }

    public function testAmountIsLessThanGivenValue()
    {
        $actual = $this->amount
                    ->subtract('0.000000000001')
                    ->isLessThan(12345.12345);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    public function testAmountIsLessThanLargeValue()
    {
        $largeValue = '12345678987654321.123456789012';
        $amount = new Amount($largeValue);
        $actual = $amount
                    ->subtract('0.000000000001')
                    ->isLessThan($largeValue);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    public function testAmountIsLessThanLargeNegativeValue()
    {
        $largeValue = '-12345678987654321.123456789012';
        $amount = new Amount($largeValue);
        $actual = $amount
                    ->subtract('0.000000000001')
                    ->isLessThan($largeValue);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    public function testAmountIsLessThanAnotherAmountObject()
    {
        $largeValue = '12345678987654321.12345678901';
        $amount1 = new Amount($largeValue);
        $amount2 = new Amount($largeValue);
        $actual = $amount1
                    ->subtract('0.000000000001')
                    ->isLessThan($amount2);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    public function testAmountIsLessThanNonDecimalValue()
    {
        $this->expectException(AmountException::class);
        $this->expectExceptionMessage('Invalid value.');
        $this->amount->isLessThan('foobar');
    }

    public function testAmountIsMoreThanLargeValue()
    {
        $largeValue = '12345678987654321.123456789012';
        $amount = new Amount($largeValue);
        $actual = $amount
                    ->add('0.000000000001')
                    ->isMoreThan($largeValue);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    public function testAmountIsMoreThanLargeNegativeValue()
    {
        $largeValue = '-12345678987654321.123456789012';
        $amount = new Amount($largeValue);
        $actual = $amount
                    ->add('0.000000000001')
                    ->isMoreThan($largeValue);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    public function testAmountIsMoreThanAnotherAmountObject()
    {
        $largeValue = '12345678987654321.12345678901';
        $amount1 = new Amount($largeValue);
        $amount2 = new Amount($largeValue);
        $actual = $amount1
                    ->add('0.000000000001')
                    ->isMoreThan($amount2);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    public function testAmountIsMoreThanNonDecimalValue()
    {
        $this->expectException(AmountException::class);
        $this->expectExceptionMessage('Invalid value.');
        $this->amount->isMoreThan('foobar');
    }

    public function testGetValueNotRounded()
    {
        $actual = $this->amount->getValue();
        $expected = $this->zeroPadded('12345.12345', 7);
        $this->assertSame($expected, $actual);
    }

    public function testGetValueRoundedToTheThousandsPlace() {
        $amount = new Amount('9876543.9876543');
        $actual = $amount->getValue(-3);  // 3 decimal places to the left.
        $expected = '9877000';
        $this->assertSame($expected, $actual);
    }

    public function testGetValueRoundedNoFractionalPart() {
        $amount = new Amount('9876543.9876543');
        $actual = $amount->getValue(0);
        $expected = '9876544';  // rounded due to 0.9.
        $this->assertSame($expected, $actual);
    }

    public function testGetValueRoundedToFourDecimalDigits() {
        $amount = new Amount('9876543.9876543');
        $actual = $amount->getValue(4);  // 4 decimal places to the right.
        $expected = '9876543.9877';
        $this->assertSame($expected, $actual);
    }

    public function testAmountObjectImmutability()
    {
        $amount1 = new Amount(12345.6789);
        // calls to these methods should not change the object's value.
        $amount2 = $amount1
                    ->multiply(3)
                    ->divide(2)
                    ->add(100)
                    ->subtract(500);
        $actual = $amount1->getValue();
        $expected = $this->zeroPadded('12345.6789', 8);
        $this->assertSame($expected, $actual);
        // the returned object should be of the same Amount class.
        $this->assertInstanceOf(Amount::class, $amount2);
        // the returned object should have the correct calculation.
        $actual = $amount2->getValue();
        $expected = $this->zeroPadded('18118.51835', 7);
        $this->assertSame($expected, $actual);
    }
}
