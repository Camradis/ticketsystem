<?php

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Exceptions\NotEnoughTicketsException;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    public function testTicketHasBeenReserved()
    {
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reserved_at);
        $ticket->reserve();
        $this->assertNotNull($ticket->fresh()->reserved_at);
    }

    public function testTicketHasBeenReleased()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(1);

        $order = $concert->orderTickets('andy@example.com' , 1);

        $ticket = $order->tickets()->first();
        $this->assertEquals($order->id, $ticket->order_id);

        $ticket->release();

        $this->assertNull($ticket->fresh()->order_id);
    }
}
