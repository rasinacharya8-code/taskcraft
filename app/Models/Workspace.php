<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the users belonging to this workspace.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'workspace_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Get the projects in this workspace.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get all tasks in this workspace through projects.
     */
    public function tasks()
    {
        return $this->hasManyThrough(Task::class, Project::class);
    }

    /**
     * Get the activity logs for this workspace.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
