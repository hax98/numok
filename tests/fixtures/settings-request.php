<?php
declare(strict_types=1);
namespace Numok\Middleware {
    final class AuthMiddleware {
        public static function adminOnly(): void {}
    }
}
namespace Numok\Controllers {
    class Controller {}
}
namespace Numok\Database {
    final class Database {
        public static array $settings = [];
        public static function transaction(callable $operation): void {
            $before = self::$settings;
            try {
                $operation();
            } catch (\Throwable $error) {
                self::$settings = $before;
                throw $error;
            }
        }
        public static function query(string $sql, array $parameters = []): void {
            if (!str_contains($sql, 'INSERT INTO settings')) {
                throw new \RuntimeException('Unexpected database operation in isolated test.');
            }
            [$name, $value] = $parameters;
            self::$settings[$name] = $value;
        }
    }
}
namespace {
    $input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
    $_SESSION = ['user_email' => 'admin@example.test'];
    $_SERVER['HTTP_HOST'] = 'partners.example.test';
    \Numok\Database\Database::$settings = $input['initial'];
    if (($input['mode'] ?? '') === 'render') {
        $settings = $input['initial'];
        ob_start();
        require __DIR__ . '/../../src/Views/settings/index.php';
        echo json_encode(['html' => ob_get_clean()], JSON_THROW_ON_ERROR);
        exit;
    }
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = $input['post'];
    register_shutdown_function(static function (): void {
        echo json_encode(['settings' => \Numok\Database\Database::$settings, 'session' => $_SESSION], JSON_THROW_ON_ERROR);
    });
    require __DIR__ . '/../../src/Controllers/SettingsController.php';
    (new \Numok\Controllers\SettingsController())->update();
}
