<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Session;
use App\Models\Spin;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Service for Martingale "Bet on 1" strategy logic
 */
class MartingaleStrategyService
{
    private const DEFAULT_BASE_BET = 1.00;
    private const DEFAULT_MAX_BET = 1000.00;
    private const WINNING_RESULT = '1';

    /**
     * Determine if next action should be "Bet" or "Skip"
     *
     * @param Session $session
     * @param float $baseBet
     * @param float $maxBet
     * @return array
     */
    public function determineNextAction(
        Session $session,
        float $baseBet = self::DEFAULT_BASE_BET,
        float $maxBet = self::DEFAULT_MAX_BET
    ): array {
        $this->validateStrategyParameters($baseBet, $maxBet);

        $spins = $session->spins()->orderBy('created_at')->get();
        
        if ($spins->isEmpty()) {
            return [
                'action' => 'Bet',
                'bet_amount' => $baseBet,
                'reason' => 'First spin - start with base bet',
                'consecutive_losses' => 0,
            ];
        }

        $consecutiveLosses = $this->getConsecutiveLossesFromEnd($spins);
        $nextBetAmount = $this->calculateNextBetAmount($baseBet, $consecutiveLosses);

        if ($nextBetAmount > $maxBet) {
            return [
                'action' => 'Skip',
                'bet_amount' => 0,
                'reason' => "Next bet amount ({$nextBetAmount}) exceeds maximum bet limit ({$maxBet})",
                'consecutive_losses' => $consecutiveLosses,
            ];
        }

        return [
            'action' => 'Bet',
            'bet_amount' => $nextBetAmount,
            'reason' => $consecutiveLosses === 0 
                ? 'Previous spin won - reset to base bet'
                : "Martingale progression after {$consecutiveLosses} consecutive losses",
            'consecutive_losses' => $consecutiveLosses,
        ];
    }

    /**
     * Calculate next bet amount using Martingale progression
     *
     * @param float $baseBet
     * @param int $consecutiveLosses
     * @return float
     */
    public function calculateNextBetAmount(float $baseBet, int $consecutiveLosses): float
    {
        if ($consecutiveLosses < 0) {
            throw new InvalidArgumentException('Consecutive losses cannot be negative');
        }

        if ($baseBet <= 0) {
            throw new InvalidArgumentException('Base bet must be positive');
        }

        // Martingale: double the bet for each consecutive loss
        return $baseBet * pow(2, $consecutiveLosses);
    }

    /**
     * Get consecutive losses from the end of spin history
     *
     * @param Collection $spins
     * @return int
     */
    public function getConsecutiveLossesFromEnd(Collection $spins): int
    {
        if ($spins->isEmpty()) {
            return 0;
        }

        $consecutiveLosses = 0;
        
        // Traverse from most recent spin backwards
        for ($i = $spins->count() - 1; $i >= 0; $i--) {
            $spin = $spins[$i];
            
            if ($this->isWinningSpin($spin)) {
                // Stop at first winning spin
                break;
            }
            
            if ($this->isLosingBet($spin)) {
                $consecutiveLosses++;
            }
        }

        return $consecutiveLosses;
    }

    /**
     * Check if a spin is a winning spin for "Bet on 1" strategy
     *
     * @param Spin $spin
     * @return bool
     */
    public function isWinningSpin(Spin $spin): bool
    {
        return $spin->result === self::WINNING_RESULT && $spin->pl > 0;
    }

    /**
     * Check if a spin represents a losing bet
     *
     * @param Spin $spin
     * @return bool
     */
    public function isLosingBet(Spin $spin): bool
    {
        return $spin->bet_amount > 0 && $spin->pl < 0;
    }

    /**
     * Validate strategy parameters
     *
     * @param float $baseBet
     * @param float $maxBet
     * @throws InvalidArgumentException
     */
    public function validateStrategyParameters(float $baseBet, float $maxBet): void
    {
        if ($baseBet <= 0) {
            throw new InvalidArgumentException('Base bet must be positive');
        }

        if ($maxBet <= 0) {
            throw new InvalidArgumentException('Maximum bet must be positive');
        }

        if ($baseBet > $maxBet) {
            throw new InvalidArgumentException('Base bet cannot exceed maximum bet');
        }
    }

    /**
     * Calculate maximum consecutive losses before hitting bet limit
     *
     * @param float $baseBet
     * @param float $maxBet
     * @return int
     */
    public function calculateMaxConsecutiveLosses(float $baseBet, float $maxBet): int
    {
        $this->validateStrategyParameters($baseBet, $maxBet);
        
        $maxLosses = 0;
        $currentBet = $baseBet;
        
        while ($currentBet <= $maxBet) {
            $currentBet *= 2;
            $maxLosses++;
        }
        
        return $maxLosses - 1; // Subtract 1 because we count the losses, not the bets
    }

    /**
     * Get strategy configuration
     *
     * @param float $baseBet
     * @param float $maxBet
     * @return array
     */
    public function getStrategyConfiguration(
        float $baseBet = self::DEFAULT_BASE_BET,
        float $maxBet = self::DEFAULT_MAX_BET
    ): array {
        $this->validateStrategyParameters($baseBet, $maxBet);
        
        return [
            'strategy_name' => 'Martingale "Bet on 1"',
            'base_bet' => $baseBet,
            'max_bet' => $maxBet,
            'winning_result' => self::WINNING_RESULT,
            'max_consecutive_losses' => $this->calculateMaxConsecutiveLosses($baseBet, $maxBet),
            'progression_type' => 'Double on loss, reset on win',
        ];
    }
} 