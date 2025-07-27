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
     * Get session with details and authorization check.
     *
     * @param int $sessionId
     * @param User $user
     * @return Session
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getSessionWithDetails(int $sessionId, User $user): Session
    {
        $session = Session::with(['user', 'spins'])->findOrFail($sessionId);

        // Check if user owns this session
        if ($session->user_id !== $user->id) {
            throw new \Illuminate\Auth\Access\AuthorizationException('You can only access your own sessions.');
        }

        return $session;
    }

    /**
     * Close an active session.
     *
     * @param int $sessionId
     * @param User $user
     * @return Session
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \InvalidArgumentException
     */
    public function closeSession(int $sessionId, User $user): Session
    {
        return DB::transaction(function () use ($sessionId, $user) {
            $session = Session::with(['user', 'spins'])->findOrFail($sessionId);

            // Check if user owns this session
            if ($session->user_id !== $user->id) {
                throw new \Illuminate\Auth\Access\AuthorizationException('You can only close your own sessions.');
            }

            // Check if session is already closed
            if ($session->end_time !== null) {
                throw new \InvalidArgumentException('Session is already closed.');
            }

            // Close the session
            $session->update([
                'end_time' => now(),
            ]);

            return $session->fresh(['user', 'spins']);
        });
    }

    /**
     * Format session data for API response with P/L calculations and strategy recommendations.
     *
     * @param Session $session
     * @return array
     */
    public function formatDetailedSessionResponse(Session $session): array
    {
        $baseData = $this->formatSessionResponse($session);

        // Add P/L calculations
        $baseData['pl_data'] = [
            'total_pl' => $session->getTotalPL(),
            'running_totals' => $session->getRunningPLTotals(),
            'statistics' => $session->getSessionStatistics(),
        ];

        // Add strategy recommendations
        $baseData['strategy'] = [
            'next_action' => $session->getNextBettingAction(),
            'configuration' => $session->getStrategyConfiguration(),
        ];

        // Add spins data
        $baseData['spins'] = $session->spins->map(function ($spin) {
            return [
                'id' => $spin->id,
                'result' => $spin->result,
                'bet_amount' => (float) $spin->bet_amount,
                'pl' => (float) $spin->pl,
                'created_at' => $spin->created_at->toISOString(),
            ];
        })->toArray();

        return $baseData;
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