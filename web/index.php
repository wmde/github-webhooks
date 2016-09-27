<?php





require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

$app['dsn'] = 'sqlite:' . __DIR__ . '/var/releases.mysql';




$app->run();