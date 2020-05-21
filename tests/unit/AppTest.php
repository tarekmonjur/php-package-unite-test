<?php

use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    protected $transactions;

    protected $rates;

    protected $calCom;


    public function setUp()
    {
        $this->transactions = explode("\n", file_get_contents(__DIR__.'./../input.txt'));
        $this->rates = @json_decode(file_get_contents(__DIR__.'./../rates.txt'), true)['rates'];
        $this->calCom = new CalculateCommission($this->transactions, $this->rates);
    }

    public function testCardIsEu()
    {
        $card_number = @json_decode($this->transactions[0], true)['bin'];
        $this->assertTrue($this->calCom->isEu($card_number));
    }

    public function testGenerateFixedAmount()
    {
        $transaction = @json_decode($this->transactions[0]);
        $fixedAmount = $this->calCom->generateAmount($transaction);
        $this->assertEquals($fixedAmount, 100);

        $transaction = @json_decode($this->transactions[1]);
        $fixedAmount2 = $this->calCom->generateAmount($transaction);
        $this->assertEquals($fixedAmount2, 45.63);
    }

    public function testCommission()
    {
        $transaction = @json_decode($this->transactions[3]);
        $commission = $this->calCom->commission($transaction);
        $this->assertEquals($commission, 2.37);
    }
}