<?php

use WMDE\Fundraising\Deployment\PayloadHandlerDispatcher;
use WMDE\Fundraising\Deployment\PayloadHandlers\AddReleaseState;
use WMDE\Fundraising\Deployment\PDOReleaseState;

// Initialize services

$app['db'] = function ( $app ) {
	return new \PDO( $app['dsn'] );
};

$app['release_state'] = function ( $app ) {
	return new PDOReleaseState( $app['db'] );
};

