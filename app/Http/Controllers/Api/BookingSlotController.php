<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\BookingSlot;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Api\BookingSlotResource;
use App\Actions\Booking\CheckSlotOverlapsAction;
use App\Http\Requests\Api\BookingSlotRequest;

class BookingSlotController extends Controller
{
    public function __construct(private CheckSlotOverlapsAction $checkSlotOverlaps) {}

    public function update(BookingSlotRequest $request, Booking $booking, BookingSlot $slot): BookingSlotResource
    {
        if ($slot->booking_id !== $booking->id) {
            abort(403, 'Slot does not belong to this booking');
        }

        Gate::authorize('own', $booking);

        $data = $request->validated();

        if (($this->checkSlotOverlaps)([$data], $slot->id)) {
            throw ValidationException::withMessages(['slot' => 'Slot overlaps with existing bookings']);
        }

        $slot->update($data);

        return new BookingSlotResource($slot);
    }

    public function store(BookingSlotRequest $request, Booking $booking): BookingSlotResource
    {
        Gate::authorize('own', $booking);

        $data = $request->validated();

        if (($this->checkSlotOverlaps)([$data])) {
            throw ValidationException::withMessages(['message' => 'Slot overlaps with existing bookings']);
        }

        $slot = $booking->slots()->create([
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
        ]);

        return new BookingSlotResource($slot);
    }
}
