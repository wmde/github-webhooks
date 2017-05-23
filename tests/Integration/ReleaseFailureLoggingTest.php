<?php

namespace WMDE\Fundraising\Deployment\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\Process\Process;
use WMDE\Fundraising\Deployment\Tests\TestEnvironment;
use WMDE\Fundraising\Deployment\TopLevelFactory;
use WMDE\PsrLogTestDoubles\LoggerSpy;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ReleaseFailureLoggingTest extends TestCase {

	const BRANCH_NAME = 'master';

	public function testWhenDeploymentFails_loggerGetsAlerted() {
		$loggerSpy = new LoggerSpy();

		$factory = TestEnvironment::newInstance()->getFactory();
		$factory->setLogger( $loggerSpy );

		$this->insertRelease( $factory );

		$command = new Process( 'cd plaintext-passwords/' );

		$factory->newDeployer( self::BRANCH_NAME )->run( $command );

		$this->assertNotEmpty( $loggerSpy->getLogCalls() );
		$this->assertSame( LogLevel::ALERT, $loggerSpy->getLogCalls()->getFirstCall()->getLevel() );
	}

	private function insertRelease( TopLevelFactory $factory ) {
		$factory->getReleaseStateWriter()->addRelease( self::BRANCH_NAME, 'deadbeef' );
	}

}
