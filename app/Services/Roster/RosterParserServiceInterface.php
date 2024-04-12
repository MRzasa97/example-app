<?php

namespace App\Services\Roster;

interface RosterParserServiceInterface
{
    public function parse(string $filePath): void;
}