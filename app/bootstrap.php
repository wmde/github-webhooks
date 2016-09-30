<?php

use WMDE\Fundraising\Deployment\ReleaseStateWriter;

// Initialize services

$app = new Silex\Application();

$app['db'] = function ( $app ) {
	return new \PDO( $app['dsn'] );
};

$app['release_state_writer'] = function ( $app ) {
	return new ReleaseStateWriter( $app['db'] );
};

return $app;