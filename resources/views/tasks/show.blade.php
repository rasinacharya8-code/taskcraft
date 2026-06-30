@extends('layouts.app')

@section('title', $task->title)
@section('page-title', 'Task Detail')

@section('topbar-actions')
    <a href="{{ route('workspaces.projects.show', [$workspace, $project]) }}" class="btn btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to {{ $project->name }}
    </a>
@endsection

@section('content')

<div class="task-detail-layout">

    {{-- Main column --}}
    <div class="task-detail-main">
        <div class="card">
            <div class="task-detail-header">
                <h2 class="task-detail-title">{{ $task->title }}</h2>
                <div class="task-detail-badges">
                    <span class="badge badge-status-{{ $task->status }}">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span>
                    <span class="badge badge-priority-{{ $task->priority }}">{{ ucfirst($task->priority) }} priority</span>
                </div>
            </div>

            @if($task->description)
            <p class="task-description">{{ $task->description }}</p>
            @else
            <p class="task-description muted">No description provided.</p>
            @endif

            {{-- Update Task Form --}}
            <form method="POST" action="{{ route('workspaces.projects.tasks.update', [$workspace, $project, $task]) }}" class="update-form">
                @csrf @method('PATCH')
                <div class="form-row">
                    <div class="form-group">
                        <label for="update-status">Status</label>
                        <select id="update-status" name="status">
                            @foreach(['todo' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'Review', 'completed' => 'Completed'] as $val => $label)
                            <option value="{{ $val }}" {{ $task->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="update-priority">Priority</label>
                        <select id="update-priority" name="priority">
                            @foreach(['low', 'medium', 'high'] as $p)
                            <option value="{{ $p }}" {{ $task->priority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="update-assignee">Assignee</label>
                        <select id="update-assignee" name="assigned_to">
                            <option value="">Unassigned</option>
                            @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ $task->assigned_to == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="btn-update-task">Update Task</button>
            </form>
        </div>

        {{-- Time Logs --}}
        <div class="card" style="margin-top: 1.5rem;">
            <h3 class="section-title">Time Logs</h3>
            <div class="time-logs-list">
                @forelse($task->timeLogs as $log)
                <div class="time-log-item">
                    <div class="mini-avatar">{{ substr($log->user->name, 0, 1) }}</div>
                    <div class="time-log-body">
                        <span class="time-log-user">{{ $log->user->name }}</span>
                        <span class="time-log-desc">{{ $log->description ?: 'Work session' }}</span>
                        <span class="time-log-date">{{ $log->logged_at->format('M d, Y') }}</span>
                    </div>
                    <div class="time-log-duration">
                        <span>{{ $log->hours }}h</span>
                    </div>
                </div>
                @empty
                <p class="muted">No time logged yet.</p>
                @endforelse
            </div>

            {{-- Total --}}
            @if($task->timeLogs->count())
            <div class="time-total">
                Total: <strong>{{ $task->total_hours }}h</strong>
            </div>
            @endif

            {{-- Log Time Form --}}
            <form method="POST" action="{{ route('workspaces.projects.tasks.time-logs.store', [$workspace, $project, $task]) }}" class="log-time-form">
                @csrf
                <h4>Log Time</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="minutes-spent">Minutes Spent</label>
                        <input type="number" id="minutes-spent" name="minutes_spent" min="1" max="1440" placeholder="e.g. 90" required>
                    </div>
                    <div class="form-group">
                        <label for="logged-at">Date</label>
                        <input type="date" id="logged-at" name="logged_at" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="log-description">Notes <span class="label-optional">(optional)</span></label>
                    <input type="text" id="log-description" name="description" placeholder="What did you work on?">
                </div>
                <button type="submit" class="btn btn-primary" id="btn-log-time">Log Time</button>
            </form>
        </div>
    </div>

    {{-- Sidebar info --}}
    <aside class="task-detail-sidebar">
        <div class="card">
            <h3 class="section-title">Details</h3>
            <dl class="detail-list">
                <dt>Project</dt>
                <dd><a href="{{ route('workspaces.projects.show', [$workspace, $project]) }}">{{ $project->name }}</a></dd>
                <dt>Created by</dt>
                <dd>{{ $task->creator->name }}</dd>
                <dt>Created</dt>
                <dd>{{ $task->created_at->format('M d, Y') }}</dd>
                @if($task->due_at)
                <dt>Due Date</dt>
                <dd class="{{ $task->due_at->isPast() && $task->status !== 'completed' ? 'overdue' : '' }}">
                    {{ $task->due_at->format('M d, Y') }}
                </dd>
                @endif
                @if($task->completed_at)
                <dt>Completed</dt>
                <dd>{{ $task->completed_at->format('M d, Y') }}</dd>
                @endif
                <dt>Time Logged</dt>
                <dd><strong>{{ $task->total_hours }}h</strong></dd>
            </dl>
        </div>

        {{-- Activity --}}
        <div class="card" style="margin-top: 1rem;">
            <h3 class="section-title">Activity</h3>
            <div class="activity-feed compact">
                @forelse($task->activityLogs as $log)
                <div class="activity-item">
                    <div class="activity-avatar">{{ substr($log->user->name ?? 'U', 0, 1) }}</div>
                    <div class="activity-body">
                        <p class="activity-details">{{ $log->details }}</p>
                        <span class="activity-time">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @empty
                <p class="muted">No activity yet.</p>
                @endforelse
            </div>
        </div>
    </aside>

</div>

@endsection
