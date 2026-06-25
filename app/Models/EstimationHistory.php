<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimationHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'estimation_history';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'room_id',
        'task_title',
        'deck_type',
        'final_estimate',
        'consensus_reached',
        'rounds_count',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'consensus_reached' => 'boolean',
        'rounds_count' => 'integer',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the room this history log belongs to.
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
