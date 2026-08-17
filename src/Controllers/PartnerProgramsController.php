<?php

namespace Numok\Controllers;

use Numok\Database\Database;
use Numok\Middleware\PartnerMiddleware;

class PartnerProgramsController extends PartnerBaseController {
    public function __construct() {
        PartnerMiddleware::handle();
    }

    public function index(): void {
        $partnerId = $_SESSION['partner_id'];

        // Get all active programs that are:
        // 1. Public (is_private = 0), OR
        // 2. Already assigned to this partner (joined)
        $programs = Database::query(
            "SELECT p.*, 
                    CASE 
                        WHEN pp.id IS NOT NULL THEN 'joined'
                        ELSE 'available'
                    END as status,
                    pp.tracking_code
             FROM programs p
             LEFT JOIN partner_programs pp ON p.id = pp.program_id 
                AND pp.partner_id = ?
             WHERE p.status = 'active'
               AND (p.is_private = 0 OR pp.id IS NOT NULL)
             ORDER BY p.name",
            [$partnerId]
        )->fetchAll();

        $settings = $this->getSettings();
        $this->view('partner/programs/index', [
            'title' => 'Available Programs - ' . ($settings['custom_app_name'] ?? 'Numok'),
            'programs' => $programs
        ]);
    }

    public function join(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /programs');
            exit;
        }

        $partnerId = $_SESSION['partner_id'];
        $programId = $_POST['program_id'] ?? 0;

        // Validate program exists, is active, and is public (private programs can only be assigned by admin)
        $program = Database::query(
            "SELECT id, terms FROM programs WHERE id = ? AND status = 'active' AND is_private = 0",
            [$programId]
        )->fetch();

        if (!$program) {
            $_SESSION['error'] = 'Invalid program selected';
            header('Location: /programs');
            exit;
        }

        // Check if already joined
        $existing = Database::query(
            "SELECT id FROM partner_programs 
             WHERE partner_id = ? AND program_id = ?",
            [$partnerId, $programId]
        )->fetch();

        if ($existing) {
            $_SESSION['error'] = 'You have already joined this program';
            header('Location: /programs');
            exit;
        }

        // Generate unique tracking code
        $trackingCode = bin2hex(random_bytes(8));

        // Prepare insert data
        $insertData = [
            'partner_id' => $partnerId,
            'program_id' => $programId,
            'tracking_code' => $trackingCode,
            'status' => 'active'
        ];

        // Store terms acceptance details if program has terms
        if (!empty($program['terms'])) {
            $insertData['terms_accepted'] = date('Y-m-d H:i:s');
            $insertData['terms_accepted_ip'] = $_SERVER['REMOTE_ADDR'];
        }

        try {
            Database::insert('partner_programs', $insertData);

            $_SESSION['success'] = 'Successfully joined the program!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to join program. Please try again.';
        }

        header('Location: /programs');
        exit;
    }

    /**
     * Update the tracking code for a partner's joined program.
     *
     * The tracking code is the value used by the public `?via=` link, so it
     * must remain globally unique and URL-safe. The ownership check prevents
     * a partner from changing another partner's link by posting a different
     * partner_program_id.
     */
    public function updateTracking(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /programs');
            exit;
        }

        $partnerId = (int) ($_SESSION['partner_id'] ?? 0);
        $partnerProgramId = filter_var(
            $_POST['partner_program_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        $trackingCode = trim((string) ($_POST['tracking_code'] ?? ''));

        if (!$partnerProgramId) {
            $_SESSION['error'] = 'We could not identify that program.';
            header('Location: /programs');
            exit;
        }

        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{2,49}$/', $trackingCode)) {
            $_SESSION['error'] = 'Use 3 to 50 characters: letters, numbers, hyphens, or underscores.';
            header('Location: /programs');
            exit;
        }

        $ownedProgram = Database::query(
            "SELECT id FROM partner_programs WHERE id = ? AND partner_id = ? LIMIT 1",
            [$partnerProgramId, $partnerId]
        )->fetch();

        if (!$ownedProgram) {
            $_SESSION['error'] = 'That tracking link is not part of your account.';
            header('Location: /programs');
            exit;
        }

        $codeInUse = Database::query(
            "SELECT id FROM partner_programs WHERE tracking_code = ? AND id <> ? LIMIT 1",
            [$trackingCode, $partnerProgramId]
        )->fetch();

        if ($codeInUse) {
            $_SESSION['error'] = 'That tracking code is already in use. Choose another one.';
            header('Location: /programs');
            exit;
        }

        try {
            Database::update(
                'partner_programs',
                ['tracking_code' => $trackingCode],
                'id = ? AND partner_id = ?',
                [$partnerProgramId, $partnerId]
            );
            $_SESSION['success'] = 'Your tracking link was updated.';
        } catch (\Throwable $e) {
            error_log('Tracking code update failed: ' . $e->getMessage());
            $_SESSION['error'] = 'We could not update your tracking link. Please try again.';
        }

        header('Location: /programs');
        exit;
    }
}
