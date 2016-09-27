<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Deployment;

use WMDE\Fundraising\Deployment\PayloadHandlers\PayloadHandler;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class PayloadHandlerDispatcher {

	private $payloadHandlers;

	public function __construct( PayloadHandler ...$payloadHandlers ) {
		$this->payloadHandlers = $payloadHandlers;
	}

	public function dispatch( $payload ) {
		foreach( $this->payloadHandlers as $handler ) {
			$handler->handlePayload( $payload );
		}
	}


}