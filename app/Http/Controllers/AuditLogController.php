<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', 'like', '%' . $request->string('action') . '%'))
            ->latest()
            ->paginate(40)
            ->withQueryString();

        $users = User::orderBy('name')->get();

        return view('audit-logs.index', compact('logs', 'users'));
    }
}
