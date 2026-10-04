<?php
declare(strict_types=1);

// Initialize error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Define root path
define('ROOT_PATH', dirname(__DIR__));

// Autoload dependencies
require_once ROOT_PATH . '/vendor/autoload.php';

// Load configuration
require_once ROOT_PATH . '/config/config.php';

// Shared session storage survives deployment. Cookies stay local to this portal.
ini_set('session.use_strict_mode','1');
ini_set('session.gc_maxlifetime','86400');
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
session_set_save_handler(new \Numok\Services\PortalSessionHandler(),true);
session_start();

// Get the path
$path = $_SERVER['REQUEST_URI'];
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

// Define routes
$routes = [
    // Partner routes (root level)
    '' => ['LandingController', 'index'],
    'login' => ['PartnerAuthController', 'index'],
    'auth/login' => ['PartnerAuthController', 'login'],
    'register' => ['PartnerAuthController', 'register'],
    'auth/register' => ['PartnerAuthController', 'store'],
    'forgot-password' => ['PasswordResetController', 'showLinkRequestForm'],
    'password/email' => ['PasswordResetController', 'sendResetLinkEmail'],
    'password/reset/(\w+)' => ['PasswordResetController', 'showResetForm'],
    'password/reset' => ['PasswordResetController', 'reset'],
    'settings' => ['PartnerProfileController', 'index'],
    'settings/update' => ['PartnerProfileController', 'update'],
    'logout' => ['PartnerAuthController', 'logout'],
    'dashboard' => ['CreatorHubController', 'index'],
    'creator/profile' => ['CreatorHubController', 'addProfile'],
    'creator/content' => ['CreatorHubController', 'createContent'],
    'creator/content/submit' => ['CreatorHubController', 'submitContent'],
    'creator/connect' => ['CreatorHubController', 'syncAccount'],
    'creator/disconnect' => ['CreatorHubController', 'disconnectAccount'],
    'internal/creator-sync' => ['CreatorSyncController', 'sync'],
    'r/([a-f0-9]{24})' => ['CreatorLinkController', 'content'],
    'go/([A-Za-z0-9_-]{3,50})' => ['CreatorLinkController', 'affiliate'],
    'tracking' => ['PartnerTrackingController', 'index'],
    'earnings' => ['PartnerEarningsController', 'index'],
    'programs' => ['PartnerProgramsController', 'index'],
    'programs/join' => ['PartnerProgramsController', 'join'],
    'programs/update-tracking' => ['PartnerProgramsController', 'updateTracking'],

    // Admin routes (under /admin)
    'admin' => ['DashboardController', 'index'],
    'admin/login' => ['AuthController', 'index'],
    'admin/auth/login' => ['AuthController', 'login'],
    'admin/logout' => ['AuthController', 'logout'],
    'admin/dashboard' => ['DashboardController', 'index'],
    'admin/creators' => ['CreatorAdminController', 'index'],
    'admin/creators/(\d+)/preview' => ['CreatorAdminController', 'preview'],
    'admin/creators/verify-content' => ['CreatorAdminController', 'verifyContent'],
    'admin/creators/record-bonus' => ['CreatorAdminController', 'recordBonus'],
    'admin/creators/sync-referrals' => ['CreatorAdminController', 'syncReferrals'],
    'admin/creators/grant-access' => ['CreatorAdminController', 'grantAccess'],
    'admin/settings' => ['SettingsController', 'index'],
    'admin/settings/update' => ['SettingsController', 'update'],
    'admin/settings/update-branding' => ['SettingsController', 'updateBranding'],
    'admin/settings/reset-branding' => ['SettingsController', 'resetBranding'],
    'admin/settings/update-profile' => ['SettingsController', 'updateProfile'],
    'admin/settings/test-connection' => ['SettingsController', 'testConnection'],

    // Programs routes
    'admin/programs' => ['ProgramsController', 'index'],
    'admin/programs/create' => ['ProgramsController', 'create'],
    'admin/programs/store' => ['ProgramsController', 'store'],
    'admin/programs/(\d+)/edit' => ['ProgramsController', 'edit'],
    'admin/programs/(\d+)/integration' => ['ProgramsController', 'integration'],
    'admin/programs/(\d+)/update' => ['ProgramsController', 'update'],
    'admin/programs/(\d+)/delete' => ['ProgramsController', 'delete'],

    // Partners routes
    'admin/partners' => ['PartnersController', 'index'],
    'admin/partners/create' => ['PartnersController', 'create'],
    'admin/partners/store' => ['PartnersController', 'store'],
    'admin/partners/(\d+)/edit' => ['PartnersController', 'edit'],
    'admin/partners/(\d+)/update' => ['PartnersController', 'update'],
    'admin/partners/(\d+)/delete' => ['PartnersController', 'delete'],
    'admin/partners/(\d+)/assign-program' => ['PartnersController', 'assignProgram'],

    // Conversions routes
    'admin/conversions' => ['ConversionsController', 'index'],
    'admin/conversions/update-status' => ['ConversionsController', 'updateStatus'],
    'admin/conversions/export' => ['ConversionsController', 'export'],

    // API routes
    'api/tracking/config/(\d+)' => ['TrackingController', 'config'],
    'api/tracking/click' => ['TrackingController', 'click'],

    // Webhook routes
    'webhook/stripe' => ['WebhookController', 'stripeWebhook'],
];

// Check if route exists or matches a pattern
$matched = false;

foreach ($routes as $pattern => $route) {
    $pattern = str_replace('/', '\/', $pattern);
    if (preg_match('/^' . $pattern . '$/', $path, $matches)) {
        // Remove the full match from the matches array
        array_shift($matches);

        // Get controller and method
        $controllerName = "Numok\\Controllers\\" . $route[0];
        $methodName = $route[1];

        try {
            // Create controller instance
            $controller = new $controllerName();

            // Type cast numeric parameters to integer
            $params = array_map(function ($value) use ($route) {
                return $route[0] !== 'CreatorLinkController' && is_numeric($value) ? (int) $value : $value;
            }, $matches);

            // Call the method with any captured parameters
            $controller->$methodName(...$params);

            $matched = true;
            break;
        } catch (\Exception $e) {
            // Log error
            error_log($e->getMessage());

            // Show error in development
            if (isset($config['app']['debug']) && $config['app']['debug']) {
                throw $e;
            }

            // Show 500 error in production
            http_response_code(500);
            echo "500 - Internal Server Error";
            exit;
        }
    }
}

if (!$matched) {
    http_response_code(404);
    echo "404 - Page Not Found";
    exit;
}
