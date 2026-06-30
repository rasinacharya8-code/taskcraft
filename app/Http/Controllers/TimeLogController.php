<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimeLogRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use App\Services\ActivityLoggerService;
use Illuminate\Support\Facades\Auth;

class TimeLogController extends Controller
{
    /**
     * Store a new time log entry for a task.
     */
    public function store(StoreTimeLogRequest $request, Workspace $workspace, Project $project, Task $task)
    {
        $timeLog = $task->timeLogs()->create(array_merge(
            $request->validated(),
            ['user_id' => Auth::id()]
        ));

        $hours = round($timeLog->minutes_spent / 60, 2);

        ActivityLoggerService::log(
            $workspace->id,
            Auth::id(),
            'logged_time',
            $task,
            Auth::user()->name . " logged {$hours}h on \"{$task->title}\""
        );

        return back()->with('success', "Time logged: {$hours} hour(s) added!");
    }
}
