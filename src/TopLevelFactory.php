<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Deployment;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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
	 * @var LoggerInterface
	 */
	private $logger;

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
		$this->logger = new NullLogger();
	}

	public function newDeployer( string $branchName ): Deployer {
		return new Deployer(
			$this->newReleaseRepository( $branchName ),
			function( string $releaseId, string $errorText ) {
				$this->logger->alert(
					'Deployment failed',
					[
						'Release ID' => $releaseId,
						'Command error output' => $errorText,
					]
				);
			}
		);
	}

	private function newReleaseRepository( string $branchName ): ReleaseRepository {
		return new PdoReleaseRepository( $this->getPdo(), $branchName );
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

	public function setLogger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

}