<?php

namespace Numok\Controllers;

use Numok\Database\Database;

class LandingController extends Controller
{
    public function index(): void
    {
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
