<?php

namespace Tests\Feature;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ViewConcertListingTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_a_published_concert_listing()
    {
        // Arrange
        $concert = Concert::create([
           'title'                  => 'The Red Chord',
           'subtitle'               => 'with Animosity and Lethargy',
           'date'                   => Carbon::parse('December 13, 2018 8:00pm'),
           'ticket_price'           => 3250,
           'venue'                  => 'The Mosh Pit',
           'venue_address'          => '123 Example Lane',
           'city'                   => 'San Francisco',
           'state'                  => 'CA',
           'zip'                    => '17916',
           'additional_information' => 'For tickets call 415-717-5557',
           'published_at'           => Carbon::parse('December 06, 2018 8:00pm')
        ]);

        // Act
        $this->visit('/concerts/' . $concert->id);

        // Assert
        $this->see('The Red Chord');
        $this->see('with Animosity and Lethargy');
        $this->see('December 13, 2018');
        $this->see('8:00pm');
        $this->see('32.50');
        $this->see('The Mosh Pit');
        $this->see('123 Example Lane');
        $this->see('San Francisco, CA 17916');
        $this->see('For tickets call 415-717-5557');
    }

    /** @test */
    function user_cannot_view_unpublished_concert_listings()
    {
        $concert = factory(Concert::class)->create([
            'published_at' => null
        ]);
        $this->get('/concerts' . $concert->id);
        $this->assertResponseStatus(404);
    }
}
