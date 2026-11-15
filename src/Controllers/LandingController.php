<?php

namespace Numok\Controllers;

use Numok\Database\Database;

class LandingController extends Controller
{
    public function index(): void
    {
        // Partners who are already logged in go straight to their dashboard.
        if (!empty($_SESSION['partner_id'])) {
            header('Location: /dashboard', true, 302);
            exit;
        }

        $program = null;

        try {
            $program = Database::query(
                "SELECT name, description, commission_type, commission_value, cookie_days, is_recurring
                 FROM programs
                 WHERE status = 'active' AND is_private = 0
                 ORDER BY id DESC LIMIT 1"
            )->fetch() ?: null;
        } catch (\Throwable $e) {
            // The landing page remains useful during the first database boot.
        }

        $this->view('landing/index', [
            'title' => 'Repostit Partner Program',
            'program' => $program,
        ]);
    }
}
