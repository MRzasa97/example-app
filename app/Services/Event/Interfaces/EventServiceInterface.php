<?php

namespace App\Services\Event\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Event;

interface EventServiceInterface
{
    public function getEventsBetweenDates(string $startDate, string $endDate): Collection;
    public function getEventsForGivenLocation(string $location): Collection;
    public function getEventsForNextWeek(string $date): Collection;
    public function getSBYEventsForNextWeek(string $date): Collection;
}