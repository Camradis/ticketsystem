<?php

use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReservationTest extends TestCase
{
    use DatabaseMigrations;

    public function testCalculateTotalCost()
    {
        //$concert = factory(Concert::class)->states('published')->create(['ticket_price' => 1200]);
        //$concert->addTickets(3);
        //
        //$tickets = $concert->findTickets(3);

        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets);

        $this->assertEquals(3600, $reservation->totalCost());
    }
}
