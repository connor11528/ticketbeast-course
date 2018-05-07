<?php
/**
 * Created by PhpStorm.
 * User: connor11528
 * Date: 5/4/18
 * Time: 9:59 AM
 */

namespace App\Billing;

class FakePaymentGateway implements PaymentGateway
{
    private $charges;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidTestToken()
    {
        return "valid-token";
    }

    public function charge($amount, $token)
    {
        if($token !== $this->getValidTestToken()){
            throw new PaymentFailedException;
        }
        $this->charges[] = $amount;
    }


    public function totalCharges()
    {
        return $this->charges->sum();
    }
}