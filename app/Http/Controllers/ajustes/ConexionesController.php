<?php

namespace App\Http\Controllers\ajustes;

use App\Http\Controllers\Controller;
use App\Models\Session as UserSession;
use Illuminate\Support\Collection;

class ConexionesController extends Controller
{
    /**
     * Show the list of active user sessions.
     */
    public function index()
    {
        $sessions = UserSession::with('user')
            ->orderByDesc('last_activity')
            ->get();

        $activeSessions = $sessions->filter->is_online;

        $sessionsByUser = $sessions
            ->groupBy(function (UserSession $session) {
                if ($session->user_id) {
                    return 'user-' . $session->user_id;
                }

                if ($session->payload_user_id) {
                    return 'payload-' . $session->payload_user_id;
                }

                return 'guest';
            })
            ->map(function (Collection $group, string $identifier) {
                $latestSession = $group->sortByDesc('last_activity')->first();

                $payloadUserId = $latestSession->payload_user_id;
                $userId = $group->pluck('user_id')->filter()->first() ?? $payloadUserId;

                $displayName = $latestSession->user_display_name;
                $displayEmail = $latestSession->user_display_email;

                if ($identifier === 'guest') {
                    $displayName = 'Sesiones invitadas';
                    $displayEmail = '—';
                }

                return [
                    'identifier' => $identifier,
                    'user' => $latestSession->user,
                    'user_id' => $userId,
                    'payload_user_id' => $payloadUserId,
                    'display_name' => $displayName,
                    'display_email' => $displayEmail,
                    'sessions' => $group,
                    'active_count' => $group->filter->is_online->count(),
                    'total_count' => $group->count(),
                    'last_activity_at' => $latestSession->last_activity_at,
                ];
            })
            ->sortByDesc('last_activity_at')
            ->values();

        $statistics = [
            'total_sessions' => $sessions->count(),
            'active_sessions' => $activeSessions->count(),
            'unique_users' => $sessionsByUser->reject(fn ($summary) => $summary['identifier'] === 'guest')->count(),
            'active_users' => $sessionsByUser->reject(fn ($summary) => $summary['identifier'] === 'guest')
                ->filter(fn ($summary) => $summary['active_count'] > 0)
                ->count(),
        ];

        return view('ajustes.Conexiones.index', [
            'sessions' => $sessions,
            'statistics' => $statistics,
            'sessionsByUser' => $sessionsByUser,
        ]);
    }
}
