<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\MoodRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_mood_records(): void
    {
        $user = User::factory()->create();
        $moodRecords = MoodRecord::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->moodRecords);
        
        foreach ($user->moodRecords as $moodRecord) {
            $this->assertEquals($user->id, $moodRecord->user->id);
        }
    }

    public function test_user_scope_verified(): void
    {
        $verifiedUser = User::factory()->create(['is_verified' => true]);
        $unverifiedUser = User::factory()->create(['is_verified' => false]);

        $verifiedUsers = User::verified()->get();

        $this->assertTrue($verifiedUsers->contains($verifiedUser));
        $this->assertFalse($verifiedUsers->contains($unverifiedUser));
    }

    public function test_user_scope_by_google_id(): void
    {
        $user = User::factory()->create(['google_id' => '123456789']);
        User::factory()->create(['google_id' => '987654321']);

        $userByGoogleId = User::byGoogleId('123456789')->first();

        $this->assertEquals($user->id, $userByGoogleId->id);
    }
}