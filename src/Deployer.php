<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Deployment;
use Symfony\Component\Process\Process;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class Deployer {

	private $releaseRepository;
	private $onDeploymentFailed;

	public function __construct( ReleaseRepository $releaseRepository, callable $onDeploymentFailed = null ) {
		$this->releaseRepository = $releaseRepository;
		$this->onDeploymentFailed = $onDeploymentFailed;
	}

	public function run( Process $deployCommand ) {
		if ( !$this->releaseRepository->hasUndeployedReleases() || $this->releaseRepository->deploymentInProcess() ) {
			return;
		}

		$latestReleaseId = $this->releaseRepository->getLatestReleaseId();
		$this->releaseRepository->markDeploymentAsStarted( $latestReleaseId );

		$deployCommand->run();

		if ( $deployCommand->isSuccessful() ) {
			$this->releaseRepository->markDeploymentAsFinished( $latestReleaseId );
		} else {
			if ( is_callable( $this->onDeploymentFailed ) ) {
				call_user_func( $this->onDeploymentFailed, $latestReleaseId, $deployCommand->getErrorOutput() );
			}

			$this->releaseRepository->markDeploymentAsFailed( $latestReleaseId );
		}

	}

}