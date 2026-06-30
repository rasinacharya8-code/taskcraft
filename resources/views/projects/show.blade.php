@extends('layouts.app')

@section('title', $project->name)
@section('page-title', $project->name)

@section('topbar-actions')
    <a href="{{ route('workspaces.show', $workspace) }}" class="btn btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back
    </a>
    <button class="btn btn-primary" onclick="document.getElementById('modal-create-task').classList.add('open')" id="btn-new-task">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Task
    </button>
@endsection

@section('content')

@if($project->description)
<p class="page-description">{{ $project->description }}</p>
@endif

{{-- Kanban Board --}}
<div class="kanban-board" id="kanban-board">

    @php
    $columns = [
        'todo'        => ['label' => 'To Do',       'color' => 'slate'],
        'in_progress' => ['label' => 'In Progress',  'color' => 'blue'],
        'review'      => ['label' => 'Review',       'color' => 'amber'],
        'completed'   => ['label' => 'Completed',    'color' => 'green'],
    ];
    @endphp

    @foreach($columns as $statusKey => $col)
    <div class="kanban-col" data-status="{{ $statusKey }}" id="col-{{ $statusKey }}">
        <div class="kanban-col-header">
            <span class="col-dot col-dot-{{ $col['color'] }}"></span>
            <span class="col-title">{{ $col['label'] }}</span>
            <span class="col-count">{{ $tasksByStatus[$statusKey]->count() }}</span>
        </div>

        <div class="task-list" id="tasks-{{ $statusKey }}">
            @foreach($tasksByStatus[$statusKey] as $task)
            <div class="task-card priority-{{ $task->priority }}" id="task-card-{{ $task->id }}" draggable="true">
                <a href="{{ route('workspaces.projects.tasks.show', [$workspace, $project, $task]) }}" class="task-card-title">
                    {{ $task->title }}
                </a>
                <div class="task-card-meta">
                    <span class="badge badge-priority-{{ $task->priority }}">{{ $task->priority }}</span>
                    @if($task->due_at)
                    <span class="task-due {{ $task->due_at->isPast() && $task->status !== 'completed' ? 'overdue' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ $task->due_at->format('M d') }}
                    </span>
                    @endif
                </div>
                @if($task->assignee)
                <div class="task-assignee">
                    <div class="mini-avatar" title="{{ $task->assignee->name }}">{{ substr($task->assignee->name, 0, 1) }}</div>
                    <span>{{ $task->assignee->name }}</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

</div>

{{-- Create Task Modal --}}
<div class="modal-overlay" id="modal-create-task" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal">
        <div class="modal-header">
            <h3>New Task</h3>
            <button class="modal-close" onclick="document.getElementById('modal-create-task').classList.remove('open')">&times;</button>
        </div>
        <form method="POST" action="{{ route('workspaces.projects.tasks.store', [$workspace, $project]) }}" class="modal-form">
            @csrf
            <div class="form-group">
                <label for="task-title">Task Title</label>
                <input type="text" id="task-title" name="title" placeholder="e.g. Implement login endpoint" required>
            </div>
            <div class="form-group">
                <label for="task-desc">Description <span class="label-optional">(optional)</span></label>
                <textarea id="task-desc" name="description" rows="3" placeholder="What needs to be done?"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="task-priority">Priority</label>
                    <select id="task-priority" name="priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="task-due">Due Date <span class="label-optional">(optional)</span></label>
                    <input type="date" id="task-due" name="due_at">
                </div>
            </div>
            <div class="form-group">
                <label for="task-assignee">Assign To <span class="label-optional">(optional)</span></label>
                <select id="task-assignee" name="assigned_to">
                    <option value="">Unassigned</option>
                    @foreach($members as $member)
                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('modal-create-task').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn-create-task-submit">Create Task</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Drag-and-drop Kanban functionality
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let draggedCard = null;

document.querySelectorAll('.task-card').forEach(card => {
    card.addEventListener('dragstart', () => {
        draggedCard = card;
        card.classList.add('dragging');
    });
    card.addEventListener('dragend', () => {
        card.classList.remove('dragging');
        draggedCard = null;
    });
});

document.querySelectorAll('.task-list').forEach(col => {
    col.addEventListener('dragover', e => {
        e.preventDefault();
        col.classList.add('drag-over');
    });
    col.addEventListener('dragleave', () => col.classList.remove('drag-over'));
    col.addEventListener('drop', async e => {
        e.preventDefault();
        col.classList.remove('drag-over');
        if (!draggedCard) return;

        const taskId  = draggedCard.id.replace('task-card-', '');
        const newStatus = col.closest('.kanban-col').dataset.status;

        col.appendChild(draggedCard);

        // Update count badges
        document.querySelectorAll('.kanban-col').forEach(c => {
            c.querySelector('.col-count').textContent = c.querySelector('.task-list').children.length;
        });

        // Persist via API
        try {
            await fetch(`{{ url('/workspaces/' . $workspace->id . '/projects/' . $project->id . '/tasks') }}/${taskId}/status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ status: newStatus })
            });
        } catch(err) { console.error('Status update failed', err); }
    });
});
</script>
@endsection
