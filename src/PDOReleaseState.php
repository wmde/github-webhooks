<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Deployment;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class PDOReleaseState implements ReleaseState {

	private $db;

	public function __construct( \PDO $db ) {
		$this->db = $db;
	}

	public function hasUndeployedReleases( string $branchName ): bool {
		$stmt = $this->db->prepare( 'SELECT COUNT(*) FROM releases WHERE branch = :branchName AND ts_ended IS NULL AND ts_started IS NULL' );
		$stmt->execute( ['branchName' =>  $branchName ] );
		return $stmt->fetchColumn() > 0;
	}

	public function deploymentInProcess( $branchName ): bool {
		$stmt = $this->db->prepare( 'SELECT COUNT(*) FROM releases WHERE branch = :branchName AND ts_ended IS NULL AND ts_started IS NOT NULL' );
		$stmt->execute( ['branchName' =>  $branchName ] );
		return $stmt->fetchColumn() > 0;
	}

	public function getNextReleases(): array {
		$stmt = $this->db->prepare(
			'SELECT branch, refid FROM releases WHERE ts_ended IS NULL AND ts_started IS NULL GROUP BY branch ORDER BY ts_added DESC'
		);
		$stmt->execute();
		$releases = [];
		while( $row = $stmt->fetch( \PDO::FETCH_ASSOC ) ) {
			$releases[ $row['branch'] ] = $row['refid'];
		}
		return $releases;
	}

	public function addRelease( string $branchName, string $refId, $now = '' ) {
		$stmt = $this->db->prepare( 'INSERT INTO releases VALUES( :refId, :branchName, :timestampAdded, NULL, NULL )' );
		$stmt->execute( [
			'branchName' => $branchName,
			'refId' => $refId,
			'timestampAdded' => $now ?: date( DATE_ISO8601 )
		] );
	}

	public function startDeployment( string $refId, string $now = '' ) {
		$stmt = $this->db->prepare( 'UPDATE releases SET ts_started = :timestampStarted WHERE refid = :refId' );
		$stmt->execute( [
			'refId' => $refId,
			'timestampStarted' => $now ?: date( DATE_ISO8601 )
		] );
	}

	public function endDeployment( string $refId, string $now = '' ) {
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