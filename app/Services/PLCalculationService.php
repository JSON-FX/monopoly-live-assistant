<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Session;
use App\Models\Spin;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Service for calculating profit/loss and session statistics
 */
class PLCalculationService
{
    /** @var int Decimal precision for financial calculations */
    private const FINANCIAL_PRECISION = 2;
    /**
     * Calculate total profit/loss for a session
     *
     * @param Session $session
     * @return float
     */
    public function calculateTotalPL(Session $session): float
    {
        return (float) $session->spins()->sum('pl');
    }

    /**
     * Calculate running P/L totals for a session
     *
     * @param Session $session
     * @return array Array of running totals with spin IDs
     */
    public function calculateRunningPLTotals(Session $session): array
    {
        $spins = $session->spins()->orderBy('created_at')->get(['id', 'pl']);
        
        if ($spins->isEmpty()) {
            return [];
        }

        $runningTotal = 0;
        $runningTotals = [];

        foreach ($spins as $spin) {
            $runningTotal += $spin->pl;
            $runningTotals[] = [
                'spin_id' => $spin->id,
                'pl' => $spin->pl,
                'running_total' => $runningTotal,
            ];
        }

        return $runningTotals;
    }

    /**
     * Generate comprehensive session statistics
     *
     * @param Session $session
     * @return array
     */
    public function generateSessionStatistics(Session $session): array
    {
        $spins = $session->spins()->get(['pl', 'bet_amount']);
        
        if ($spins->isEmpty()) {
            return $this->getEmptySessionStatistics();
        }

        // Single-pass calculation for optimal performance
        $totalSpins = $spins->count();
        $winningSpins = 0;
        $losingSpins = 0;
        $breakEvenSpins = 0;
        $totalPL = 0;
        $totalBetAmount = 0;
        $largestWin = 0;
        $largestLoss = 0;

        foreach ($spins as $spin) {
            $pl = $spin->pl;
            $betAmount = $spin->bet_amount;
            
            // Categorize spins
            if ($pl > 0) {
                $winningSpins++;
                $largestWin = max($largestWin, $pl);
            } elseif ($pl < 0) {
                $losingSpins++;
                $largestLoss = min($largestLoss, $pl);
            } else {
                $breakEvenSpins++;
            }
            
            $totalPL += $pl;
            $totalBetAmount += $betAmount;
        }
        
        $winRate = $totalSpins > 0 ? ($winningSpins / $totalSpins) * 100 : 0;
        $averageBet = $totalSpins > 0 ? $totalBetAmount / $totalSpins : 0;
        $averagePLPerSpin = $totalSpins > 0 ? $totalPL / $totalSpins : 0;

        return [
            'total_spins' => $totalSpins,
            'winning_spins' => $winningSpins,
            'losing_spins' => $losingSpins,
            'break_even_spins' => $breakEvenSpins,
            'win_rate_percentage' => round($winRate, self::FINANCIAL_PRECISION),
            'total_pl' => $totalPL,
            'total_bet_amount' => $totalBetAmount,
            'largest_win' => $largestWin,
            'largest_loss' => $largestLoss,
            'average_bet' => round($averageBet, self::FINANCIAL_PRECISION),
            'average_pl_per_spin' => round($averagePLPerSpin, self::FINANCIAL_PRECISION),
            'is_profitable' => $totalPL > 0,
        ];
    }

    /**
     * Validate session data for P/L calculations
     *
     * @param Session $session
     * @throws InvalidArgumentException
     */
    public function validateSessionData(Session $session): void
    {
        if (!$session->exists) {
            throw new InvalidArgumentException('Session must be persisted to database');
        }

        if (!$session->user_id) {
            throw new InvalidArgumentException('Session must have a valid user_id');
        }

        // Check for invalid spin data
        $invalidSpins = $session->spins()
            ->where(function ($query) {
                $query->whereNull('pl')
                    ->orWhereNull('bet_amount')
                    ->orWhere('bet_amount', '<', 0);
            })->count();

        if ($invalidSpins > 0) {
            throw new InvalidArgumentException("Session contains {$invalidSpins} spins with invalid data");
        }
    }

    /**
     * Get default statistics for empty sessions
     *
     * @return array
     */
    private function getEmptySessionStatistics(): array
    {
        return [
            'total_spins' => 0,
            'winning_spins' => 0,
            'losing_spins' => 0,
            'break_even_spins' => 0,
            'win_rate_percentage' => 0,
            'total_pl' => 0,
            'total_bet_amount' => 0,
            'largest_win' => 0,
            'largest_loss' => 0,
            'average_bet' => 0,
            'average_pl_per_spin' => 0,
            'is_profitable' => false,
        ];
    }
} 