<?php

namespace Tests\Unit\Http\Controllers;

use App\Factories\RosterParserFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Services\Event\Interfaces\EventServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_index_returns_a_test_message()
    {
        $response = $this->get('/events');
        $response->assertStatus(200);
        $response->assertExactJson(['test']);
    }

    public function test_get_events_between_dates_fails_if_dates_missing()
    {
        $response = $this->json('GET', '/events/flights/ / ');
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message', 'errors' => ['start_date', 'end_date']
        ]);
    }

    public function test_get_events_between_fails_if_end_date_is_sooner_then_start_date()
    {
        $response = $this->json('GET', '/events/flights/2022-01-20/2022-01-19');
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message', 'errors' => ['end_date']
        ]);
    }

    public function test_get_events_between_returns_events_successfully()
    {
        $this->withoutExceptionHandling();

        $mockService = $this->mock(EventServiceInterface::class);
        $mockService->shouldReceive('getEventsBetweenDates')
                ->once()
                ->with('2022-01-01', '2022-01-20')
                ->andReturn(new Collection(['event1', 'event2']));
        
        $response = $this->json('GET', '/events/flights/2022-01-01/2022-01-20');
        $response->assertStatus(200);
        $response->assertJson(['event1', 'event2']);
    }

    public function test_upload_roster_handles_file_upload_successfully()
    {
        $path = base_path('tests/fixtures/Roster - CrewConnex.html');
        $fileContent = file_get_contents($path);

        $file = new UploadedFile(
            $path,
            'roster.html',
            'text/html',
            null,
            true
        );

        Storage::disk('local')->put('rosters/roster.html', $fileContent);

        $response = $this->json('POST', '/events/upload', ['roster_file' => $file]);
        $response->dump();
        $response->assertStatus(200);
        $response->assertJson(['message' => 'File uploaded successfully']);
    }

    public function test_get_events_for_given_location_fails_if_invalid_location_provided()
    {
        $response = $this->json('GET', '/events/flights/location/KR');
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors' => ['location']]);
    }

    public function test_get_events_for_given_location_returns_events_successfully()
    {
        $this->withoutExceptionHandling();

        $mockService = $this->mock(EventServiceInterface::class);
        $mockService->shouldReceive('getEventsForGivenLocation')
                    ->once()
                    ->with('KRK')
                    ->andReturn(new Collection(['event3', 'event4']));

        $response = $this->json('GET', '/events/flights/location/KRK');
        $response->assertStatus(200);
        $response->assertJson(['event3', 'event4']);
    }

    public function test_get_events_for_next_week_successfully()
    {
        $this->withoutExceptionHandling();
        $mockService = $this->mock(EventServiceInterface::class);
        $mockService->shouldReceive('getEventsForNextWeek')
        ->once()
        ->with('2022-01-14')
        ->andReturn(new Collection(['event3', 'event4']));

        $response = $this->json('GET', 'events/flights/next-week');
        $response->assertStatus(200);
        $response->assertJson(['event3', 'event4']);
    }

    public function test_get_sby_events_for_next_week_successfully()
    {
        $this->withoutExceptionHandling();
        $mockService = $this->mock(EventServiceInterface::class);
        $mockService->shouldReceive('getSBYEventsForNextWeek')
        ->once()
        ->with('2022-01-14')
        ->andReturn(new Collection(['event3', 'event4']));

        $response = $this->json('GET', 'events/flights/stand-by/next-week');
        $response->assertStatus(200);
        $response->assertJson(['event3', 'event4']);
    }

}