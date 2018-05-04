<?php
/**
 * Created by PhpStorm.
 * User: connor11528
 * Date: 5/4/18
 * Time: 10:00 AM
 */
namespace App\Billing;

interface PaymentGateway
{
    public function charge($amount, $token);
}