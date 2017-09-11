<?php

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewConcertListingTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testConcertViewing()
    {
        // Arrange

        // Create a concert

        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('December 13, 2016 20:00:00')
        ]);

        // Act

        // View a concert list

        $this->visit('/concerts/'.$concert->id);

        // Assert

        // See the concert details

        $this->see('The Red Chord');
        $this->see('with Animosity and Lethargy');
        $this->see('December 13, 2016');
        $this->see('8:00pm');
        $this->see('32.50');
        $this->see('The Mosh Pit');
        $this->see('123 Example Lane');
        $this->see('Laraville, ON 17916');
    }
}
