<?php

namespace Tests\Unit;

use App\Reservation;
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
        $tickets     = collect(
            [
                Mockery::spy(Ticket::class),
                Mockery::spy(Ticket::class),
                Mockery::spy(Ticket::class),
            ]
        );
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
}
