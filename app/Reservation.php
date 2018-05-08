<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    private $tickets;

    public function __construct($tickets)
    {
        $this->tickets = $tickets;
    }

    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    public function cancel()
    {
        foreach($this->tickets as $ticket){
            $ticket->release();
        }
    }

    public function tickets()
    {
        return $this->tickets;
    }
}
