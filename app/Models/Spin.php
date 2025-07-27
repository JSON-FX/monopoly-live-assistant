<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spin extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'session_id',
        'result',
        'bet_amount',
        'pl',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bet_amount' => 'decimal:2',
            'pl' => 'decimal:2',
        ];
    }

    /**
     * Get the session that owns the spin.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    /**
     * Basic validation rules for Spin model.
     *
     * @return array<string, string>
     */
    public static function validationRules(): array
    {
        return [
            'session_id' => 'required|integer|exists:game_sessions,id',
            'result' => 'required|string|max:255',
            'bet_amount' => 'required|numeric|min:0',
            'pl' => 'required|numeric',
        ];
    }
}
