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

class EventController extends Controller
{
    public function __construct(
        public readonly EventServiceInterface $eventServiceInterface
    )
    {
        
    }

    public function index(Request $request)
    {
        return response()->json('test');
    }

    public function getEventsBetweenDates(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $events = $this->eventServiceInterface->getEventsBetweenDates($startDate, $endDate);

        return response()->json($events);
    }

    public function getEventsForGivenLocation(Request $request)
    {
        $location = $request->location;
        $events = $this->eventServiceInterface->getEventsForGivenLocation($location);

        return response()->json($events);
    }

    public function getEventsForNextWeek(Request $request)
    {
        $events = $this->eventServiceInterface->getEventsForNextWeek('2022-01-14');

        return response()->json($events);
    }

    public function getSBYEventsForNextWeek(Request $request)
    {
        $events = $this->eventServiceInterface->getSBYEventsForNextWeek('2022-01-14');
        return response()->json($events);
    }

    public function uploadRoster(Request $request)
    {
        $request->validate([
            'roster_file' => 'required|file|mimes:html,txt,pdf',
        ]);

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
