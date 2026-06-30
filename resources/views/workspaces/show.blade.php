@extends('layouts.app')

@section('title', $workspace->name)
@section('page-title', $workspace->name)

@section('topbar-actions')
    <button class="btn btn-primary" onclick="document.getElementById('modal-create-project').classList.add('open')" id="btn-new-project">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Project
    </button>
@endsection

@section('content')

{{-- Stats Row --}}
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon stat-icon-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['total_projects'] }}</span>
            <span class="stat-label">Projects</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['total_tasks'] }}</span>
            <span class="stat-label">Total Tasks</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['in_progress_tasks'] }}</span>
            <span class="stat-label">In Progress</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-emerald">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['completed_tasks'] }}</span>
            <span class="stat-label">Completed</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-amber">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['members'] }}</span>
            <span class="stat-label">Members</span>
        </div>
    </div>
</div>

<div class="two-col-layout">
    {{-- Projects section --}}
    <section>
        <h2 class="section-title">Projects</h2>
        @if($workspace->projects->isEmpty())
        <div class="empty-state-small">No projects yet. Create one to get started.</div>
        @else
        <div class="project-list">
            @foreach($workspace->projects as $project)
            @php
                $total     = $project->tasks_count;
                $done      = $project->completed_tasks_count;
                $progress  = $total > 0 ? round(($done / $total) * 100) : 0;
            @endphp
            <a href="{{ route('workspaces.projects.show', [$workspace, $project]) }}" class="card card-hover project-row" id="project-row-{{ $project->id }}">
                <div class="project-row-info">
                    <div class="project-dot status-{{ $project->status }}"></div>
                    <div>
                        <div class="project-row-name">{{ $project->name }}</div>
                        <div class="project-row-meta">{{ $total }} tasks &middot; {{ $done }} done</div>
                    </div>
                </div>
                <div class="progress-wrap">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $progress }}%"></div>
                    </div>
                    <span class="progress-label">{{ $progress }}%</span>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </section>

    {{-- Activity Feed --}}
    <section>
        <h2 class="section-title">Recent Activity</h2>
        <div class="activity-feed">
            @forelse($recentActivity as $log)
            <div class="activity-item">
                <div class="activity-avatar">{{ substr($log->user->name ?? 'U', 0, 1) }}</div>
                <div class="activity-body">
                    <p class="activity-details">{{ $log->details }}</p>
                    <span class="activity-time">{{ $log->created_at->diffForHumans() }}</span>
                </div>
                <span class="activity-action action-{{ $log->action }}">{{ $log->action }}</span>
            </div>
            @empty
            <div class="empty-state-small">No activity yet.</div>
            @endforelse
        </div>
    </section>
</div>

{{-- Team Members --}}
<section style="margin-top: 2rem;">
    <h2 class="section-title">Team Members</h2>
    <div class="members-grid">
        @foreach($workspace->users as $member)
        <div class="member-card card">
            <div class="member-avatar">{{ substr($member->name, 0, 1) }}</div>
            <div class="member-info">
                <span class="member-name">{{ $member->name }}</span>
                <span class="member-email">{{ $member->email }}</span>
            </div>
            <span class="badge badge-{{ $member->pivot->role }}">{{ $member->pivot->role }}</span>
        </div>
        @endforeach
    </div>
</section>

{{-- Create Project Modal --}}
<div class="modal-overlay" id="modal-create-project" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal">
        <div class="modal-header">
            <h3>New Project</h3>
            <button class="modal-close" onclick="document.getElementById('modal-create-project').classList.remove('open')">&times;</button>
        </div>
        <form method="POST" action="{{ route('workspaces.projects.store', $workspace) }}" class="modal-form">
            @csrf
            <div class="form-group">
                <label for="proj-name">Project Name</label>
                <input type="text" id="proj-name" name="name" placeholder="e.g. API Redesign" required>
            </div>
            <div class="form-group">
                <label for="proj-desc">Description <span class="label-optional">(optional)</span></label>
                <textarea id="proj-desc" name="description" rows="3" placeholder="What is this project about?"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('modal-create-project').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn-create-project-submit">Create Project</button>
            </div>
        </form>
    </div>
</div>

@endsection
