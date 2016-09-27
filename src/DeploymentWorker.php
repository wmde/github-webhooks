<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Deployment;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DeploymentWorker {

	private $releaseState;
	private $deploymentFunctions;

	public function __construct( ReleaseState $releaseState, array $deploymentFunctions ) {
		$this->releaseState = $releaseState;
		$this->deploymentFunctions = $deploymentFunctions;
	}

	public function run() {
		foreach($this->releaseState->getLatestReleases() as $branchName => $refId ) {
			if ( !$this->releaseState->deploymentInProcess( $branchName ) ) {
				$this->releaseState->markDeploymentAsStarted( $refId );
				call_user_func( $this->deploymentFunctions[ $branchName ], $branchName, $refId );
				$this->releaseState->markDeploymentAsFinished( $refId );
			}

		}
	}
}