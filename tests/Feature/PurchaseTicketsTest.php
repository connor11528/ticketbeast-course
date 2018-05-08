<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
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
    use WithoutMiddleware;

    protected function setUp()
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    private function orderTickets($concert, $params)
    {
        $savedRequest = $this->app['request'];
        $response = $this->json('POST', "/concerts/{$concert->id}/orders", $params);
        $this->app['request'] = $savedRequest;
        return $response;
    }

    private function assertValidationError($field)
    {
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey($field, array_get($this->decodeResponseJson(), 'errors'));
    }

    /** @test */
    function customer_can_purchase_tickets_to_a_published_concert()
    {
        $this->withoutExceptionHandling();
        // Arrange
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3250])->addTickets(3);

        // Act
        $this->orderTickets(
            $concert,
            [
                'email'           => 'john@example.com',
                'ticket_quantity' => 3,
                'payment_token'   => $this->paymentGateway->getValidTestToken(),
            ]
        );

        // Assert
        $this->assertResponseStatus(201);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        $this->assertTrue($concert->hasOrderFor('john@example.com'));

        // make sure has one order with three tickets
        $this->assertEquals(3, $concert->ordersFor('john@example.com')->first()->ticketQuantity());
    }

    /** @test */
    function cannot_purchase_tickets_to_an_unpublished_concert()
    {
        $concert = factory(Concert::class)->states('unpublished')->create()->addTickets(3);

        $this->orderTickets(
            $concert,
            [
                'email'           => 'john@example.com',
                'ticket_quantity' => 3,
                'payment_token'   => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $this->assertResponseStatus(404);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

//    /** @test */
//    function cannot_purchase_tickets_another_customer_is_already_trying_to_purchase()
//    {
//        $concert = factory(Concert::class)->states('published')->create([
//            'ticket_price' => 1200,
//        ])->addTickets(3);
//
//        $this->paymentGateway->beforeFirstCharge(function($paymentGateway) use ($concert) {
//            $this->orderTickets($concert, [
//                'email'           => 'personB@example.com',
//                'ticket_quantity' => 1,
//                'payment_token'   => $this->paymentGateway->getValidTestToken(),
//            ]);
//
//            $this->assertResponseStatus(422);
//            $this->assertFalse($concert->hasOrderFor('personB@example.com'));
//            $this->assertEquals(0, $this->paymentGateway->totalCharges());
//        });
//
//        $this->orderTickets($concert, [
//            'email'           => 'personA@example.com',
//            'ticket_quantity' => 3,
//            'payment_token'   => $this->paymentGateway->getValidTestToken(),
//        ]);
//
//        $this->assertEquals(3600, $this->paymentGateway->totalCharges());
//        $this->assertTrue($concert->hasOrderFor('personA@example.com'));
//        $this->assertEquals(3, $concert->ordersFor('personA@example.com')->first()->ticketQuantity());
//    }

    /** @test */
    function email_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets(
            $concert,
            [
                'ticket_quantity' => 3,
                'payment_token'   => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $this->assertValidationError('email');
    }

    /** @test */
    function email_must_be_valid_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $this->orderTickets(
            $concert,
            [
                'email'           => 'not-an-email-address',
                'ticket_quantity' => 3,
                'payment_token'   => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $this->assertValidationError('email');
    }

    /** @test */
    function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $this->orderTickets(
            $concert,
            [
                'email'           => 'john@example.com',
                'ticket_quantity' => 0,
                'payment_token'   => $this->paymentGateway->getValidTestToken(),
            ]
        );
        $this->assertValidationError('ticket_quantity');
    }

    /** @test */
    function payment_token_is_required()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $this->orderTickets(
            $concert,
            [
                'email'           => 'valid@email.com',
                'ticket_quantity' => 3,
            ]
        );
        $this->assertValidationError('payment_token');
    }

    /** @test */
    function an_order_is_not_created_if_payment_fails()
    {
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3250])->addTickets(3);
        $this->orderTickets(
            $concert,
            [
                'email'           => 'john@example.com',
                'ticket_quantity' => 3,
                'payment_token'   => 'invalid-token-here',
            ]
        );

        $this->assertResponseStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ticketsRemaining());
    }

    /** @test */
    function cannot_purchase_more_tickets_than_remain()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(50);

        $this->orderTickets(
            $concert,
            [
                'email'           => 'john@example.com',
                'ticket_quantity' => 51,
                'payment_token'   => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $this->assertResponseStatus(422);

        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }
}