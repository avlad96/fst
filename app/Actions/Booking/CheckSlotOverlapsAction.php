<?php

namespace App\Actions\Booking;

use App\Models\BookingSlot;
use Illuminate\Database\Eloquent\Collection;

class CheckSlotOverlapsAction
{
    public function __invoke(Collection|array $slots, ?int $excludeId = null): bool
    {
        return BookingSlot::query()
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($query) use ($slots) {
                foreach ($slots as $slot) {
                    $query->orWhere(function ($q) use ($slot) {
                        $q->where('start_time', '<', $slot['end_time'])->where('end_time', '>', $slot['start_time']);
                    });
                }
            })
            ->exists();
    }
}