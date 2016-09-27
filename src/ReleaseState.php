<?php
namespace WMDE\Fundraising\Deployment;


/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
interface ReleaseState {

	public function deploymentInProcess( $branchName ) : bool;

	public function getLatestReleases(): array;

	public function addRelease( string $branchName, string $refId, $now = '' );

	public function markDeploymentAsStarted( string $refId, string $now = '' );

	public function markDeploymentAsFinished( string $refId, string $now = '' );
}