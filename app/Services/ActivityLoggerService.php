<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLoggerService
{
    /**
     * Log an activity in the workspace.
     *
     * @param  int    $workspaceId
     * @param  int    $userId
     * @param  string $action      e.g. "created", "updated", "completed", "logged_time"
     * @param  Model  $loggable    The model (Task, Project) being acted upon
     * @param  string $details     Human-readable description
     */
    public static function log(int $workspaceId, int $userId, string $action, Model $loggable, string $details = ''): void
    {
        ActivityLog::create([
            'workspace_id'  => $workspaceId,
            'user_id'       => $userId,
            'action'        => $action,
            'loggable_type' => get_class($loggable),
            'loggable_id'   => $loggable->getKey(),
            'details'       => $details,
        ]);
    }
}
