<?php 

namespace App\Repositories\Currency;
use App\Models\Currency;

interface CurrencyInterface
{
    /**
    * Get list of all currency.
    *
    */
    public function findAll();
}