<?php

namespace Tests\Unit;

use App\Models\MoodRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoodRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_mood_record_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $moodRecord = MoodRecord::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $moodRecord->user);
        $this->assertEquals($user->id, $moodRecord->user->id);
    }

    public function test_mood_record_has_accessors(): void
    {
        $moodRecord = MoodRecord::factory()->create([
            'mood' => 'senyum',
            'reason' => 'Karena cuaca cerah',
            'suggestion_action' => 'Tetap jaga semangat'
        ]);

        $this->assertEquals('Senang', $moodRecord->mood_label);
        
        // Test accessor formatted_date dan formatted_time
        $this->assertNotNull($moodRecord->formatted_date);
        $this->assertNotNull($moodRecord->formatted_time);
    }
}