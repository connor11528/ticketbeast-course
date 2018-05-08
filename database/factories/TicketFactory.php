<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Ticket::class, function (Faker $faker) {
    return [
        'concert_id' => function () {
            return factory(App\Concert::class)->create()->id;
        },
    ];
});

$factory->state(App\Ticket::class, 'reserved', function (Faker $faker) {
    return [
        'reserved_at' => Carbon::now()
    ];
});
