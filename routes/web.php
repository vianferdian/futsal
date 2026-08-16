<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return match ($user->role) {
            \App\Enums\UserRole::ADMIN => redirect()->route('admin.dashboard'),
            \App\Enums\UserRole::SUPERVISOR => redirect()->route('supervisor.dashboard'),
            \App\Enums\UserRole::TEAM_ADMIN => redirect()->route('team.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    // CRUD Pengawas (Supervisor)
    Route::get('/users/supervisors', [\App\Http\Controllers\Admin\UserController::class, 'indexSupervisors'])->name('admin.users.supervisors.index');
    Route::get('/users/supervisors/create', [\App\Http\Controllers\Admin\UserController::class, 'createSupervisor'])->name('admin.users.supervisors.create');
    Route::post('/users/supervisors', [\App\Http\Controllers\Admin\UserController::class, 'storeSupervisor'])->name('admin.users.supervisors.store');
    Route::get('/users/supervisors/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'editSupervisor'])->name('admin.users.supervisors.edit');
    Route::put('/users/supervisors/{user}', [\App\Http\Controllers\Admin\UserController::class, 'updateSupervisor'])->name('admin.users.supervisors.update');
    Route::delete('/users/supervisors/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroySupervisor'])->name('admin.users.supervisors.destroy');

    // CRUD Admin Tim (Team Admin)
    Route::get('/users/team-admins', [\App\Http\Controllers\Admin\UserController::class, 'indexTeamAdmins'])->name('admin.users.team-admins.index');
    Route::get('/users/team-admins/create', [\App\Http\Controllers\Admin\UserController::class, 'createTeamAdmin'])->name('admin.users.team-admins.create');
    Route::post('/users/team-admins', [\App\Http\Controllers\Admin\UserController::class, 'storeTeamAdmin'])->name('admin.users.team-admins.store');
    Route::get('/users/team-admins/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'editTeamAdmin'])->name('admin.users.team-admins.edit');
    Route::put('/users/team-admins/{user}', [\App\Http\Controllers\Admin\UserController::class, 'updateTeamAdmin'])->name('admin.users.team-admins.update');
    Route::delete('/users/team-admins/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroyTeamAdmin'])->name('admin.users.team-admins.destroy');

    // CRUD Administrator
    Route::get('/users/admins', [\App\Http\Controllers\Admin\UserController::class, 'indexAdmins'])->name('admin.users.admins.index');
    Route::get('/users/admins/create', [\App\Http\Controllers\Admin\UserController::class, 'createAdmin'])->name('admin.users.admins.create');
    Route::post('/users/admins', [\App\Http\Controllers\Admin\UserController::class, 'storeAdmin'])->name('admin.users.admins.store');
    Route::get('/users/admins/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'editAdmin'])->name('admin.users.admins.edit');
    Route::put('/users/admins/{user}', [\App\Http\Controllers\Admin\UserController::class, 'updateAdmin'])->name('admin.users.admins.update');
    Route::delete('/users/admins/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroyAdmin'])->name('admin.users.admins.destroy');

    // CRUD Master Data
    Route::resource('/competitions', \App\Http\Controllers\Admin\CompetitionController::class)->names('admin.competitions');
    Route::resource('/teams', \App\Http\Controllers\Admin\TeamController::class)->names('admin.teams');
    Route::resource('/players', \App\Http\Controllers\Admin\PlayerController::class)->names('admin.players');
    Route::resource('/officials', \App\Http\Controllers\Admin\OfficialController::class)->names('admin.officials');
    Route::resource('/venues', \App\Http\Controllers\Admin\VenueController::class)->names('admin.venues');

    // CRUD Pertandingan (Match) & Tugas Pengawas
    Route::resource('/matches', \App\Http\Controllers\Admin\MatchController::class)->names('admin.matches');
    Route::post('/matches/{match}/assign-supervisor', [\App\Http\Controllers\Admin\MatchController::class, 'assignSupervisor'])->name('admin.matches.assign-supervisor');
    Route::delete('/matches/{match}/unassign-supervisor/{user}', [\App\Http\Controllers\Admin\MatchController::class, 'unassignSupervisor'])->name('admin.matches.unassign-supervisor');

    // Audit Logs
    Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('admin.audit-logs.index');

    // Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('admin.settings.update');
});

