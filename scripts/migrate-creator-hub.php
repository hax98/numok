<?php
declare(strict_types=1);
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/config.php';
$db = \Numok\Database\Database::getInstance();
// Only additive changes. Never import deploy.sql into an existing database.
$db->exec(file_get_contents(dirname(__DIR__) . '/database/0005-creator-hub.sql'));
foreach (['customer_key' => 'VARCHAR(100) NULL', 'content_token' => 'VARCHAR(100) NULL', 'currency' => "CHAR(3) NOT NULL DEFAULT 'usd'"] as $column => $definition) {
    $check = $db->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=?');
    $check->execute(['conversions', $column]);
    if (!$check->fetchColumn()) $db->exec("ALTER TABLE conversions ADD COLUMN `$column` $definition");
}
echo "Creator hub additive migration complete.\n";
