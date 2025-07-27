<?php

namespace App\Services;

use App\Models\Session;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SessionService
{
    /**
     * Create a new session for the authenticated user.
     *
     * @param User $user
     * @return Session
     */
    public function createSession(User $user): Session
    {
        return DB::transaction(function () use ($user) {
            $session = Session::create([
                'user_id' => $user->id,
                'start_time' => now(),
                'end_time' => null,
            ]);

            return $session->load('user');
        });
    }

    /**
     * Format session data for API response.
     *
     * @param Session $session
     * @return array
     */
    public function formatSessionResponse(Session $session): array
    {
        return [
            'id' => $session->id,
            'user_id' => $session->user_id,
            'start_time' => $session->start_time->toISOString(),
            'end_time' => $session->end_time?->toISOString(),
            'created_at' => $session->created_at->toISOString(),
            'updated_at' => $session->updated_at->toISOString(),
            'user' => [
                'id' => $session->user->id,
                'name' => $session->user->name,
                'email' => $session->user->email,
            ],
        ];
    }
} 