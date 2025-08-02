<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\BookingSlot;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Api\BookingResource;
use App\Http\Requests\Api\BookingStoreRequest;
use Illuminate\Validation\ValidationException;
use App\Actions\Booking\CheckSlotOverlapsAction;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BookingController extends Controller
{
    public function __construct(private CheckSlotOverlapsAction $checkSlotOverlaps) {}

    public function index(): ResourceCollection
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return BookingResource::collection($user->bookings()->with('slots')->get());
    }

    public function store(BookingStoreRequest $request): BookingResource
    {
        $slots = $request->input('slots');

        // Проверяем пересечения слотов в текущем запросе
        $slotsSorted = collect($slots)->sortBy('start_time')->values();
        for ($i = 1; $i < $slotsSorted->count(); $i++) {
            $prevEnd = Carbon::parse($slotsSorted[$i-1]['end_time']);
            $currStart = Carbon::parse($slotsSorted[$i]['start_time']);
            
            if ($currStart < $prevEnd) {
                throw ValidationException::withMessages(['message' => 'Slots overlap']);
            }
        }

        if (($this->checkSlotOverlaps)($slots)) {
            throw ValidationException::withMessages(['message' => 'Slots overlap with existing bookings']);
        }

        // Создаем бронирование и слоты одной транзакцией, чтобы избежать частичного сохранения при ошибке
        $booking = DB::transaction(function () use ($slots) {
            $booking = Booking::create(['user_id' => Auth::id()]);
            $booking->slots()->createMany($slots);

            return $booking;
        });

        return new BookingResource($booking);
    }

    public function destroy(Booking $booking): Response
    {
        Gate::authorize('own', $booking);

        $booking->delete();

        return response()->noContent();
    }
}
