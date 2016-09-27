<?php

namespace WMDE\Fundraising\Deployment\Tests;

use WMDE\Fundraising\Deployment\DeploymentWorker;
use WMDE\Fundraising\Deployment\ReleaseState;

use WMDE\Fundraising\Deployment\Tests\CallbackSpy;

class DeploymentWorkerTest extends \PHPUnit_Framework_TestCase {

	public function testGivenReleasesForBranches_eachDeployFunctionIsCalled() {
		$releaseState = $this->createMock( ReleaseState::class );
		$releaseState->method( 'getLatestReleases' )->willReturn( [
			'testBranch' => 'deadbeef',
			'otherBranch' => 'badcoffee'
		] );
		$firstCallback = new CallbackSpy();
		$secondCallback = new CallbackSpy();
		$callbacks = [
			'testBranch' => [ $firstCallback, 'doCallback' ],
			'otherBranch' => [ $secondCallback, 'doCallback' ]
		];

		$worker = new DeploymentWorker( $releaseState, $callbacks );
		$worker->run();
		$this->assertTrue( $firstCallback->wasCalledWith( ['testBranch', 'deadbeef' ] ) );
		$this->assertTrue( $secondCallback->wasCalledWith( ['otherBranch', 'badcoffee' ] ) );
	}

	public function testGivenReleasesInDeployment_onlyDeployFunctionForUnreleasedBranchIsCalled() {
		$releaseState = $this->createMock( ReleaseState::class );
		$releaseState->method( 'getLatestReleases' )->willReturn( [
			'testBranch' => 'deadbeef',
			'otherBranch' => 'badcoffee'
		] );
		$releaseState->method( 'deploymentInProcess' )->will(
			$this->returnValueMap( [
			[ 'testBranch' , true ],
			[ 'otherBranch', false ]
		] ));
		$callbacks = [
			'testBranch' => CallbackSpy::createCallable(),
			'otherBranch' => CallbackSpy::createCallable()
		];

		$worker = new DeploymentWorker( $releaseState, $callbacks );
		$worker->run();
		$this->assertFalse( $callbacks['testBranch'][0]->wasCalledWith( ['testBranch', 'deadbeef' ] ) );
		$this->assertTrue( $callbacks['otherBranch'][0]->wasCalledWith( ['otherBranch', 'badcoffee' ] ) );
	}

	public function testDeploymentWillBeStartedAndEnded() {
		$releaseState = $this->createMock( ReleaseState::class );
		$releaseState->method( 'getLatestReleases' )->willReturn( [
			'testBranch' => 'deadbeef'
		] );

		$releaseState->expects( $this->once() )
			->method( 'markDeploymentAsStarted' )
			->with( $this->equalTo( 'deadbeef' ) );
		$releaseState->expects( $this->once() )
			->method( 'markDeploymentAsFinished' )
			->with( $this->equalTo( 'deadbeef' ) );

		$callbacks = [
			'testBranch' => CallbackSpy::createCallable()
		];
		$worker = new DeploymentWorker( $releaseState, $callbacks );
		$worker->run();
	}

	// TODO: Error recovery. When deployment function throws an error, unset start date, log last try and try again in exponential intervals
}
