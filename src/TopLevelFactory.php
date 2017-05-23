<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Deployment;

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TopLevelFactory {

	public static function newFromConfig(): self {
		return new self( self::getVarPath() . '/releases.sqlite' );
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
		$this->logger = $this->newDefaultLogger();
	}

	private function newDefaultLogger(): LoggerInterface {
		$logger = new Logger( 'Deployment logger' );

		$streamHandler = new StreamHandler(
			$this->getLoggingPath() . '/error-debug.log'
		);

		$fingersCrossedHandler = new FingersCrossedHandler( $streamHandler );
		$streamHandler->setFormatter( new LineFormatter( LineFormatter::SIMPLE_FORMAT ) );
		$logger->pushHandler( $fingersCrossedHandler );

		$errorHandler = new StreamHandler(
			$this->getLoggingPath() . '/error.log',
			Logger::ERROR
		);

		$errorHandler->setFormatter( new JsonFormatter() );
		$logger->pushHandler( $errorHandler );

		return $logger;
	}

	private function getLoggingPath(): string {
		return self::getVarPath() . '/log';
	}

	private static function getVarPath(): string {
		return __DIR__ . '/../var';
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