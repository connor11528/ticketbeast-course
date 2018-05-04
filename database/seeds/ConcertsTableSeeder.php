<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ConcertsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Concert::class, 1)->states('published')->create([
            'title'                  => "Slayer",
            'subtitle'               => "with Forbidden and Testament",
            'additional_information' => 'some other content',
            'venue'                  => "The Rock Pile",
            'venue_address'          => "55 Sample Blvd",
            'city'                   => "Laraville",
            'state'                  => "ON",
            'zip'                    => "19276",
            'date'                   => Carbon::today()->addMonths(3)->hour(19),
            'ticket_price'           => 3250
        ]);
    }
}
