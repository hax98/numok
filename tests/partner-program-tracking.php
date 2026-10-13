<?php
declare(strict_types=1);

// Run with PDO SQLite. Fixtures and sessions are local; no provider config is loaded.
require __DIR__.'/bootstrap.php';

use Numok\Controllers\PartnerProgramsController;
use Numok\Database\Database;

if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
    throw new RuntimeException('PDO SQLite is required for this isolated regression test.');
}
$db = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
(new ReflectionProperty(Database::class, 'instance'))->setValue(null, $db);
$db->exec("CREATE TABLE partners(id INTEGER PRIMARY KEY,status TEXT);
    CREATE TABLE programs(id INTEGER PRIMARY KEY,name TEXT,description TEXT,status TEXT,
        is_private INTEGER,terms TEXT,commission_type TEXT,commission_value REAL,
        is_recurring INTEGER,cookie_days INTEGER);
    CREATE TABLE partner_programs(id INTEGER PRIMARY KEY,partner_id INTEGER,program_id INTEGER,
        tracking_code TEXT,status TEXT);
    CREATE TABLE creator_tracking_aliases(tracking_code TEXT,partner_program_id INTEGER);
    CREATE TABLE settings(name TEXT,value TEXT);
    INSERT INTO partners VALUES(11,'active'),(22,'active');
    INSERT INTO programs VALUES(1,'Joined program','Fixture','active',0,'','percentage',20,1,360),
        (2,'Available program','Fixture','active',0,'','percentage',20,1,360);
    INSERT INTO partner_programs VALUES(77,11,1,'own_fixture','active'),
        (1,22,1,'foreign_fixture','active');");
session_start(['use_cookies'=>false,'cache_limiter'=>'']);
$_SESSION = ['partner_id'=>11,'creator_csrf'=>'isolated-fixture-token'];
$_SERVER['REQUEST_METHOD'] = 'GET';

final class TrackingProgramsTestController extends PartnerProgramsController {
    public string $html = '';
    public array $programRows = [];
    protected function view(string $template, array $data = []): void {
        $this->programRows = $data['programs'];
        extract($data);
        ob_start();
        require dirname(__DIR__).'/src/Views/'.$template.'.php';
        $this->html = (string) ob_get_clean();
    }
}

$mode = $argv[1] ?? '';
if ($mode !== '') {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = ['partner_program_id'=>1,'tracking_code'=>'attempted_change',
        'csrf'=>$mode === '--invalid-csrf' ? 'wrong-token' : 'isolated-fixture-token'];
    register_shutdown_function(static function() use ($db): void {
        echo "\nRESULT:".json_encode([
            'error'=>$_SESSION['error'] ?? null,
            'httpStatus'=>http_response_code(),
            'links'=>$db->query('SELECT id,tracking_code FROM partner_programs ORDER BY id')->fetchAll(),
            'aliases'=>(int)$db->query('SELECT COUNT(*) FROM creator_tracking_aliases')->fetchColumn(),
        ], JSON_THROW_ON_ERROR);
    });
    (new TrackingProgramsTestController())->updateTracking();
    throw new RuntimeException('The rejected request did not exit.');
}

$checks = 0;
$check = static function(bool $ok, string $message) use (&$checks): void {
    if (!$ok) throw new RuntimeException($message);
    $checks++;
};
$controller = new TrackingProgramsTestController();
$controller->index();
$joined = array_values(array_filter($controller->programRows, static fn($p)=>$p['status']==='joined'));
$available = array_values(array_filter($controller->programRows, static fn($p)=>$p['status']==='available'));
$check(count($joined)===1 && $joined[0]['id']===1, 'The joined program retains its program ID.');
$check(($joined[0]['partner_program_id'] ?? null)===77, 'The joined row exposes its distinct membership ID.');
$check(count($available)===1 && $available[0]['id']===2, 'Unjoined public programs remain available.');
$check(($available[0]['partner_program_id'] ?? null)===null, 'Unjoined programs do not gain membership IDs.');
preg_match('/name="partner_program_id" value="(\d+)"/', $controller->html, $posted);
$check(($posted[1] ?? '')==='77', 'The actual rendered rename form submits the membership ID.');
$check(str_contains($controller->html, 'name="program_id" value="2"'), 'The join form still submits the program ID.');
$owned = Database::query('SELECT id FROM partner_programs WHERE id=? AND partner_id=? LIMIT 1', [(int)$posted[1],11])->fetch();
$check(($owned['id'] ?? null)===77, 'The rendered rename ID resolves to the authenticated partner.');

foreach (['--foreign-membership','--invalid-csrf'] as $case) {
    $process = proc_open([PHP_BINARY,'-d','extension_dir='.ini_get('extension_dir'),
        '-d','extension=pdo_sqlite',__FILE__,$case],
        [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
    if (!is_resource($process)) throw new RuntimeException('Could not run isolated rejection request.');
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $status = proc_close($process);
    $check($status===0 && $error==='', 'Rejected request completed without runtime errors.');
    $position = strrpos($output, 'RESULT:');
    $result = $position===false ? null : json_decode(substr($output,$position+7),true,512,JSON_THROW_ON_ERROR);
    $check(is_array($result), 'Rejected request produced a complete observation.');
    if ($case==='--foreign-membership') {
        $check($result['error']==='That tracking link is not part of your account.', 'Foreign membership IDs remain rejected.');
    } else {
        $check($result['httpStatus']===403, 'Invalid CSRF remains rejected.');
    }
    $check($result['links']===[['id'=>1,'tracking_code'=>'foreign_fixture'],['id'=>77,'tracking_code'=>'own_fixture']], 'Rejected requests leave both partners links unchanged.');
    $check($result['aliases']===0, 'Rejected requests create no aliases.');
}
echo "Partner tracking regression checks passed: {$checks}. Local fixtures only.\n";
