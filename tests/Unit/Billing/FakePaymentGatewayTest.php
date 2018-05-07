<?php
/**
 * Created by PhpStorm.
 * User: connor11528
 * Date: 5/4/18
 * Time: 10:10 AM
 */
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use Tests\TestCase;


class FakePaymentGatewayTest extends TestCase
{
    /** @test */
    function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = new FakePaymentGateway;
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }

    /** @test */
    function charges_with_an_invalid_payment_token_fail()
    {
        $this->expectException(PaymentFailedException::class);

        $paymentGateway = new FakePaymentGateway;
        $paymentGateway->charge(2500, 'invalid-payment-token');
    }
}