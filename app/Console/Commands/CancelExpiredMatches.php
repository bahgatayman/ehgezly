<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\OpenMatch;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelExpiredMatches extends Command
{
    protected $signature = 'matches:cancel-expired';
    protected $description = 'Cancel expired open matches and notify players';

    public function handle(): int
    {
        $now = Carbon::now();

        $matches = OpenMatch::with(['timeslot', 'players.customer.user'])
            ->whereIn('status', ['waiting_players', 'ready_to_book'])
            ->whereHas('timeslot', function ($q) use ($now) {
                $q->whereRaw("TIMESTAMP(date, start_time) < ?", [$now->toDateTimeString()]);
            })
            ->get();

        foreach ($matches as $match) {
            DB::transaction(function () use ($match) {
                $match->update(['status' => 'cancelled']);
                if ($match->timeslot) {
                    $match->timeslot->update(['status' => 'available']);
                }

                foreach ($match->players as $player) {
                    if ($player->customer?->user) {
                        Notification::create([
                            'user_id' => $player->customer->user->id,
                            'title' => 'انتهى وقت الماتش',
                            'message' => "انتهى وقت ماتش {$match->name} دون اكتمال العدد، تم إلغاؤه تلقائياً",
                            'type' => 'match_expired',
                            'notifiable_type' => OpenMatch::class,
                            'notifiable_id' => $match->id,
                            'is_read' => false,
                        ]);
                    }
                }
            });
        }

        return self::SUCCESS;
    }
}
