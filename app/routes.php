<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @global \Silex\Application $app
 */

$app->post( '/deploy-fundraising', function ( Request $request ) use ($app) {
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

	if ( !empty( $payload->repository->full_name ) &&
		!empty( $payload->ref ) &&
		$payload->repository->full_name === 'wmde/FundraisingFrontend' &&
		in_array( $payload->ref, [ 'refs/heads/master', 'refs/heads/production' ] ) ) {
		$app['release_state']->addRelease( $this->repoFullName . '/' . $this->branchName, $payload->after );
	}

	return new Response( 'Ok' );
} );