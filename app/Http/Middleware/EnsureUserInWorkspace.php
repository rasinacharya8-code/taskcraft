<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserInWorkspace
{
    /**
     * Verify the authenticated user belongs to the workspace.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $workspace = $request->route('workspace');

        if (!$workspace instanceof Workspace) {
            $workspace = Workspace::findOrFail($workspace);
        }

        $isMember = $workspace->users()
            ->where('user_id', auth()->id())
            ->exists();

        if (!$isMember) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Share the workspace & user's role with all views in this request
        $role = $workspace->users()
            ->where('user_id', auth()->id())
            ->first()
            ->pivot->role ?? 'viewer';

        view()->share('currentWorkspace', $workspace);
        view()->share('currentUserRole', $role);

        return $next($request);
    }
}
