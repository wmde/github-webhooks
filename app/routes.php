<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @global \Silex\Application $app
 */

$app->post( '/webhook', function ( Request $request ) use ($app) {
	if ( !$request->headers->has( 'X-GitHub-Event' ) ) {
		return new Response( 'Bad request - X-GitHub-Event header missing', Response::HTTP_BAD_REQUEST );
	}
	if ( $request->headers->get( 'X-GitHub-Event' ) !== 'push' ) {
		return new Response( 'Unsupported event.', Response::HTTP_NOT_IMPLEMENTED );
	}
	$payload = json_decode( $request->getContent() );
	if( !$payload ) {
		return new Response( 'Bad request - Could not decode payload', Response::HTTP_BAD_REQUEST );
	}
	$app['payload_dispatcher']->dispatch( $payload );
	return new Response( 'Ok' );
} );