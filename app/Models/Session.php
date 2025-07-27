<?php

namespace App\Models;

use App\Services\PLCalculationService;
use App\Services\MartingaleStrategyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'game_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the spins for the session.
     */
    public function spins(): HasMany
    {
        return $this->hasMany(Spin::class);
    }

    /**
     * Basic validation rules for Session model.
     *
     * @return array<string, string>
     */
    public static function validationRules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
        ];
    }

    /**
     * Get total profit/loss for this session
     *
     * @return float
     */
    public function getTotalPL(): float
    {
        return app(PLCalculationService::class)->calculateTotalPL($this);
    }

    /**
     * Get running P/L totals for this session
     *
     * @return array
     */
    public function getRunningPLTotals(): array
    {
        return app(PLCalculationService::class)->calculateRunningPLTotals($this);
    }

    /**
     * Get comprehensive session statistics
     *
     * @return array
     */
    public function getSessionStatistics(): array
    {
        return app(PLCalculationService::class)->generateSessionStatistics($this);
    }

    /**
     * Determine next betting action using Martingale strategy
     *
     * @param float $baseBet
     * @param float $maxBet
     * @return array
     */
    public function getNextBettingAction(float $baseBet = 1.00, float $maxBet = 1000.00): array
    {
        return app(MartingaleStrategyService::class)->determineNextAction($this, $baseBet, $maxBet);
    }

    /**
     * Get strategy configuration for this session
     *
     * @param float $baseBet
     * @param float $maxBet
     * @return array
     */
    public function getStrategyConfiguration(float $baseBet = 1.00, float $maxBet = 1000.00): array
    {
        return app(MartingaleStrategyService::class)->getStrategyConfiguration($baseBet, $maxBet);
    }

    /**
     * Validate session data for P/L calculations
     *
     * @throws \InvalidArgumentException
     */
    public function validateForCalculations(): void
    {
        app(PLCalculationService::class)->validateSessionData($this);
    }
}
