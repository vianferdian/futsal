<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\FutsalMatch;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $userId = $request->input('user_id');
        $matchId = $request->input('match_id');
        $date = $request->input('date');

        $query = AuditLog::with(['user', 'match'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where('action', 'like', '%' . $search . '%');
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($matchId) {
            $query->where('match_id', $matchId);
        }

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $logs = $query->paginate(25)->withQueryString();

        $users = User::whereIn('id', function($q) {
            $q->select('user_id')->from('audit_logs');
        })->get();

        $matches = FutsalMatch::whereIn('id', function($q) {
            $q->select('match_id')->from('audit_logs');
        })->get();

        return view('pages.admin.audit_logs.index', compact(
            'logs',
            'users',
            'matches',
            'search',
            'userId',
            'matchId',
            'date'
        ));
    }
}
