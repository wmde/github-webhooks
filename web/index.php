<?php





require_once __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../app/bootstrap.php';

$app['dsn'] = 'sqlite:' . __DIR__ . '/var/releases.mysql';

$app->run();