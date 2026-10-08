<?php
declare(strict_types=1);
// Pure unit checks can run without installing production provider clients.
spl_autoload_register(static function(string $class):void {
    $prefix='Numok\\';
    if (!str_starts_with($class,$prefix)) return;
    $file=dirname(__DIR__).'/src/'.str_replace('\\','/',substr($class,strlen($prefix))).'.php';
    if (is_file($file)) require $file;
});
