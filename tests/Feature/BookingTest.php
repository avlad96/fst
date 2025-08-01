<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Http\Controllers\Api\BookingController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class BookingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
    }

    public function test_authorization_fails_without_token(): void
    {
        $response = $this->getJson('/api/bookings');

        $response->assertStatus(401);
    }
 
    public function test_store_booking_with_multiple_slots_successful(): void
    {
        $start1 = $this->faker->dateTimeBetween('+1 days', '+2 days');
        $end1 = (clone $start1)->modify('+2 hours');

        $start2 = (clone $end1)->modify('+1 hour');
        $end2 = (clone $start2)->modify('+2 hours');

        $request = [
            'slots' => [
                [
                    'start_time' => $this->fmt($start1),
                    'end_time' => $this->fmt($end1),
                ],
                [
                    'start_time' => $this->fmt($start2),
                    'end_time' => $this->fmt($end2),
                ],
            ],
        ];

        foreach ($request['slots'] as $slot) {
            $this->assertDatabaseMissing('booking_slots', [
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
            ]);
        }
 
        $response = $this->postJson(
            'api/bookings',
            $request,
            ['Authorization' => $this->user->api_token]
        );

        foreach ($request['slots'] as $slot) {
            $this->assertDatabaseHas('booking_slots', [
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
            ]);
        }
        
        $this->assertDatabaseCount('booking_slots', 2);

        $response->assertStatus(201);
    }

    public function test_cannot_create_booking_slot_with_overlap(): void
    {
        $booking = Booking::create(['user_id' => $this->user->id]);

        $start1 = $this->faker->dateTimeBetween('+1 day', '+2 days');
        $end1 = (clone $start1)->modify('+2 hours');
        $booking->slots()->create([
            'start_time' => $this->fmt($start1),
            'end_time' => $this->fmt($end1),
        ]);

        $start2 = (clone $end1)->modify('-1 hour');
        $end2 = (clone $start2)->modify('+2 hours');
        $request = [
            'start_time' => $this->fmt($start2),
            'end_time' => $this->fmt($end2),
        ];

        $response = $this->postJson(
            "api/bookings/{$booking->id}/slots",
            $request,
            ['Authorization' => $this->user->api_token]
        );

        $response->assertStatus(422);
    }

    public function test_update_booking_slot_successful(): void
    {
        $booking = Booking::create(['user_id' => $this->user->id]);

        $start1 = $this->faker->dateTimeBetween('+1 day', '+2 days');
        $end1 = (clone $start1)->modify('+2 hours');
        $slot = $booking->slots()->create([
            'start_time' => $this->fmt($start1),
            'end_time' => $this->fmt($end1),
        ]);

        $start2 = (clone $end1)->modify('+1 hour');
        $end2 = (clone $start2)->modify('+3 hours');
        $request = [
            'start_time' => $this->fmt($start2),
            'end_time' => $this->fmt($end2),
        ];

        $response = $this->patchJson(
            "api/bookings/{$booking->id}/slots/{$slot->id}",
            $request,
            ['Authorization' => $this->user->api_token]
        );

        $response->assertOk();
    }

    public function test_cannot_update_booking_slot_with_overlap(): void
    {
        $booking = Booking::create(['user_id' => $this->user->id]);

        $start1 = $this->faker->dateTimeBetween('+1 day', '+2 days');
        $end1 = (clone $start1)->modify('+2 hours');
        $booking->slots()->create([
            'start_time' => $this->fmt($start1),
            'end_time' => $this->fmt($end1),
        ]);
        
        $start2 = $this->faker->dateTimeBetween('+3 days', '+4 days');
        $end2 = (clone $start2)->modify('+2 hours');
        $slotToUpdate = $booking->slots()->create([
            'start_time' => $this->fmt($start2),
            'end_time' => $this->fmt($end2),
        ]);

        $request = [
            'start_time' => $this->fmt((clone $start1)->modify('+1 hour')),
            'end_time' => $this->fmt((clone $end1)->modify('+1 hour')),
        ];

        $response = $this->patchJson(
            "api/bookings/{$booking->id}/slots/{$slotToUpdate->id}",
            $request,
            ['Authorization' => $this->user->api_token]
        );

        $response->assertStatus(422);
    }

    private function createUser(): User
    {
        $user = User::factory()->create();

        return $user;
    }

    private function fmt(\DateTimeInterface $dt): string
    {
        return $dt->format('Y-m-d\TH:i:s');
    }
}
