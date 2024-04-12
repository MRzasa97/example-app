<?php

namespace App\Factories;

use App\Services\Roster\RosterParserServiceInterface;
use App\Services\Roster\HtmlRosterParserService;

class RosterParserFactory {
    public static function create($fileExtension): RosterParserServiceInterface {
        switch($fileExtension)
        {
            case 'html':
                return new HtmlRosterParserService();
            default:
                throw new \Exception("Parser for specifier extension {$fileExtension} doesn't exist");
        }
    }
}