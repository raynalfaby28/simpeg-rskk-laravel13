<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user')
            ->when($module = $request->get('module'), fn ($q) => $q->where('module', $module))
            ->when($action = $request->get('action'), fn ($q) => $q->where('action', $action))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $modules = AuditLog::select('module')->distinct()->orderBy('module')->pluck('module');
        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('audit_logs.index', compact('logs', 'modules', 'actions'));
    }
}