// Route Supervisor
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Supervisor\DashboardController::class, 'index'])->name('supervisor.dashboard');
    
    // Lineup Verification
    Route::get('/matches/{match}/verify-lineup', [\App\Http\Controllers\Supervisor\LineupVerificationController::class, 'verifyForm'])->name('supervisor.matches.verify-lineup');
    Route::post('/matches/{match}/verify-lineup/{team}/approve', [\App\Http\Controllers\Supervisor\LineupVerificationController::class, 'approveLineup'])->name('supervisor.matches.verify-lineup.approve');
    Route::post('/matches/{match}/verify-lineup/{team}/unlock', [\App\Http\Controllers\Supervisor\LineupVerificationController::class, 'unlockLineup'])->name('supervisor.matches.verify-lineup.unlock');
    Route::post('/matches/{match}/verify-lineup/{team}/jersey', [\App\Http\Controllers\Supervisor\LineupVerificationController::class, 'updateJersey'])->name('supervisor.matches.verify-lineup.jersey');

    // Match Workspace
    Route::get('/matches/{match}/workspace', [\App\Http\Controllers\Supervisor\MatchWorkspaceController::class, 'showWorkspace'])->name('supervisor.matches.workspace');
    Route::post('/matches/{match}/start', [\App\Http\Controllers\Supervisor\MatchWorkspaceController::class, 'startMatch'])->name('supervisor.matches.start');
    Route::post('/matches/{match}/pause', [\App\Http\Controllers\Supervisor\MatchWorkspaceController::class, 'pauseTimer'])->name('supervisor.matches.pause');
    Route::post('/matches/{match}/resume', [\App\Http\Controllers\Supervisor\MatchWorkspaceController::class, 'resumeTimer'])->name('supervisor.matches.resume');
    Route::post('/matches/{match}/end-first-half', [\App\Http\Controllers\Supervisor\MatchWorkspaceController::class, 'endFirstHalf'])->name('supervisor.matches.end-first-half');
    Route::post('/matches/{match}/start-second-half', [\App\Http\Controllers\Supervisor\MatchWorkspaceController::class, 'startSecondHalf'])->name('supervisor.matches.start-second-half');
    Route::post('/matches/{match}/finish', [\App\Http\Controllers\Supervisor\MatchWorkspaceController::class, 'finishMatch'])->name('supervisor.matches.finish');

    // Live Match Events
    Route::post('/matches/{match}/events/goal', [\App\Http\Controllers\Supervisor\MatchEventController::class, 'storeGoal'])->name('supervisor.matches.events.goal');
    Route::post('/matches/{match}/events/card', [\App\Http\Controllers\Supervisor\MatchEventController::class, 'storeCard'])->name('supervisor.matches.events.card');
    Route::post('/matches/{match}/events/foul', [\App\Http\Controllers\Supervisor\MatchEventController::class, 'storeFoul'])->name('supervisor.matches.events.foul');
    Route::post('/matches/{match}/events/timeout', [\App\Http\Controllers\Supervisor\MatchEventController::class, 'storeTimeout'])->name('supervisor.matches.events.timeout');
    Route::post('/matches/{match}/events/official-card', [\App\Http\Controllers\Supervisor\MatchEventController::class, 'storeOfficialCard'])->name('supervisor.matches.events.official-card');
    Route::delete('/matches/{match}/events/{event}', [\App\Http\Controllers\Supervisor\MatchEventController::class, 'destroy'])->name('supervisor.matches.events.destroy');

    // Post-Match Report
    Route::get('/matches/{match}/report', [\App\Http\Controllers\Supervisor\MatchReportController::class, 'show'])->name('supervisor.matches.report');
    Route::post('/matches/{match}/report', [\App\Http\Controllers\Supervisor\MatchReportController::class, 'save'])->name('supervisor.matches.report.save');
    Route::post('/matches/{match}/report/submit', [\App\Http\Controllers\Supervisor\MatchReportController::class, 'submit'])->name('supervisor.matches.report.submit');
});

// Route Team Admin
Route::middleware(['auth', 'role:team_admin'])->prefix('team')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Team\DashboardController::class, 'index'])->name('team.dashboard');
    
    // Lineup Management
    Route::get('/matches/{match}/lineup', [\App\Http\Controllers\Team\LineupController::class, 'showForm'])->name('team.matches.lineup');
    Route::post('/matches/{match}/lineup', [\App\Http\Controllers\Team\LineupController::class, 'saveLineup'])->name('team.matches.lineup.save');
});

// Route General Authenticated
Route::middleware(['auth'])->group(function () {
    Route::get('/matches/{match}/start-list', [\App\Http\Controllers\Admin\ReportController::class, 'startList'])->name('matches.start-list');
    Route::get('/matches/{match}/summary', [\App\Http\Controllers\Admin\ReportController::class, 'matchSummary'])->name('matches.summary');
    Route::get('/matches/{match}/summary/pdf', [\App\Http\Controllers\Admin\ReportController::class, 'matchSummaryPdf'])->name('matches.summary.pdf');
});
