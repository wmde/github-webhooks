<?php

namespace WMDE\Fundraising\Deployment\Tests;

use WMDE\Fundraising\Deployment\Deployer;
use WMDE\Fundraising\Deployment\ReleaseState;
use Symfony\Component\Process\Process;

class DeployerTest extends \PHPUnit_Framework_TestCase {

	const RELEASE_ID = 'deadbeef';

	public function testGivenNoReleases_deployCommandIsNotCalled() {
		$releaseState = $this->createMock( ReleaseState::class );
		$releaseState->method( 'hasUndeployedReleases' )->willReturn( false );

		$command = $this->createMock( Process::class );
		$command->expects( $this->never() )
			->method( 'run' );

		$deployer = new Deployer( $releaseState);
		$deployer->run( $command );
	}

	public function testGivenReleaseForBranch_deployCommandIsExecuted() {
		$releaseState = $this->createDeployableReleaseState();

		$command = $this->createMock( Process::class );
		$command->expects( $this->once() )
			->method( 'run' );

		$deployer = new Deployer( $releaseState);
		$deployer->run( $command );
	}

	public function testGivenReleaseInDeployment_deployCommandIsNotCalled() {
		$releaseState = $this->createMock( ReleaseState::class );
		$releaseState->method( 'hasUndeployedReleases' )->willReturn( true );
		$releaseState->method( 'deploymentInProcess' )->willReturn( true );

		$command = $this->createMock( Process::class );
		$command->expects( $this->never() )
			->method( 'run' );

		$deployer = new Deployer( $releaseState);
		$deployer->run( $command );
	}

	public function testDeploymentWillBeStartedAndEnded() {
		$releaseState = $this->createDeployableReleaseState();

		$releaseState->expects( $this->once() )
			->method( 'markDeploymentAsStarted' )
			->with( $this->equalTo( self::RELEASE_ID ) );
		$releaseState->expects( $this->once() )
			->method( 'markDeploymentAsFinished' )
			->with( $this->equalTo( self::RELEASE_ID ) );

		$command = $this->createMock( Process::class );
		$command->method( 'isSuccessful' )->willReturn( true );
		$deployer = new Deployer( $releaseState);
		$deployer->run( $command );
	}

	public function testGivenFailingCommand_ReleaseIsMarkedAsFailed() {
		$releaseState = $this->createDeployableReleaseState();

		$releaseState->expects( $this->once() )
			->method( 'markDeploymentAsFailed' )
			->with( $this->equalTo( self::RELEASE_ID ) );

		$command = $this->createMock( Process::class );
		$command->method( 'isSuccessful' )->willReturn( false );

		$deployer = new Deployer( $releaseState);
		$deployer->run( $command );
	}

	/**
	 * @return ReleaseState|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function createDeployableReleaseState() {
		$releaseState = $this->createMock( ReleaseState::class );
		$releaseState->method( 'hasUndeployedReleases' )->willReturn( true );
		$releaseState->method( 'deploymentInProcess' )->willReturn( false );
		$releaseState->method( 'getLatestReleaseId' )->willReturn( self::RELEASE_ID );
		return $releaseState;
	}

}
