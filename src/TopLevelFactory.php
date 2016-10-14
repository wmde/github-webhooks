<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Deployment;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TopLevelFactory {

	public static function newFromConfig(): self {
		return new self( __DIR__ . '/../var/releases.sqlite' );
	}

	private $dbDsn;

	/**
	 * @var \PDO|null
	 */
	private $pdo = null;

	/**
	 * @var ReleaseStateWriter|null
	 */
	private $releaseStateWriter = null;

	public function __construct( string $dbDsn ) {
		$this->dbDsn = $dbDsn;
	}

	public function newDeployer( string $branchName ): Deployer {
		return new Deployer( new PdoReleaseRepository( $this->getPdo(), $branchName ) );
	}

	public function getPdo(): \PDO {
		if ( $this->pdo === null ) {
			$this->pdo = new \PDO( $this->dbDsn );
		}

		return $this->pdo;
	}

	public function getReleaseStateWriter(): ReleaseStateWriter {
		if ( $this->releaseStateWriter === null ) {
			$this->releaseStateWriter = new ReleaseStateWriter( $this->getPdo() );
		}

		return $this->releaseStateWriter;
	}

	public function setReleaseStateWriter( ReleaseStateWriter $releaseStateWriter ) {
		$this->releaseStateWriter = $releaseStateWriter;
	}

}