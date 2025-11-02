<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\MoodRecord;
use App\Models\MoodQuote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class MoodControllerTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_mood_modal_returns_view(): void
    {
        $user = User::factory()->create(['is_verified' => true]);
        $this->actingAs($user);

        $response = $this->withHeaders([
            'Accept' => 'text/html, application/xhtml+xml',
        ])->get('/mood/modal?mood=senyum');

        // Endpoint ini mengembalikan view partial untuk turbo frame
        $response->assertStatus(200);
        $response->assertViewIs('components._partials.mood_modal_content');
    }

    public function test_get_random_quote_returns_json(): void
    {
        MoodQuote::create([
            'quote' => 'Test quote',
            'author' => 'Test author'
        ]);

        $user = User::factory()->create(['is_verified' => true]);
        $this->actingAs($user);

        $response = $this->getJson('/mood/quote');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'quote',
            'author'
        ]);
    }

    public function test_save_mood_creates_record(): void
    {
        $user = User::factory()->create(['is_verified' => true]);
        $this->actingAs($user);

        $data = [
            'mood' => 'senyum',
            'reason' => 'Testing',
            'suggestion_action' => 'Keep testing'
        ];

        $response = $this->postJson('/mood/save', $data);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('mood_records', [
            'user_id' => $user->id,
            'mood' => 'senyum',
            'reason' => 'Testing'
        ]);
    }
}