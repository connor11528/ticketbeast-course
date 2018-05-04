<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * Created by PhpStorm.
 * User: connor11528
 * Date: 5/3/18
 * Time: 4:08 PM
 */

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function customer_can_purchase_concert_tickets()
    {
        // $this->withExceptionHandling();
        // $this->withExceptionHandling();

        // Arrange
        $paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $paymentGateway);
        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250
        ]);

        // Act
        $this->json('POST', "/concerts/{$concert->id}/orders", [
           'email' => 'john@example.com',
           'ticket_quantity' => 3,
           'payment_token' => $paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertResponseStatus(201);
        $this->assertEquals(9750, $paymentGateway->totalCharges());

        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets->count());

    }
}