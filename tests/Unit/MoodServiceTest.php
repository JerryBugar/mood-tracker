<?php

namespace Tests\Unit;

use App\Models\MoodQuote;
use App\Models\MoodRecord;
use App\Models\User;
use App\Services\MoodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Mockery;

class MoodServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_mood_creates_record(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $moodService = new MoodService();
        $data = [
            'mood' => 'senyum',
            'reason' => 'Testing',
            'suggestion_action' => 'Keep testing'
        ];

        $moodRecord = $moodService->saveMood($data);

        $this->assertNotNull($moodRecord);
        $this->assertEquals('senyum', $moodRecord->mood);
        $this->assertEquals('Testing', $moodRecord->reason);
        $this->assertEquals($user->id, $moodRecord->user->id);
    }

    public function test_get_random_quote_returns_quote(): void
    {
        MoodQuote::create([
            'quote' => 'Test quote',
            'author' => 'Test author'
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $moodService = new MoodService();
        $quoteData = $moodService->getRandomQuote();

        $this->assertArrayHasKey('quote', $quoteData);
        $this->assertArrayHasKey('author', $quoteData);
        $this->assertIsString($quoteData['quote']);
        $this->assertIsString($quoteData['author']);
    }
}