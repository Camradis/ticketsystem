<?php

use App\Models\Concert;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Exceptions\NotEnoughTicketsException;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    public function testCreatingOrderFromTicketsEmailAndAmount()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(5);

        $this->assertEquals(5, $concert->ticketsRemaining());

        $order= Order::forTickets($concert->findTickets(3), 'andy@example.com', 3600);

        $this->assertEquals('andy@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(2, $concert->ticketsRemaining());
        $this->assertEquals(3600, $order->amount);
    }

    public function testTicketsAreReleasedWhenOrderIsCancelled()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);
        $order = $concert->orderTickets('andy@example.com' , 5);

        $this->assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();

        $this->assertEquals(10, $concert->ticketsRemaining());
        $this->assertNull(Order::find($order->id));
    }

    public function testConvertingToArray()
    {
        $concert = factory(Concert::class)->create(['ticket_price' => 1200,]);
        $concert->addTickets(5);
        $order = $concert->orderTickets('andy@example.com' , 5);

        $result = $order->toArray();

        $this->assertEquals([
            'email' => 'andy@example.com',
            'ticket_quantity' => 5,
            'amount' => 6000,
        ], $result);
    }
}
