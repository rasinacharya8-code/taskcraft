@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'My Workspaces')

@section('topbar-actions')
    <button class="btn btn-primary" onclick="document.getElementById('modal-create-workspace').classList.add('open')" id="btn-new-workspace">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Workspace
    </button>
@endsection

@section('content')

{{-- Workspaces Grid --}}
@if($workspaces->isEmpty())
<div class="empty-state">
    <div class="empty-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
    </div>
    <h2>No workspaces yet</h2>
    <p>Create your first workspace to start organizing projects and tasks with your team.</p>
    <button class="btn btn-primary" onclick="document.getElementById('modal-create-workspace').classList.add('open')">Create Workspace</button>
</div>
@else
<div class="grid grid-cols-3">
    @foreach($workspaces as $ws)
    <a href="{{ route('workspaces.show', $ws) }}" class="card card-hover workspace-card" id="workspace-{{ $ws->id }}">
        <div class="workspace-card-header">
            <div class="workspace-avatar">{{ substr($ws->name, 0, 2) }}</div>
            <div class="workspace-meta">
                <h3>{{ $ws->name }}</h3>
                @if($ws->description)
                <p>{{ Str::limit($ws->description, 60) }}</p>
                @endif
            </div>
        </div>
        <div class="workspace-stats">
            <div class="stat-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                {{ $ws->projects_count }} {{ Str::plural('project', $ws->projects_count) }}
            </div>
            <div class="stat-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                {{ $ws->projects->sum('tasks_count') }} tasks
            </div>
        </div>
        <div class="workspace-role-badge">
            {{ $ws->pivot->role ?? 'member' }}
        </div>
    </a>
    @endforeach
</div>
@endif

{{-- Create Workspace Modal --}}
<div class="modal-overlay" id="modal-create-workspace" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal">
        <div class="modal-header">
            <h3>New Workspace</h3>
            <button class="modal-close" onclick="document.getElementById('modal-create-workspace').classList.remove('open')">&times;</button>
        </div>
        <form method="POST" action="{{ route('workspaces.store') }}" class="modal-form">
            @csrf
            <div class="form-group">
                <label for="ws-name">Workspace Name</label>
                <input type="text" id="ws-name" name="name" placeholder="e.g. Product Team" required>
            </div>
            <div class="form-group">
                <label for="ws-desc">Description <span class="label-optional">(optional)</span></label>
                <textarea id="ws-desc" name="description" rows="3" placeholder="What does this workspace focus on?"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('modal-create-workspace').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn-create-workspace-submit">Create Workspace</button>
            </div>
        </form>
    </div>
</div>

@endsection
