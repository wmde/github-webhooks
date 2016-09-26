<?php

namespace WMDE\Fundraising\Deployment\PayloadHandlers;

interface PayloadHandler {
	/**
	 * Do something with a GitHub webhook payload.
	 *
	 * @param \stdClass $payload
	 */
	public function handlePayload( \stdClass $payload );
}