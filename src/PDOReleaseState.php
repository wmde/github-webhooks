<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Deployment;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class PDOReleaseState implements ReleaseState {

	private $db;
	private $branchName;

	public function __construct( \PDO $db, string $branchName ) {
		$this->db = $db;
		$this->branchName = $branchName;
	}

	public function hasUndeployedReleases(): bool {
		$stmt = $this->db->prepare( 'SELECT COUNT(*) FROM releases WHERE branch = :branchName AND ts_ended IS NULL AND ts_started IS NULL' );
		$stmt->execute( ['branchName' =>  $this->branchName ] );
		return $stmt->fetchColumn() > 0;
	}

	public function deploymentInProcess(): bool {
		$stmt = $this->db->prepare( 'SELECT COUNT(*) FROM releases WHERE branch = :branchName AND ts_ended IS NULL AND ts_started IS NOT NULL' );
		$stmt->execute( ['branchName' =>  $this->branchName ] );
		return $stmt->fetchColumn() > 0;
	}

	public function getLatestRelease(): string {
		$stmt = $this->db->prepare(
			'SELECT refid FROM releases WHERE ts_ended IS NULL AND ts_started IS NULL AND branch = :branchName ORDER BY ts_added DESC LIMIT 1'
		);
		$stmt->execute( ['branchName' =>  $this->branchName ] );
		return $stmt->fetchColumn();
	}

	public function markDeploymentAsStarted( string $refId, string $now = '' ) {
		$stmt = $this->db->prepare( 'UPDATE releases SET ts_started = :timestampStarted WHERE refid = :refId' );
		$stmt->execute( [
			'refId' => $refId,
			'timestampStarted' => $now ?: date( DATE_ISO8601 )
		] );
	}

	public function markDeploymentAsFinished( string $refId, string $now = '' ) {
		$stmt = $this->db->prepare( 'UPDATE releases SET ts_ended = :timestampEnded WHERE refid = :refId' );
		$stmt->execute( [
			'refId' => $refId,
			'timestampEnded' => $now ?: date( DATE_ISO8601 )
		] );
		$this->updateOlderReleases( $refId );
	}

	private function updateOlderReleases( string $refId ) {
		$stmt = $this->db->prepare( 'SELECT * FROM releases WHERE refid = :refId' );
		$stmt->execute( [ 'refId' => $refId ] );
		$row = $stmt->fetch();

		$stmt = $this->db->prepare(
			'UPDATE releases SET ts_started = :timestampStarted, ts_ended = :timestampEnded ' .
			'WHERE ts_started IS NULL AND ts_ended IS NULL AND branch = :branchName AND ts_added < :timestampAdded'
		);
		$stmt->execute( [
			'branchName' => $row['branch'],
			'timestampAdded' => $row['ts_added'],
			'timestampStarted' => $row['ts_started'],
			'timestampEnded' => $row['ts_ended'],
		] );
	}
}