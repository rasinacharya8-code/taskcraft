<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeLog;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        // ── Create users ──────────────────────────────────────────────
        $alice = User::create([
            'name'     => 'Alice Johnson',
            'email'    => 'alice@taskcraft.dev',
            'password' => Hash::make('password'),
        ]);

        $bob = User::create([
            'name'     => 'Bob Smith',
            'email'    => 'bob@taskcraft.dev',
            'password' => Hash::make('password'),
        ]);

        $carol = User::create([
            'name'     => 'Carol Lee',
            'email'    => 'carol@taskcraft.dev',
            'password' => Hash::make('password'),
        ]);

        // ── Demo login user ────────────────────────────────────────────
        $you = User::create([
            'name'     => 'Demo User',
            'email'    => 'demo@taskcraft.dev',
            'password' => Hash::make('password'),
        ]);

        // ── Create Workspaces ──────────────────────────────────────────
        $workspace1 = Workspace::create([
            'name'        => 'Product Development',
            'description' => 'Core product engineering and feature development.',
        ]);

        $workspace2 = Workspace::create([
            'name'        => 'Marketing Hub',
            'description' => 'Campaigns, content, and growth strategy.',
        ]);

        // ── Attach members with roles ──────────────────────────────────
        $workspace1->users()->attach($you->id, ['role' => 'owner']);
        $workspace1->users()->attach($alice->id, ['role' => 'member']);
        $workspace1->users()->attach($bob->id, ['role' => 'member']);
        $workspace1->users()->attach($carol->id, ['role' => 'viewer']);

        $workspace2->users()->attach($you->id, ['role' => 'owner']);
        $workspace2->users()->attach($alice->id, ['role' => 'member']);

        // ── Projects ───────────────────────────────────────────────────
        $proj1 = Project::create([
            'workspace_id' => $workspace1->id,
            'name'         => 'TaskCraft v2.0 Launch',
            'description'  => 'Full redesign and feature rollout for the Q3 launch.',
            'status'       => 'active',
        ]);

        $proj2 = Project::create([
            'workspace_id' => $workspace1->id,
            'name'         => 'API Integration Layer',
            'description'  => 'Building REST API and webhooks for third-party integrations.',
            'status'       => 'active',
        ]);

        $proj3 = Project::create([
            'workspace_id' => $workspace2->id,
            'name'         => 'Q3 Content Calendar',
            'description'  => 'Social media, blog posts, and newsletter campaigns.',
            'status'       => 'active',
        ]);

        // ── Tasks for Project 1 ────────────────────────────────────────
        $tasks1 = [
            ['title' => 'Design new Kanban board UI',          'status' => 'completed', 'priority' => 'high',   'assigned_to' => $alice->id],
            ['title' => 'Implement dark mode toggle',          'status' => 'in_progress','priority' => 'medium', 'assigned_to' => $bob->id],
            ['title' => 'Write unit tests for models',         'status' => 'review',    'priority' => 'high',   'assigned_to' => $you->id],
            ['title' => 'Optimize Eloquent query performance',  'status' => 'todo',      'priority' => 'high',   'assigned_to' => $bob->id],
            ['title' => 'Update onboarding documentation',     'status' => 'todo',      'priority' => 'low',    'assigned_to' => $carol->id],
            ['title' => 'Fix login page mobile layout',        'status' => 'in_progress','priority' => 'medium', 'assigned_to' => $alice->id],
            ['title' => 'Add email notification system',       'status' => 'todo',      'priority' => 'medium', 'assigned_to' => $you->id],
        ];

        foreach ($tasks1 as $taskData) {
            $task = Task::create(array_merge($taskData, [
                'project_id' => $proj1->id,
                'created_by' => $you->id,
                'due_at'     => Carbon::now()->addDays(rand(2, 14)),
                'completed_at' => $taskData['status'] === 'completed' ? Carbon::now()->subDays(rand(1, 5)) : null,
            ]));
            $this->seedTimeLogsAndActivity($task, $workspace1, [$alice, $bob, $you]);
        }

        // ── Tasks for Project 2 ────────────────────────────────────────
        $tasks2 = [
            ['title' => 'Design REST API endpoints',     'status' => 'completed',  'priority' => 'high',   'assigned_to' => $you->id],
            ['title' => 'Implement OAuth2 authentication','status' => 'in_progress','priority' => 'high',   'assigned_to' => $bob->id],
            ['title' => 'Write API documentation',       'status' => 'todo',       'priority' => 'medium', 'assigned_to' => $alice->id],
            ['title' => 'Set up webhook listeners',      'status' => 'review',     'priority' => 'medium', 'assigned_to' => $you->id],
        ];

        foreach ($tasks2 as $taskData) {
            $task = Task::create(array_merge($taskData, [
                'project_id'   => $proj2->id,
                'created_by'   => $alice->id,
                'due_at'       => Carbon::now()->addDays(rand(5, 20)),
                'completed_at' => $taskData['status'] === 'completed' ? Carbon::now()->subDays(rand(1, 3)) : null,
            ]));
            $this->seedTimeLogsAndActivity($task, $workspace1, [$alice, $bob, $you]);
        }

        // ── Tasks for Project 3 ────────────────────────────────────────
        $tasks3 = [
            ['title' => 'Draft Q3 blog post series',    'status' => 'in_progress', 'priority' => 'high',  'assigned_to' => $alice->id],
            ['title' => 'Schedule social media posts',  'status' => 'todo',        'priority' => 'medium','assigned_to' => $you->id],
            ['title' => 'Design newsletter template',   'status' => 'completed',   'priority' => 'high',  'assigned_to' => $alice->id],
        ];

        foreach ($tasks3 as $taskData) {
            $task = Task::create(array_merge($taskData, [
                'project_id'   => $proj3->id,
                'created_by'   => $you->id,
                'due_at'       => Carbon::now()->addDays(rand(3, 10)),
                'completed_at' => $taskData['status'] === 'completed' ? Carbon::now()->subDays(rand(1, 4)) : null,
            ]));
            $this->seedTimeLogsAndActivity($task, $workspace2, [$alice, $you]);
        }
    }

    private function seedTimeLogsAndActivity(Task $task, Workspace $workspace, array $users): void
    {
        // Add 1–3 time log entries per task
        $logCount = rand(1, 3);
        foreach (range(1, $logCount) as $i) {
            $user = $users[array_rand($users)];
            $minutes = rand(30, 240);
            $hours   = round($minutes / 60, 2);

            TimeLog::create([
                'task_id'       => $task->id,
                'user_id'       => $user->id,
                'minutes_spent' => $minutes,
                'logged_at'     => Carbon::now()->subDays(rand(0, 7)),
                'description'   => "Work session on {$task->title}",
            ]);

            ActivityLog::create([
                'workspace_id'  => $workspace->id,
                'user_id'       => $user->id,
                'action'        => 'logged_time',
                'loggable_type' => Task::class,
                'loggable_id'   => $task->id,
                'details'       => "{$user->name} logged {$hours}h on \"{$task->title}\"",
            ]);
        }

        // Add a "created" log
        ActivityLog::create([
            'workspace_id'  => $workspace->id,
            'user_id'       => $task->created_by,
            'action'        => 'created',
            'loggable_type' => Task::class,
            'loggable_id'   => $task->id,
            'details'       => "Task \"{$task->title}\" was created",
        ]);

        // If completed, add a "completed" log
        if ($task->status === 'completed') {
            ActivityLog::create([
                'workspace_id'  => $workspace->id,
                'user_id'       => $task->assigned_to ?? $task->created_by,
                'action'        => 'completed',
                'loggable_type' => Task::class,
                'loggable_id'   => $task->id,
                'details'       => "\"{$task->title}\" was marked as completed",
            ]);
        }
    }
}
