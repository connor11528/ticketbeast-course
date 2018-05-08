<?php

use Faker\Generator as Faker;

$factory->define(App\Ticket::class, function (Faker $faker) {
    return [
        'concert_id' => function(){
            return factory(App\Concert::class)->create()->id;
        }
    ];
});
