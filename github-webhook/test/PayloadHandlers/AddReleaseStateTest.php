<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Deployment\Tests\PayloadHandlers;

use WMDE\Fundraising\Deployment\PayloadHandlers\AddReleaseState;
use WMDE\Fundraising\Deployment\ReleaseState;

class AddReleaseStateTest extends \PHPUnit_Framework_TestCase {

	public function testWhenRepositoryAndBranchMatch_addNewReleaseState() {
		$releaseState = $this->createMock( ReleaseState::class );
		$releaseState->expects( $this->once() )
			->method( 'addRelease' )
			->with( 'baxterthehacker/public-repo/master', '0d1a26e67d8f5eaf1f6ba5c57fc3c7d91ac0fd1c' );

		$handler = new AddReleaseState( 'baxterthehacker/public-repo', 'master', $releaseState );

		$payload = json_decode( file_get_contents( __DIR__ . '/../files/push-payload.json' ) );

		$handler->handlePayload( $payload );
	}

	public function testWhenRepositoryOrBranchAreDifferent_noReleaseStateIsCreated() {
		$releaseState = $this->createMock( ReleaseState::class );
		$releaseState->expects( $this->never() )
			->method( 'addRelease' );

		$payload = json_decode( file_get_contents( __DIR__ . '/../files/push-payload.json' ) );
		$handler = new AddReleaseState( 'baxterthehacker/another-repo', 'master', $releaseState );
		$handler->handlePayload( $payload );

		$handler = new AddReleaseState( 'baxterthehacker/public-repo', 'release', $releaseState );
		$handler->handlePayload( $payload );
	}

}
