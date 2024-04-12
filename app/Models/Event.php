<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'type',
        'start_time',
        'end_time',
        'flight_number',
        'from',
        'to',
        'event_date'
    ];

    protected $cast = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];
}
