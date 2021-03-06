<?php

namespace WMDE\Fundraising\Deployment;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
interface ReleaseRepository {

	public function hasUndeployedReleases(): bool;

	public function deploymentInProcess(): bool;

	public function getLatestReleaseId(): string;

	public function markDeploymentAsStarted( string $refId, string $now = '' );

	public function markDeploymentAsFinished( string $refId, string $now = '' );

	public function markDeploymentAsFailed( string $refId, string $now = '' );
}