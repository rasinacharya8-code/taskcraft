<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    /**
     * Show the user's dashboard (list of all their workspaces).
     */
    public function index()
    {
        // Eager-load projects with task counts to avoid N+1 queries
        $workspaces = Auth::user()
            ->workspaces()
            ->withCount('projects')
            ->with(['projects' => fn($q) => $q->withCount('tasks')])
            ->latest()
            ->get();

        return view('dashboard', compact('workspaces'));
    }

    /**
     * Show a specific workspace with projects and recent activity.
     */
    public function show(Workspace $workspace)
    {
        // Eager-load all relationships needed for the workspace view
        $workspace->load([
            'projects' => fn($q) => $q->withCount(['tasks', 'tasks as completed_tasks_count' => fn($q) => $q->where('status', 'completed')]),
            'users',
        ]);

        $recentActivity = $workspace->activityLogs()
            ->with(['user', 'loggable'])
            ->latest()
            ->take(15)
            ->get();

        $stats = [
            'total_projects'    => $workspace->projects()->count(),
            'total_tasks'       => $workspace->tasks()->count(),
            'completed_tasks'   => $workspace->tasks()->where('status', 'completed')->count(),
            'in_progress_tasks' => $workspace->tasks()->where('status', 'in_progress')->count(),
            'members'           => $workspace->users()->count(),
        ];

        return view('workspaces.show', compact('workspace', 'recentActivity', 'stats'));
    }

    /**
     * Store a new workspace.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $workspace = Workspace::create($validated);

        // Attach the creator as the owner
        $workspace->users()->attach(Auth::id(), ['role' => 'owner']);

        return redirect()->route('workspaces.show', $workspace)
                         ->with('success', "Workspace \"{$workspace->name}\" created successfully!");
    }
}
