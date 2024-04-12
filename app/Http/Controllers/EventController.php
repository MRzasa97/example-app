<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;
use App\Services\Roster\RosterParserServiceInterface;
use Illuminate\Support\Facades\Storage;
use App\Factories\RosterParserFactory;
use App\Services\Event\Interfaces\EventServiceInterface;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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

    public function getEventsBetweenDates(Request $request): JsonResponse
    {
        $validator = Validator::make([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ], [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $events = $this->eventServiceInterface->getEventsBetweenDates($startDate, $endDate);

        return response()->json($events);
    }

    public function getEventsForGivenLocation(Request $request): JsonResponse
    {
        $validator = Validator::make([
            'location' => $request->location,
        ], [
            'location' => 'required|string|regex:/[a-zA-Z].*[a-zA-Z].*[a-zA-Z]/',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }


        $location = $request->location;
        $events = $this->eventServiceInterface->getEventsForGivenLocation($location);

        return response()->json($events);
    }

    public function getEventsForNextWeek(Request $request): JsonResponse
    {
        $events = $this->eventServiceInterface->getEventsForNextWeek('2022-01-14');

        return response()->json($events);
    }

    public function getSBYEventsForNextWeek(Request $request): JsonResponse
    {
        $events = $this->eventServiceInterface->getSBYEventsForNextWeek('2022-01-14');
        return response()->json($events);
    }

    public function uploadRoster(Request $request): JsonResponse
    {
        $validator = Validator::make([
            'file' => $request->file('roster_file'),
        ], [
            'file' => 'required|file|mimes:html,txt,pdf',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

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
