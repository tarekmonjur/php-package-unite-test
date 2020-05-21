<?php

class CalculateCommission
{
    private $transactions;
    private $rates = [];

    public function __construct($transactions = [], $rates = [])
    {
        $this->transactions = $transactions;
        $this->rates = (empty($rates)) ?  $this->getRates() : $rates;
    }

    private function getRates()
    {
        $results = @json_decode(file_get_contents('https://api.exchangeratesapi.io/latest'), true);
        if ($results['rates']) {
            return $results['rates'];
        }
        return [];
    }

    public function generateAmount($transaction)
    {
        $amountFixed = 0;
        if (!$this->rates) {
            return $amountFixed;
        }

        $rate = isset($this->rates[$transaction->currency]) ? $this->rates[$transaction->currency] : 0;
        if ($transaction->currency == 'EUR' || $rate == 0) {
            $amountFixed = $transaction->amount;
        }

        if ($transaction->currency != 'EUR' || $rate > 0) {
            $amountFixed = $transaction->amount / $rate;
        }

        return round($amountFixed, 2);
    }

    public function commission($transaction)
    {
        $amount_fixed = $this->generateAmount($transaction);
        $isCardEu = $this->isEu($transaction->bin);
        $commission_amount = round(($amount_fixed * ($isCardEu ? 0.01 : 0.02)), 2);
        return $commission_amount;
    }

    public function generateCommissions()
    {
        $commission = [];
        foreach ($this->transactions as $transaction) {
            if (empty($transaction)) {
                break;
            }
            $transactionData = json_decode($transaction);
            $commission[] = $this->commission($transactionData);
        }
        return $commission;
    }

    public function isEu($card_number)
    {
        if (empty($card_number)) {
            return false;
        }
        $cardResult = @file_get_contents('https://lookup.binlist.net/' .$card_number);
        if (!$cardResult) {
            return false;
        }

        $cardInfo = json_decode($cardResult);
        $code = $cardInfo->country->alpha2;

        switch($code) {
            case 'AT':
            case 'BE':
            case 'BG':
            case 'CY':
            case 'CZ':
            case 'DE':
            case 'DK':
            case 'EE':
            case 'ES':
            case 'FI':
            case 'FR':
            case 'GR':
            case 'HR':
            case 'HU':
            case 'IE':
            case 'IT':
            case 'LT':
            case 'LU':
            case 'LV':
            case 'MT':
            case 'NL':
            case 'PO':
            case 'PT':
            case 'RO':
            case 'SE':
            case 'SI':
            case 'SK':
                $result = true;
                break;
            default:
                $result = false;
        }
        return $result;
    }
}