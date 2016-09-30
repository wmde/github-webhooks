<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Deployment;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ReleaseStateWriter {
	private $db;

	/**
	 * ReleaseStateWriter constructor.
	 * @param $db
	 */
	public function __construct( \PDO $db ) {
		$this->db = $db;
	}

	public function addRelease( string $branchName, string $refId, $now = '' ) {
		$stmt = $this->db->prepare( 'INSERT INTO releases VALUES( :refId, :branchName, :timestampAdded, NULL, NULL )' );
		$stmt->execute( [
			'branchName' => $branchName,
			'refId' => $refId,
			'timestampAdded' => $now ?: date( DATE_ISO8601 )
		] );
	}
}