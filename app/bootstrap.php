<?php

use WMDE\Fundraising\Deployment\PayloadHandlerDispatcher;
use WMDE\Fundraising\Deployment\PayloadHandlers\AddReleaseState;
use WMDE\Fundraising\Deployment\PDOReleaseState;

// Initialize services

/**
 * @global \Silex\Application $app
 * @return \Silex\Application
 */


$app['db'] = function ( $app ) {
	return new \PDO( $app['dsn'] );
};

$app['release_state'] = function ( $app ) {
	return new PDOReleaseState( $app['db'] );
};

$app['payload_dispatcher'] = function ( $app ) {
	return new PayloadHandlerDispatcher(
		new AddReleaseState( 'wmde/FundraisingFrontend', 'master', $app['release_state'] ),
		new AddReleaseState( 'wmde/FundraisingFrontend', 'production', $app['release_state'] )
	);
};

