<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use App\Services\ActivityLoggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Show a single task with time logs and activity feed.
     */
    public function show(Workspace $workspace, Project $project, Task $task)
    {
        $task->load(['assignee', 'creator', 'timeLogs.user', 'activityLogs.user']);
        $members = $workspace->users()->get();

        return view('tasks.show', compact('workspace', 'project', 'task', 'members'));
    }

    /**
     * Store a new task in a project.
     */
    public function store(StoreTaskRequest $request, Workspace $workspace, Project $project)
    {
        $data = array_merge($request->validated(), [
            'created_by' => Auth::id(),
            'status'     => 'todo',
        ]);

        $task = $project->tasks()->create($data);

        ActivityLoggerService::log(
            $workspace->id,
            Auth::id(),
            'created',
            $task,
            Auth::user()->name . ' created task "' . $task->title . '"'
        );

        return redirect()->route('workspaces.projects.show', [$workspace, $project])
                         ->with('success', 'Task created successfully!');
    }

    /**
     * Update a task (including status, priority, assignee).
     */
    public function update(UpdateTaskRequest $request, Workspace $workspace, Project $project, Task $task)
    {
        $oldStatus = $task->status;
        $data = $request->validated();

        if (isset($data['status']) && $data['status'] === 'completed' && $task->status !== 'completed') {
            $data['completed_at'] = now();
        }

        $task->update($data);

        $details = Auth::user()->name . ' updated task "' . $task->title . '"';
        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            $details = Auth::user()->name . ' moved "' . $task->title . '" to ' . ucfirst(str_replace('_', ' ', $data['status']));
        }

        ActivityLoggerService::log($workspace->id, Auth::id(), 'updated', $task, $details);

        return back()->with('success', 'Task updated!');
    }

    /**
     * Quick-update status via AJAX (Kanban drag).
     */
    public function updateStatus(Request $request, Workspace $workspace, Project $project, Task $task)
    {
        $request->validate(['status' => ['required', 'in:todo,in_progress,review,completed']]);

        $task->update(['status' => $request->status]);

        if ($request->status === 'completed') {
            $task->update(['completed_at' => now()]);
        }

        ActivityLoggerService::log(
            $workspace->id,
            Auth::id(),
            'updated',
            $task,
            Auth::user()->name . ' moved "' . $task->title . '" to ' . ucfirst(str_replace('_', ' ', $request->status))
        );

        return response()->json(['message' => 'Status updated', 'task' => $task]);
    }
}
