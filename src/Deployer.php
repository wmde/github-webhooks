<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Deployment;
use Symfony\Component\Process\Process;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class Deployer {

	private $releaseState;

	public function __construct( ReleaseRepository $releaseState ) {
		$this->releaseState = $releaseState;
	}

	public function run( Process $deployCommand ) {
		if ( !$this->releaseState->hasUndeployedReleases() || $this->releaseState->deploymentInProcess() ) {
			return;
		}
		$latestReleaseId = $this->releaseState->getLatestReleaseId();
		$this->releaseState->markDeploymentAsStarted( $latestReleaseId );
		$deployCommand->run();
		if ( $deployCommand->isSuccessful() ) {
			$this->releaseState->markDeploymentAsFinished( $latestReleaseId );
		} else {
			$this->releaseState->markDeploymentAsFailed( $latestReleaseId );
		}

	}

}