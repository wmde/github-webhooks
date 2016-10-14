<?php

namespace WMDE\Fundraising\Deployment\Tests\Unit;

use WMDE\Fundraising\Deployment\Deployer;
use WMDE\Fundraising\Deployment\ReleaseRepository;
use Symfony\Component\Process\Process;

class DeployerTest extends \PHPUnit_Framework_TestCase {

	const RELEASE_ID = 'deadbeef';
	const PROCESS_ERROR_MESSAGE = 'MAWrror';

	public function testGivenNoReleases_deployCommandIsNotCalled() {
		$releaseState = $this->createMock( ReleaseRepository::class );
		$releaseState->method( 'hasUndeployedReleases' )->willReturn( false );

		$process = $this->createMock( Process::class );
		$process->expects( $this->never() )
			->method( 'run' );

		$deployer = new Deployer( $releaseState);
		$deployer->run( $process );
	}

	public function testGivenReleaseForBranch_deployCommandIsExecuted() {
		$releaseState = $this->createDeployableReleaseState();

		$process = $this->createMock( Process::class );
		$process->expects( $this->once() )
			->method( 'run' );

		$deployer = new Deployer( $releaseState);
		$deployer->run( $process );
	}

	public function testGivenReleaseInDeployment_deployCommandIsNotCalled() {
		$releaseState = $this->createMock( ReleaseRepository::class );
		$releaseState->method( 'hasUndeployedReleases' )->willReturn( true );
		$releaseState->method( 'deploymentInProcess' )->willReturn( true );

		$process = $this->createMock( Process::class );
		$process->expects( $this->never() )
			->method( 'run' );

		$deployer = new Deployer( $releaseState);
		$deployer->run( $process );
	}

	public function testDeploymentWillBeStartedAndEnded() {
		$releaseState = $this->createDeployableReleaseState();

		$releaseState->expects( $this->once() )
			->method( 'markDeploymentAsStarted' )
			->with( $this->equalTo( self::RELEASE_ID ) );
		$releaseState->expects( $this->once() )
			->method( 'markDeploymentAsFinished' )
			->with( $this->equalTo( self::RELEASE_ID ) );

		$deployer = new Deployer( $releaseState );
		$deployer->run( $this->newSucceedingDeployProcess() );
	}

	private function newSucceedingDeployProcess(): Process {
		$process = $this->createMock( Process::class );
		$process->method( 'isSuccessful' )->willReturn( true );
		return $process;
	}

	public function testWhenDeployProcessFails_releaseIsMarkedAsFailed() {
		$releaseState = $this->createDeployableReleaseState();

		$releaseState->expects( $this->once() )
			->method( 'markDeploymentAsFailed' )
			->with( $this->equalTo( self::RELEASE_ID ) );

		$deployer = new Deployer( $releaseState);
		$deployer->run( $this->newFailingDeployProcess() );
	}

	private function newFailingDeployProcess(): Process {
		$process = $this->createMock( Process::class );

		$process->method( 'isSuccessful' )->willReturn( false );
		$process->method( 'getErrorOutput' )->willReturn( self::PROCESS_ERROR_MESSAGE );

		return $process;
	}

	/**
	 * @return ReleaseRepository|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function createDeployableReleaseState() {
		$releaseState = $this->createMock( ReleaseRepository::class );
		$releaseState->method( 'hasUndeployedReleases' )->willReturn( true );
		$releaseState->method( 'deploymentInProcess' )->willReturn( false );
		$releaseState->method( 'getLatestReleaseId' )->willReturn( self::RELEASE_ID );
		return $releaseState;
	}

	public function testWhenDeploymentFails_onDeploymentFailedCallbackGetsCalled() {
		$argumentsPassedToCallback = null;

		$onDeploymentFailedCallback = function() use ( &$argumentsPassedToCallback ) {
			$argumentsPassedToCallback = func_get_args();
		};

		$deployer = new Deployer( $this->createDeployableReleaseState(), $onDeploymentFailedCallback );
		$deployer->run( $this->newFailingDeployProcess() );

		$this->assertSame(
			[ self::RELEASE_ID, self::PROCESS_ERROR_MESSAGE ],
			$argumentsPassedToCallback
		);
	}

	public function testWhenDeploymentSucceeds_onDeploymentFailedCallbackDoesNotGetCalled() {
		$deployer = new Deployer(
			$this->createDeployableReleaseState(),
			function() {
				$this->fail( 'Should not have been called' );
			}
		);

		$deployer->run( $this->newSucceedingDeployProcess() );

		$this->assertTrue( (bool)'Sebastian does not like this' );
	}

}
