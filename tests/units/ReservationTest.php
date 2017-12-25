<?php

use App\Models\Concert;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Exceptions\NotEnoughTicketsException;

class ReservationTest extends TestCase
{
    use DatabaseMigrations;

    public function testCalculateTotalCost()
    {
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 1200]);
        $concert->addTickets(3);

        $tickets = $concert->findTickets(3);

        $reservation = new Reservation($tickets);

        $this->assertEquals(3600, $reservation->totalCost());
    }
}
