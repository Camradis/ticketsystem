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
        $concert = factory(Concert::class)->create([
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
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('December 13, 2016 17:00:00')
        ]);

        // Retrieve the formatted date
        $start_time = $concert->formatted_start_time;

        // Verify the date is formatted as expected
        $this->assertEquals('5:00pm' , $start_time);
    }
}
