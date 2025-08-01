<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Booking;
use App\Models\BookingSlot;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    public function own(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id;
    }
}
