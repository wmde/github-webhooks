<?php

namespace WMDE\Fundraising\Deployment\Tests\EdgeToEdge;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Deployment\ReleaseStateWriter;

class DeploymentRouteTest extends WebRouteTestCase  {

	public function testGivenMissingGithubHeaders_requestIsRejected() {
		$client = $this->createClient();
		$client->request( 'POST', '/deploy' );
		$this->assertSame( Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode() );
	}

	public function testGivenWrongGithubEvent_requestIsRejected() {
		$client = $this->createClient();
		$client->request( 'POST', '/deploy', [], [], [ 'HTTP_X-GitHub-Event' => 'dummy' ] );
		$this->assertSame( Response::HTTP_NOT_IMPLEMENTED, $client->getResponse()->getStatusCode() );
	}

	public function testGivenInvalidJsonContent_requestIsRejected() {
		$client = $this->createClient();
		$client->request( 'POST', '/deploy', [], [], $this->getValidHeaders(), '~=[,,_,,]:3' );
		$this->assertSame( Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode() );
	}

	public function testGivenAValidPayload_aReleaseIsCreated() {
		$releaseState = $this->getMockBuilder( ReleaseStateWriter::class )->disableOriginalConstructor()->getMock();
		$releaseState->expects( $this->once() )
			->method( 'addRelease' )
			->with( 'master', '0d1a26e67d8f5eaf1f6ba5c57fc3c7d91ac0fd1c' );
		$client = $this->createClient( function ( Application $app ) use ( $releaseState ){
			$app['release_state_writer'] = $releaseState;
		} );
		$client->request( 'POST', '/deploy', [], [], $this->getValidHeaders(), $this->getValidPayload() );
		$this->assertTrue( $client->getResponse()->isOk() );
	}

	public function testGivenADifferentRepositoryName_noReleaseIsCreated() {
		$releaseState = $this->getMockBuilder( ReleaseStateWriter::class )->disableOriginalConstructor()->getMock();
		$releaseState->expects( $this->never() )
			->method( 'addRelease' );
		$client = $this->createClient( function ( Application $app ) use ( $releaseState ){
			$app['release_state_writer'] = $releaseState;
		} );
		$client->request( 'POST', '/deploy', [], [], $this->getValidHeaders(), $this->getPayloadWithDifferentRepositoryName() );
		$this->assertTrue( $client->getResponse()->isOk() );
	}

	private function getValidHeaders() {
		return [ 'HTTP_X-GitHub-Event' => 'push' ];
	}

	private function getValidPayload() {
		return file_get_contents( __DIR__ . '/../files/push-payload.json' );
	}

	private function getPayloadWithDifferentRepositoryName() {
		return str_replace( 'wmde/FundraisingFrontend', 'wmde/FundraisingBackend', $this->getValidPayload());
	}

}
