<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'creator_id',
        'name',
        'deck_type',
        'status',
        'current_task_title',
    ];

    /**
     * Get the user who created this room.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the participants of this room.
     */
    public function participants()
    {
        return $this->hasMany(Participant::class, 'room_id');
    }

    /**
     * Get the active participants of this room.
     */
    public function activeParticipants()
    {
        return $this->hasMany(Participant::class, 'room_id')->where('is_active', true);
    }

    /**
     * Get the votes cast in this room.
     */
    public function votes()
    {
        return $this->hasMany(Vote::class, 'room_id');
    }

    /**
     * Get the estimation history logs for this room.
     */
    public function estimationHistories()
    {
        return $this->hasMany(EstimationHistory::class, 'room_id');
    }
}
