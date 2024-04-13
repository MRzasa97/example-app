<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;
use App\Services\Roster\RosterParserServiceInterface;
use Illuminate\Support\Facades\Storage;
use App\Factories\RosterParserFactory;
use App\Http\Requests\EventRequest;
use App\Http\Resources\EventResource;
use App\Services\Event\Interfaces\EventServiceInterface;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController extends Controller
{
    public function __construct(
        public readonly EventServiceInterface $eventServiceInterface
    )
    {
        
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json('test');
    }

    public function getEventsBetweenDates(EventRequest $request): AnonymousResourceCollection
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $events = $this->eventServiceInterface->getEventsBetweenDates($startDate, $endDate);

        return EventResource::collection($events);
    }

    public function getEventsForGivenLocation(EventRequest $request): AnonymousResourceCollection
    {
        $location = $request->input('location');
        $events = $this->eventServiceInterface->getEventsForGivenLocation($location);

        return EventResource::collection($events);
    }

    public function getEventsForNextWeek(Request $request): AnonymousResourceCollection
    {
        $events = $this->eventServiceInterface->getEventsForNextWeek('2022-01-14');

        return EventResource::collection($events);
    }

    public function getSBYEventsForNextWeek(Request $request): AnonymousResourceCollection
    {
        $events = $this->eventServiceInterface->getSBYEventsForNextWeek('2022-01-14');
        return EventResource::collection($events);
    }

    public function uploadRoster(EventRequest $request): JsonResponse
    {
        $file = $request->file('roster_file');

        $filename = time() . '-' . $file->getClientOriginalName();
        $fileExtension = $file->getClientOriginalExtension();

        $path = $file->storeAs('public/rosters', $filename);

        try {
            $parser = RosterParserFactory::create($fileExtension);
            $parser->parse($path);
            Storage::delete($path);
            return response()->json(['message' => 'File uploaded successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
