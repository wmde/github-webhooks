<?php

require_once __DIR__ . '/../vendor/autoload.php';

$topLevelFactory = \WMDE\Fundraising\Deployment\TopLevelFactory::newFromConfig();

$app = require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . ' /../app/routes.php';

$app->run();