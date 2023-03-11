<?php

namespace Tests\Unit\Repositories\Currency;

use App\Models\Currency;
use App\Repositories\Currency\CurrencyRepository;
use TestCase;
use Mockery;

class CurrencyRepositoryTest extends TestCase
{
    /**
     * @testdox Test findAll success
     *
     * @return void
     */
    public function testfindAllSuccess()
    {
        $repository = new CurrencyRepository();
        $currencies = $repository->findAll();
        $this->assertIsArray($currencies);

        $currency = $currencies[0];
        $this->assertInstanceOf(Currency::class, $currency);
    }

    /**
     * @testdox Test get tenant currency list false
     *
     * @return void
     */
    public function testIsSupportedFalse()
    {
        $repository = new CurrencyRepository();
        $currencyCode = 'ABCD';
        $isValid = $repository->isSupported($currencyCode);
        $this->assertFalse($isValid);
    }

    /**
     * @testdox Test get tenant currency list
     *
     * @return void
     */
    public function testIsSupportedTrue()
    {
        $repository = new CurrencyRepository();
        $currencyCode = array_keys(CurrencyRepository::SUPPORTED_CURRENCIES)[0];
        $isValid = $repository->isSupported($currencyCode);
        $this->assertTrue($isValid);
    }
}
