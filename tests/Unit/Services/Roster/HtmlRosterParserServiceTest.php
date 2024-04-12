<?php

namespace Tests\Unit\Services\Roster;

use Tests\TestCase;
use App\Services\Roster\HtmlRosterParserService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Event;
use Illuminate\Http\UploadedFile;

class HtmlRosterParserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_it_throws_exception_if_file_does_not_exist()
    {
        $service = new HtmlRosterParserService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Failed to read file content.");

        $service->parse('nonexistent.html');
    }

    public function test_it_parses_html_content_susccessfully()
    {
        Storage::fake('local');

        $path = 'rosters/roster.html';
        $realPath = base_path('tests/fixtures/Roster - CrewConnex.html');
        $fileContent = file_get_contents($realPath);

        Storage::disk('local')->put($path, $fileContent);

        
        $service = new HtmlRosterParserService();
        $service->parse($path);

        $this->assertDatabaseHas('events', [
            'type' => 'FLT'
        ]);
    }
}