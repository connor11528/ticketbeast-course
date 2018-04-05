<?php

namespace Tests\Unit;

use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function can_get_formatted_date()
    {
        // Create a concert with a known date
        $concert = factory(Concert::class, 1)->create([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        // Retrieve the formatted date
        $date = $concert[0]->formatted_date;

        // Verify the date is formatted as expected
        $this->assertEquals('December 1, 2016', $date);
    }
}