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

    protected function setUp()
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    private function orderTickets($concert, $params)
    {
        $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    private function assertValidationError($field)
    {
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey($field, array_get($this->decodeResponseJson(), 'errors'));
    }

    /** @test */
    function customer_can_purchase_tickets_to_a_published_concert()
    {
        // Arrange
        $concert = factory(Concert::class)->states('published')->create([ 'ticket_price' => 3250 ]);
        $concert->addTickets(3);

        // Act
        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertResponseStatus(201);
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    function cannot_purchase_tickets_to_an_unpublished_concert()
    {
        $concert = factory(Concert::class)->states('unpublished')->create();
        $concert->addTickets(3);
        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertResponseStatus(404);
        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /** @test */
    function email_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError('email');
    }

    /** @test */
    function email_must_be_valid_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $this->orderTickets($concert, [
            'email'           => 'not-an-email-address',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError('email');
    }

    /** @test */
    function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);
        $this->assertValidationError('ticket_quantity');
    }

    /** @test */
    function payment_token_is_required()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $this->orderTickets($concert, [
            'email'           => 'valid@email.com',
            'ticket_quantity' => 3
        ]);
        $this->assertValidationError('payment_token');
    }

    /** @test */
    function an_order_is_not_created_if_payment_fails()
    {
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3250]);
        $this->orderTickets($concert, [
           'email'           => 'john@example.com',
           'ticket_quantity' => 3,
           'payment_token'   => 'invalid-token-here'
        ]);

        $this->assertResponseStatus(422);
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNull($order);
    }

    /** @test */
    function cannot_purchase_more_tickets_than_remain()
    {
        //$this->withoutExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(50);
        $this->orderTickets($concert, [
            'email'           => 'valid@email.com',
            'ticket_quantity' => 51,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertResponseStatus(422);
        $order = $concert->orders()->where('email', 'valid@email.com')->first();
        $this->assertNull($order);
        $this->assertEquals(50, $concert->ticketsRemaining());
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }
}