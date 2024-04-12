<?php

namespace Tests\Unit\Factories;

use App\Factories\RosterParserFactory;
use App\Services\Roster\HtmlRosterParserService;
use PHPUnit\Framework\TestCase;

class RosterParserFactoryTest extends TestCase
{
    public function test_it_returns_html_roster_parser_service_for_html_extension()
    {
        $parser = RosterParserFactory::create('html');
        $this->assertInstanceOf(HtmlRosterParserService::class, $parser);
    }

    public function test_it_throws_exception_for_unsupported_extensions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Parser for specifier extension txt doesn't exist");

        RosterParserFactory::create('txt');
    }
}