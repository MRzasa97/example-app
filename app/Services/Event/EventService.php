<?php

namespace App\Services\Event;

use App\Services\Event\Interfaces\EventServiceInterface;
use Facades\App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class EventService implements EventServiceInterface
{
    public function getEventsBetweenDates(string $startDate, string $endDate): Collection
    {
        $events = Event::whereBetween('start_time', [$startDate, $endDate])->get();

        return $events;
    }

    public function getEventsForGivenLocation(string $location): Collection
    {
        $events = Event::where('from', $location)->get();

        return $events;
    } 

    public function getEventsForNextWeek(string $date): Collection
    {
        $currentDate = Carbon::createFromFormat('Y-m-d', $date);

        $dates = $this->getNextWeekStartAndEndDate($currentDate);

        $events = Event::where('type', 'FLT')
                       ->whereBetween('start_time', [$dates['start_date'], $dates['end_date']])
                       ->get();

        return $events;
    }

    public function getSBYEventsForNextWeek(string $date): Collection
    {
        $currentDate = Carbon::createFromFormat('Y-m-d', $date);
        
        $dates = $this->getNextWeekStartAndEndDate($currentDate);

        $events = Event::where('type', 'SBY')
                       ->whereBetween('start_time', [$dates['start_date'], $dates['end_date']])
                       ->get();

        return $events;
    }

    private function getNextWeekStartAndEndDate(Carbon $date): array
    {
        $nextMonday = $date->copy()->next(Carbon::MONDAY);
    
        $nextSunday = $nextMonday->copy()->endOfWeek();
    
        $startDate = $nextMonday->startOfDay()->toDateTimeString();

        $endDate = $nextSunday->endOfDay()->toDateTimeString();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }
}