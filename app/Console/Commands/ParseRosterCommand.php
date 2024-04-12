<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\Carbon;
use App\Models\Event;
use Illuminate\Support\Collection;

class ParseRosterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-roster-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $htmlContent = file_get_contents(storage_path('/app/public/Roster - CrewConnex.html'));
        if($htmlContent)
        {
            $clawler = new Crawler($htmlContent);
            $lastDate = '';
            $periodText = $clawler->filter('div.row:nth-child(12) > b:nth-child(1)')->text();
            // Assuming the period format is like "13Dec21 - 26Dec21"
            preg_match('/(\d{2}[A-Za-z]{3}\d{2}) to (\d{2}[A-Za-z]{3}\d{2})/', $periodText, $matches);
            $startPeriod = Carbon::createFromFormat('dMy', $matches[1]);
            $endPeriod = Carbon::createFromFormat('dMy', $matches[2]);
            $clawler->filter('table.activityTableStyle > tbody > tr')->each(function(Crawler $node, $i) use (&$lastDate, &$startPeriod, &$endPeriod){
                $activityCode = $node->filter('.activitytablerow-activity')->text();
                if($activityCode == 'Activity') {
                    return;
                }
                $periodDate = null;
                $parsedCode = $this->determineCode($activityCode);
                $dateText = $node->filter('.activitytablerow-date')->text();
                
                // Parsing date might require custom logic depending on format
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

                $date = $this->setMonthYear($startPeriod, $endPeriod, $date);

                $from = $node->filter('.activitytablerow-fromstn')->text();
                $to = $node->filter('.activitytablerow-tostn')->text();
                $stdUtc = $node->filter('.activitytablerow-stdutc')->text();
                $staUtc = $node->filter('.activitytablerow-stautc')->text();

                if($parsedCode == 'FLT' || $parsedCode == 'SBY')
                {
                    $stdTimestamp = Carbon::createFromTimeStamp(strtotime($stdUtc))->setDateFrom($date);
                    $staTimestamp = Carbon::createFromTimeStamp(strtotime($staUtc))->setDateFrom($date);
                }
                else {
                    $stdTimestamp = null;
                    $staTimestamp = null;
                }
         
                Event::create([
                    'type' => $parsedCode,
                    'flight_number' => $parsedCode == 'FLT' ? $activityCode : null,
                    'start_time' => $stdTimestamp,
                    'end_time' => $staTimestamp,
                    'from' => $from,
                    'to' => $to,
                    'event_date' => $date
                ]);
            });
        }
    }

    public function determineCode(string $code)
    {
        $type = match (true) {
            $code === 'OFF' => 'DO',
            $code === 'SBY' => 'SBY',
            preg_match('/^[A-Za-z]{2}\d+$/', $code) === 1 => 'FLT',
            default => 'UNK',
        };
    
        return $type;
    }

    public function setMonthYear(Carbon $startDate, Carbon $endDate, Carbon $date)
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
}
