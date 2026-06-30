<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use App\Models\Workspace;
use App\Services\ActivityLoggerService;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Show all projects for a workspace.
     */
    public function index(Workspace $workspace)
    {
        $projects = $workspace->projects()
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn($q) => $q->where('status', 'completed'),
                'tasks as in_progress_count'     => fn($q) => $q->where('status', 'in_progress'),
            ])
            ->latest()
            ->get();

        return view('projects.index', compact('workspace', 'projects'));
    }

    /**
     * Show a single project with its Kanban board.
     */
    public function show(Workspace $workspace, Project $project)
    {
        // Eager-load tasks and their assignees to prevent N+1 queries
        $project->load([
            'tasks' => fn($q) => $q->with('assignee')->orderBy('priority', 'desc'),
        ]);

        $members = $workspace->users()->get();

        $tasksByStatus = [
            'todo'        => $project->tasks->where('status', 'todo'),
            'in_progress' => $project->tasks->where('status', 'in_progress'),
            'review'      => $project->tasks->where('status', 'review'),
            'completed'   => $project->tasks->where('status', 'completed'),
        ];

        return view('projects.show', compact('workspace', 'project', 'tasksByStatus', 'members'));
    }

    /**
     * Store a new project in the workspace.
     */
    public function store(StoreProjectRequest $request, Workspace $workspace)
    {
        $project = $workspace->projects()->create($request->validated());

        ActivityLoggerService::log(
            $workspace->id,
            Auth::id(),
            'created',
            $project,
            Auth::user()->name . ' created project "' . $project->name . '"'
        );

        return redirect()->route('workspaces.projects.show', [$workspace, $project])
                         ->with('success', "Project \"{$project->name}\" created!");
    }

    /**
     * Update a project's status.
     */
    public function update(Workspace $workspace, Project $project)
    {
        $validated = request()->validate([
            'status' => ['required', 'in:active,on_hold,completed'],
        ]);

        $project->update($validated);

        ActivityLoggerService::log(
            $workspace->id,
            Auth::id(),
            'updated',
            $project,
            Auth::user()->name . ' changed project "' . $project->name . '" status to ' . $validated['status']
        );

        return back()->with('success', 'Project status updated!');
    }
}
