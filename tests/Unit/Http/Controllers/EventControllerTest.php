<?php

namespace Tests\Unit\Http\Controllers;

use App\Factories\RosterParserFactory;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Services\Event\Interfaces\EventServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_index_returns_a_test_message()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->get('/events');

        $response->assertStatus(200);
        $response->assertExactJson(['OK']);
    }

    public function test_get_events_between_dates_fails_if_dates_missing()
    {
        $response = $this->json('GET', '/events/flights/', []);
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message', 'errors' => ['start_date', 'end_date']
        ]);
    }

    public function test_get_events_between_fails_if_end_date_is_sooner_then_start_date()
    {
        $response = $this->json('GET', '/events/flights', ['start_date' => '2022-01-10', 'end_date' => '2022-01-09']);
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message', 'errors' => ['end_date']
        ]);
    }

    public function test_get_events_between_returns_events_successfully()
    {
        $this->withoutExceptionHandling();
        $events = new Collection([new Event(['type' => 'FLT']), new Event(['type' => 'FLT'])]);
        $mockService = $this->mock(EventServiceInterface::class);
        $mockService->shouldReceive('getEventsBetweenDates')
                ->once()
                ->with('2022-01-01', '2022-01-20')
                ->andReturn($events);
        
        $response = $this->json('GET', '/events/flights', ['start_date' => '2022-01-01', 'end_date' => '2022-01-20']);
        $response->assertStatus(200);
        // $this->assertInstanceOf(JsonResponse::class, $response);
        $resourceResponse = EventResource::collection($events)->response()->getContent();
        $response->assertJson(json_decode($resourceResponse, true));
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
        $response = $this->json('GET', '/events/flights/location', ['location' => 'KR']);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors' => ['location']]);
    }

    public function test_get_events_for_given_location_returns_events_successfully()
    {
        $this->withoutExceptionHandling();

        $events = new Collection([new Event(['to' => 'KRK']), new Event(['to' => 'KRK'])]);
        $mockService = $this->mock(EventServiceInterface::class);
        $mockService->shouldReceive('getEventsForGivenLocation')
                    ->once()
                    ->with('KRK')
                    ->andReturn($events);

        $response = $this->json('GET', '/events/flights/location', ['location' => 'KRK']);
        $resourceResponse = EventResource::collection($events)->response()->getContent();
        $response->assertJson(json_decode($resourceResponse, true));
    }

    public function test_get_events_for_next_week_successfully()
    {
        $this->withoutExceptionHandling();

        $events = new Collection([new Event(['to' => 'KRK']), new Event(['to' => 'KRK'])]);
        $mockService = $this->mock(EventServiceInterface::class);
        $mockService->shouldReceive('getEventsForNextWeek')
        ->once()
        ->with('2022-01-14')
        ->andReturn($events);

        $response = $this->json('GET', 'events/flights/next-week');
        $resourceResponse = EventResource::collection($events)->response()->getContent();
        $response->assertJson(json_decode($resourceResponse, true));
    }

    public function test_get_sby_events_for_next_week_successfully()
    {
        $this->withoutExceptionHandling();
        $events = new Collection([new Event(['to' => 'KRK']), new Event(['to' => 'KRK'])]);
        $mockService = $this->mock(EventServiceInterface::class);
        $mockService->shouldReceive('getSBYEventsForNextWeek')
        ->once()
        ->with('2022-01-14')
        ->andReturn(new Collection($events));

        $response = $this->json('GET', 'events/flights/stand-by/next-week');
        $response->assertStatus(200);
        $resourceResponse = EventResource::collection($events)->response()->getContent();
        $response->assertJson(json_decode($resourceResponse, true));
    }

}