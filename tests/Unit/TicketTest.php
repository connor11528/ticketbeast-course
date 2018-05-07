<?php
/**
 * Created by PhpStorm.
 * User: connor11528
 * Date: 5/7/18
 * Time: 4:07 PM
 */

namespace Tests\Unit;

use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_ticket_can_be_released()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(1);
        $order = $concert->orderTickets('jane@example.com', 1);
        $ticket = $order->tickets()->first();

        $this->assertEquals($order->id, $ticket->order_id);
        $ticket->release();
        $this->assertNull($ticket->fresh()->order_id);
    }
}