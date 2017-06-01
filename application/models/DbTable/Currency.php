<?php

class Application_Model_DbTable_Currency extends Zend_Db_Table_Abstract
{

    protected $_name = 'quotes';

    public static $currenciesNames = [
        'AUD' => 'Australian Dollar',
        'BTC' => 'Bitcoin',
        'CAD' => 'Canadian Dollar',
        'CHF' => 'Swiss Franc',
        'CNY' => 'Chinese Yuan',
        'CZK' => 'Czech Republic Koruna',
        'DKK' => 'Danish Krone',
        'EEK' => 'Estonian Kroon',
        'EUR' => 'Euro',
        'GBP' => 'British Pound Sterling',
        'HKD' => 'Hong Kong Dollar',
        'ILS' => 'Israeli New Sheqel',
        'JPY' => 'Japanese Yen',
        'LVL' => 'Latvian Lats',
        'PLN' => 'Polish Zloty',
        'SAR' => 'Saudi Riyal',
        'SEK' => 'Swedish Krona',
        'UAH' => 'Ukrainian Hryvnia',
        'USD' => 'United States Dollar',
        'XAU' => 'Gold (troy ounce)',
    ];

    /**
     * Get stored exchange rate for specific currency relatively USD
     *
     * @param $quote String
     * @return mixed Array
     */
    public function getCurrencyByQuote($quote)
    {

        $row = $this->fetchRow('quote = \'' . $quote . '\'');
        if (!$row) {
            throw new Exception("Could not find row $quote");
        }

        return $row->toArray();

    }

    /**
     * Store exchange rate for specific currency relatively USD
     *
     * @param $quote String
     * @param $rate Array
     */
    public function addCurrency($quote, $rate)
    {
        $this->insert([
            'quote' => $quote,
            'rate' => $rate,
        ]);
    }

    /**
     * Update stored exchange rate for specific currency relatively USD
     *
     * @param $quote String
     * @param $rate Array
     */
    public function updateCurrency($quote, $rate)
    {

        $this->update([
            'rate' => $rate
        ], 'quote = \'' . $quote . '\'');

    }

    /**
     * Get real and actual rates
     *
     * @return bool
     */
    public function getExchangeRates()
    {

        // set API Endpoint and access key (and any options of your choice)
        $endpoint = 'live';
        $access_key = '2f60e5bb67cbd6f88cfb1cae350ed997';
        $currencies = '&currencies=AUD,BTC,CAD,CHF,CNY,CZK,DKK,EEK,EUR,GBP,HKD,ILS,JPY,LVL,PLN,SAR,SEK,UAH,USD,XAU';

        // Initialize CURL:
        $ch = curl_init('http://apilayer.net/api/' . $endpoint . '?access_key=' . $access_key . $currencies);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Store the data:
        $json = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response:
        $exchangeRates = json_decode($json, true);

        if (
            isset($exchangeRates['success']) && ($exchangeRates['success'] == 1) &&
            isset($exchangeRates['quotes']) && is_array($exchangeRates['quotes'])
        ) {

            return $exchangeRates['quotes'];

        }

        return false;

    }

    /**
     * Storing exchange rates
     *
     * @param $exchangeRates
     */
    public function storeExchangeRates($exchangeRates)
    {

        foreach ($exchangeRates as $quote => $rate) {
            // Cut 'USD' from beginning of every quote
            $quote = substr($quote, 3);
            $this->addCurrency($quote, $rate);

        }

    }

    /**
     * Calculate result amount based on cuurencies and amount
     *
     * @param $currencyIn String
     * @param $currencyOut String
     * @param $amountIn Float
     * @return float|string
     */
    public function calculateAmount($currencyIn, $currencyOut, $amountIn)
    {

        switch (true) {
            case ($currencyIn == 'USD'):
                $rateIn = $this->getCurrencyByQuote($currencyOut)['rate'];
                $amountOut = $amountInDollars = $amountIn * $rateIn;
                break;

            case ($currencyOut == 'USD'):
                $rateIn = (float)$this->getCurrencyByQuote($currencyIn)['rate'];
                $amountOut = $amountInDollars = $amountIn / $rateIn;
                break;

            case ($currencyIn != 'USD') && ($currencyOut != 'USD'):
                $rateIn = (float)$this->getCurrencyByQuote($currencyIn)['rate'];
                $rateOut = (float)$this->getCurrencyByQuote($currencyOut)['rate'];
                $amountOut = ($amountIn / $rateIn) * $rateOut;
                break;
            default:
                $amountOut = $amountIn;
        }

        $amountOut = money_format('%+n', $amountOut);
        $this->storeHistory($currencyIn, $amountIn, $amountOut, $currencyOut);

        return $amountOut;

    }

    /**
     * Store exchange history
     *
     * @param $currencyIn String
     * @param $amount Float
     * @param $result Float
     * @param $currencyOut String
     */
    public function storeHistory($currencyIn, $amount, $result, $currencyOut)
    {
        $history = new Application_Model_DbTable_History();
        $history->addHistory(self::$currenciesNames[$currencyIn], $amount, $result, self::$currenciesNames[$currencyOut]);

    }

}
