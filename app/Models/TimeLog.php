<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'minutes_spent',
        'logged_at',
        'description',
    ];

    protected $casts = [
        'logged_at' => 'date',
    ];

    /**
     * Get the task associated with the time log.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user who logged the time.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get hours formatted.
     */
    public function getHoursAttribute()
    {
        return round($this->minutes_spent / 60, 2);
    }
}
