<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'room_id',
        'participant_id',
        'task_title',
        'estimate_value',
    ];

    /**
     * Get the room this vote was cast in.
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Get the participant who cast this vote.
     */
    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }
}
