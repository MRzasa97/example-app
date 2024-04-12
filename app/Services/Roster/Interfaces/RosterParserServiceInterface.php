<?php

namespace App\Services\Roster\Interfaces;

interface RosterParserServiceInterface
{
    public function parse(string $filePath): void;
}