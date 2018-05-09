<?php

namespace Tests\Unit;

use App\Concert;
use App\Reservation;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function calculating_the_total_cost()
    {
        $tickets = collect([
            (object)['price' => 1200],
            (object)['price' => 1200],
            (object)['price' => 1200],
        ]);

        $reservation = new Reservation($tickets, 'john@example.com');

        $this->assertEquals(3600, $reservation->totalCost());
    }

    /** @test */
    function reserved_tickets_are_released_when_a_reservation_is_cancelled()
    {
        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);
        $reservation = new Reservation($tickets, 'jane@example.com');
        $reservation->cancel();
        foreach ($tickets as $ticket) {
            $ticket->shouldHaveReceived('release');
        }
    }

    /** @test */
    function retrieving_the_reservations_tickets()
    {
        $tickets = collect([
           (object)['price' => 1200],
           (object)['price' => 1200],
           (object)['price' => 1200],
        ]);
        $reservation = new Reservation($tickets, 'jane@example.com');
        $this->assertEquals($tickets, $reservation->tickets());
    }

    /** @test */
    function completing_a_reservation()
    {
        $concert  = factory(Concert::class)->create(['ticket_price' => 1200]);
        $tickets = factory(Ticket::class, 3)->create(['concert_id' => $concert->id]);
        $reservation = new Reservation($tickets, 'john@example.com');

        $order = $reservation->complete();

        // check that calling reservation->complete creates an order
        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);

    }
}
