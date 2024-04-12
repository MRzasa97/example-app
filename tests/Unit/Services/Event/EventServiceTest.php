<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\Event\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use App\Models\Event;
use Carbon\Carbon;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Setup necessary data or configurations specific to these tests
    }

    public function test_it_retrieves_events_between_dates()
    {
        $event1 = Event::create([
            'start_time' => now()->toDateTimeString(),
            'from' => 'Location1',
            'type' => 'FLT'
        ]);
        $event2 = Event::create([
            'start_time' => now()->addDays(2)->toDateTimeString(),
            'from' => 'Location2',
            'type' => 'SBY'
        ]);

        $service = new EventService();
        $events = $service->getEventsBetweenDates(now()->toDateTimeString(), now()->addDays(3)->toDateTimeString());

        $this->assertInstanceOf(Collection::class, $events);
        $this->assertCount(2, $events);
    }

    public function test_it_retrieves_events_for_given_location()
    {
        $location = 'KRP';
        $event = Event::create([
            'start_time' => now()->toDateTimeString(),
            'from' => $location,
            'type' => 'FLT'
        ]);

        $service = new EventService();
        $events = $service->getEventsForGivenLocation($location);

        $this->assertInstanceOf(Collection::class, $events);
        $this->assertTrue($events->contains($event));
    }

    public function test_it_retrieves_events_for_next_week()
    {
        $nextWeekDate = Carbon::now()->next(Carbon::MONDAY);
        $event1 = Event::create([
            'start_time' => $nextWeekDate->toDateTimeString(),
            'from' => 'Location',
            'type' => 'FLT'
        ]);

        $event2 = Event::create([
            'start_time' => $nextWeekDate->toDateTimeString(),
            'from' => 'Location',
            'type' => 'SBY'
        ]);

        $service = new EventService();
        $events = $service->getEventsForNextWeek(now()->toDateString());

        $this->assertInstanceOf(Collection::class, $events);
        $this->assertTrue($events->contains($event1));
        $this->assertTrue($events->doesntContain($event2));
    }

    public function test_it_retrieves_sby_events_for_next_week()
    {
        $nextWeekDate = Carbon::now()->next(Carbon::MONDAY);
        $event1 = Event::create([
            'start_time' => $nextWeekDate->toDateTimeString(),
            'from' => 'Location',
            'type' => 'FLT'
        ]);

        $event2 = Event::create([
            'start_time' => $nextWeekDate->toDateTimeString(),
            'from' => 'Location',
            'type' => 'SBY'
        ]);

        $service = new EventService();
        $events = $service->getSBYEventsForNextWeek(now()->toDateString());

        $this->assertInstanceOf(Collection::class, $events);
        $this->assertTrue($events->contains($event2));
        $this->assertTrue($events->doesntContain($event1));
    }
}