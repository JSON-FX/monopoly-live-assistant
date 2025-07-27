<?php

namespace App\Services;

use App\Models\Session;
use App\Models\Spin;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SpinService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly SessionService $sessionService
    ) {}

    /**
     * Create a new spin for the specified session.
     *
     * @param int $sessionId
     * @param User $user
     * @param array $data
     * @return array
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \InvalidArgumentException
     */
    public function createSpin(int $sessionId, User $user, array $data): array
    {
        return DB::transaction(function () use ($sessionId, $user, $data) {
            // Find and authorize session access
            $session = Session::with(['user', 'spins'])->findOrFail($sessionId);

            if ($session->user_id !== $user->id) {
                throw new \Illuminate\Auth\Access\AuthorizationException('You can only add spins to your own sessions.');
            }

            // Validate session is still active (not closed)
            if ($session->end_time !== null) {
                throw new \InvalidArgumentException('Cannot add spins to a closed session.');
            }

            // Create the spin
            $spin = Spin::create([
                'session_id' => $session->id,
                'result' => $data['result'],
                'bet_amount' => $data['bet_amount'],
                'pl' => $data['pl'],
            ]);

            // Reload session with updated spins
            $session = $session->fresh(['user', 'spins']);

            // Return updated session data with P/L calculations
            return $this->sessionService->formatDetailedSessionResponse($session);
        });
    }

    /**
     * Format spin data for API response.
     *
     * @param Spin $spin
     * @return array
     */
    public function formatSpinResponse(Spin $spin): array
    {
        return [
            'id' => $spin->id,
            'session_id' => $spin->session_id,
            'result' => $spin->result,
            'bet_amount' => (float) $spin->bet_amount,
            'pl' => (float) $spin->pl,
            'created_at' => $spin->created_at->toISOString(),
            'updated_at' => $spin->updated_at->toISOString(),
        ];
    }
} 