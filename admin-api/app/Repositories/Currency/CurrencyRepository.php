<?php

namespace App\Repositories\Currency;

use App\Models\Currency;

class CurrencyRepository
{
    const SUPPORTED_CURRENCIES = [
        'USD' => '$',
        'AED' => 'د.إ',
        'AFN' => '؋',
        'ALL' => 'L',
        'AMD' => 'դր.',
        'ANG' => 'ƒ',
        'AOA' => 'Kz',
        'ARS' => '$',
        'AUD' => '$',
        'AWG' => 'ƒ',
        'AZN' => 'm',
        'BAM' => 'КМ',
        'BBD' => '$',
        'BDT' => '৳',
        'BGN' => 'лв',
        'BIF' => 'Fr',
        'BMD' => '$',
        'BND' => '$',
        'BOB' => 'Bs.',
        'BRL' => 'R$',
        'BSD' => '₹',
        'BWP' => 'P',
        'BZD' => '$',
        'CAD' => 'R$',
        'CDF' => 'Fr',
        'CHF' => 'Fr',
        'CLP' => '$',
        'CNY' => '¥',
        'COP' => '$',
        'CRC' => '₡',
        'CVE' => '$',
        'CZK' => 'Kč',
        'DJF' => 'Fr',
        'DKK' => 'kr',
        'DOP' => '$',
        'DZD' => 'د.ج',
        'EGP' => '€',
        'ETB' => 'ج.م',
        'EUR' => '€',
        'FJD' => '$',
        'FKP' => '£',
        'GBP' => '£',
        'GEL' => 'ლ',
        'GIP' => '£',
        'GMD' => 'D',
        'GNF' => 'Fr',
        'GTQ' => 'Q',
        'GYD' => '$',
        'HKD' => '$',
        'HNL' => 'L',
        'HRK' => 'kn',
        'HTG' => 'G',
        'HUF' => 'Ft',
        'IDR' => 'Rp',
        'ILS' => '₪',
        'INR' => '₹',
        'ISK' => 'kr',
        'JMD' => '$',
        'JPY' => '¥',
        'KES' => 'Sh',
        'KGS' => 'лв',
        'KHR' => '€',
        'KMF' => '៛',
        'KRW' => '₩',
        'KYD' => '$',
        'KZT' => '₸',
        'LAK' => '₭',
        'LBP' => 'ل.ل',
        'LKR' => 'Rs',
        'LRD' => '$',
        'LSL' => 'L',
        'MAD' => 'د.م.',
        'MDL' => 'L',
        'MGA' => 'Ar',
        'MKD' => 'ден',
        'MMK' => 'Ks',
        'MNT' => '₮',
        'MOP' => 'P',
        'MRO' => 'UM',
        'MUR' => '₨',
        'MVR' => '.ރ',
        'MWK' => 'MK',
        'MXN' => '$',
        'MYR' => 'RM',
        'MZN' => 'MT',
        'NAD' => '$',
        'NGN' => '₦',
        'NIO' => 'C$',
        'NOK' => 'kr',
        'NPR' => '₨',
        'NZD' => '$',
        'PAB' => 'B/.',
        'PEN' => 'S/.',
        'PGK' => 'K',
        'PHP' => '₱',
        'PKR' => '₨',
        'PLN' => 'zł',
        'PYG' => '₲',
        'QAR' => 'ر.ق',
        'RON' => 'L',
        'RSD' => 'дин.',
        'RUB' => 'руб.',
        'RWF' => 'Fr',
        'SAR' => 'ر.س',
        'SBD' => '$',
        'SCR' => '₨',
        'SEK' => 'kr',
        'SGD' => '$',
        'SHP' => '£',
        'SLL' => 'Le',
        'SOS' => 'Sh',
        'SRD' => '$',
        'STD' => 'Db',
        'SZL' => 'L',
        'THB' => '฿',
        'TJS' => 'ЅМ',
        'TOP' => 'T$',
        'TRY' => 'NULL',
        'TTD' => '$',
        'TWD' => '$',
        'TZS' => 'Sh',
        'UAH' => '₴',
        'UGX' => 'Sh',
        'UYU' => '$',
        'UZS' => 'лв',
        'VND' => '₫',
        'VUV' => 'Vt',
        'WST' => 'T',
        'XAF' => 'Fr',
        'XCD' => '$',
        'XOF' => 'Fr',
        'XPF' => 'Fr',
        'YER' => '﷼',
        'ZAR' => 'R',
        'ZMW' => 'ZK'
    ];

    /**
     * Get list of all currency
     *
     * @return array
     */
    public function findAll() : array
    {
        $currencyArray = [];
        foreach(self::SUPPORTED_CURRENCIES as $code => $symbol) {
            array_push($currencyArray, new Currency($code, $symbol));
        }
        return $currencyArray;
    }

    /**
     * Check request currency is available in currency list
     *
     * @param string $currencyCode
     * @return array
     */
    public function isSupported(string $currencyCode) : bool
    {
        $allCurrencyList = $this->findAll();

        foreach ($allCurrencyList as $currency) {
            // check system code and request code are same
            if ($currencyCode === $currency->code()) {
                return true;
            }
        }
        return false;
    }
}
