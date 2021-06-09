<?php

$database_config = [
 'driver'=>'mysql',
    'host'=>'127.0.0.1',
    'database'=>'hadrniva_brighthat',
    'username'=>'hadrniva_brighthat',
    'password'=>'secondtoNone***%$@^@',
    'charset'=>'utf8',
    'collation'=>'utf8_unicode_ci',
    'prefix'=>''

];

$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($database_config);
$capsule->setAsGlobal();
$capsule->bootEloquent();

return $capsule;