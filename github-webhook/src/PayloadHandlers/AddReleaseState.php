<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Deployment\PayloadHandlers;

use PayloadHandlers\PayloadHandler;
use WMDE\Fundraising\Deployment\ReleaseState;

/**
 * Add a new release state when the repository and branch on the payload matches.
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddReleaseState implements PayloadHandler {

	private $repoFullName;
	private $branchName;
	private $releaseStateRepo;

	public function __construct( string $repoFullName, string $branchName, ReleaseState $releaseStateRepo ) {
		$this->repoFullName = $repoFullName;
		$this->branchName = $branchName;
		$this->releaseStateRepo = $releaseStateRepo;
	}

	public function handlePayload( \stdClass $payload ) {
		if ( $this->payloadDoesNotMatch( $payload ) ) {
			return;
		}
		$this->releaseStateRepo->addRelease( $this->repoFullName . '/' . $this->branchName, $payload->after );
	}

	private function payloadDoesNotMatch( \stdClass $payload ): bool {
		return empty( $payload->repository->full_name ) ||
			empty( $payload->ref ) ||
			$payload->repository->full_name !== $this->repoFullName ||
			$payload->ref !== 'refs/heads/' . $this->branchName;
	}
}