<?php
declare(strict_types=1);

namespace App\Services\Roster;

use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\Carbon;
use Facades\App\Models\Event;
use Illuminate\Support\Collection;
use App\Services\Roster\Interfaces\RosterParserServiceInterface;
use Exception;
use Illuminate\Support\Facades\Storage;

class HtmlRosterParserService implements RosterParserServiceInterface
{
    public function parse(string $filePath): void
    {
        
        if(Storage::disk('local')->exists($filePath))
        {
            $htmlContent = Storage::disk('local')->get($filePath);
            $clawler = new Crawler($htmlContent);
            $lastDate = '';
            $periodText = $clawler->filter('div.row:nth-child(12) > b:nth-child(1)')->text();
            // Assuming the period format is like "13Dec21 - 26Dec21"
            $dateRange = $this->validateDateRange($periodText);
            $clawler->filter('table.activityTableStyle > tbody > tr')->each(function(Crawler $node, $i) use (&$lastDate, &$dateRange){
                $activityCode = $node->filter('.activitytablerow-activity')->text();
                if($activityCode == 'Activity') {
                    return;
                }

                $parsedCode = $this->determineCode($activityCode);
                $dateText = $node->filter('.activitytablerow-date')->text();
                $date = null;

                $this->parseAndValidateDate($dateText, $lastDate, $date);

                $date = $this->setMonthYear($dateRange['start_period'], $dateRange['end_period'], $date);

                $from = $node->filter('.activitytablerow-fromstn')->text();
                $to = $node->filter('.activitytablerow-tostn')->text();
                $stdUtc = $node->filter('.activitytablerow-stdutc')->text();
                $staUtc = $node->filter('.activitytablerow-stautc')->text();

                $timestamps = $this->parseTimestamp($stdUtc, $staUtc, $date, $parsedCode);
         
                Event::create([
                    'type' => $parsedCode,
                    'flight_number' => $parsedCode == 'FLT' ? $activityCode : null,
                    'start_time' => $timestamps['std_timestamp'],
                    'end_time' => $timestamps['std_timestamp'],
                    'from' => $from,
                    'to' => $to,
                    'event_date' => $date
                ]);
            });
        } else {
            throw new \Exception("Failed to read file content.");
        }
    }

    private function determineCode(string $code): string
    {
        $type = match (true) {
            $code === 'OFF' => 'DO',
            $code === 'SBY' => 'SBY',
            preg_match('/^[A-Za-z]{2}\d+$/', $code) === 1 => 'FLT',
            default => 'UNK',
        };
    
        return $type;
    }

    private function setMonthYear(Carbon $startDate, Carbon $endDate, Carbon $date): Carbon
    {
        $periodDate = clone $startDate;
        while($periodDate->lte($endDate))
        {
            if($periodDate->day == $date->day)
            {
                $date->month($periodDate->month);
                $date->year($periodDate->year);
            }
            $periodDate->addDay();
        }
        return $date;
    }

    private function parseTimestamp(string $stdUtc, string $staUtc, Carbon $date, string $parsedCode): ?array
    {
        if($parsedCode == 'FLT' || $parsedCode == 'SBY')
        {
            $stdTimestamp = Carbon::createFromTimeStamp(strtotime($stdUtc))->setDateFrom($date);
            $staTimestamp = Carbon::createFromTimeStamp(strtotime($staUtc))->setDateFrom($date);
        }
        else {
            $stdTimestamp = null;
            $staTimestamp = null;
        }

        return [
            'std_timestamp' => $stdTimestamp,
            'sta_timestamp' => $staTimestamp
        ];
    }

    private function parseAndValidateDate(string &$dateText, string &$lastDate, ?Carbon &$date): void
    {
        if($dateText != $lastDate && $dateText != '' && $lastDate != '')
        {
            $date = Carbon::createFromFormat('d', explode(" ", $dateText)[1])->startOfDay();
            $lastDate = $dateText;
        }
        else if($dateText != $lastDate && $dateText == '' && $lastDate != '') {
            $date = Carbon::createFromFormat('d', explode(" ", $lastDate)[1])->startOfDay();
        }
        else {
            $date = Carbon::createFromFormat('d', explode(" ", $dateText)[1])->startOfDay();
            $lastDate = $dateText;
        }
    }

    private function validateDateRange(string $periodText): array
    {
        if(!preg_match('/(\d{2}[A-Za-z]{3}\d{2}) to (\d{2}[A-Za-z]{3}\d{2})/', $periodText, $matches))
        {
            throw new Exception("Date format not recognized.");
        }
        return [
            'start_period' => Carbon::createFromFormat('dMy', $matches[1]),
            'end_period' => Carbon::createFromFormat('dMy', $matches[2])
        ];
    }
}