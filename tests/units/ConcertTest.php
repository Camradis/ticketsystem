<?php

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    public function testCanGetFormattedDate()
    {
        // Create concert with a known date
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('December 13, 2016 8:00pm')
        ]);

        // Retrieve the formatted date
        $date = $concert->formatted_date;

        // Verify the date is formatted as expected
        $this->assertEquals('December 13, 2016' , $date);
    }

    public function testCanGetFormattedStartTime()
    {
        // Create concert with a known date
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('December 13, 2016 17:00:00')
        ]);

        // Retrieve the formatted date
        $start_time = $concert->formatted_start_time;

        // Verify the date is formatted as expected
        $this->assertEquals('5:00pm' , $start_time);
    }

    public function testCanGetFormattedTicketPrice()
    {
        // Create concert with a known date
        $concert = factory(Concert::class)->make([
            'ticket_price' => 6750,
        ]);

        // Retrieve the formatted date
        $ticket_price = $concert->ticket_price_in_dollars;

        // Verify the date is formatted as expected
        $this->assertEquals('67.50' , $ticket_price);
    }

    public function testCanGetPublishedConcert()
    {
        $published_concertA = factory(Concert::class)->states('published')->create();
        $published_concertB = factory(Concert::class)->states('published')->create();
        $unpublished_concert = factory(Concert::class)->states('unpublished')->create();

        $published_concerts = Concert::published()->get();

        $this->assertTrue($published_concerts->contains($published_concertA));
        $this->assertTrue($published_concerts->contains($published_concertB));
        $this->assertFalse($published_concerts->contains($unpublished_concert));
    }

    public function testCanOrderConcertTicket()
    {
        $concert = factory(Concert::class)->create();

        $order = $concert->orderTickets('jane@example.com' , 3);

        $this->assertEquals('jane@example.com' , $order->email);
        $this->assertEquals(3, $order->tickets->count());
    }
}
