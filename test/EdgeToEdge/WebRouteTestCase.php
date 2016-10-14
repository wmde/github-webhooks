<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Deployment\Tests\EdgeToEdge;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Deployment\TopLevelFactory;

/**
 * @licence GNU GPL v2+
 */
abstract class WebRouteTestCase extends \PHPUnit_Framework_TestCase {

	const DISABLE_DEBUG = false;
	const ENABLE_DEBUG = true;

	/**
	 * @param callable|null $onAppCreated
	 * @param bool $debug
	 *
	 * @return Client
	 */
	public function createClient( callable $onAppCreated = null, bool $debug = true ): Client {
		$topLevelFactory = new TopLevelFactory( 'sqlite::memory:' );
		$topLevelFactory->getPdo()->exec( file_get_contents( __DIR__ .'/../../db/schema.sql' ) );

		$app = $this->createApplication( $debug, $topLevelFactory );

		if ( is_callable( $onAppCreated ) ) {
			call_user_func( $onAppCreated, $topLevelFactory, $app );
		}

		return new Client( $app );
	}

	private function createApplication( bool $debug, TopLevelFactory $topLevelFactory ) : Application {
		$app = require __DIR__ . ' /../../app/bootstrap.php';

		require __DIR__ . ' /../../app/routes.php';

		if ( $debug ) {
			$app['debug'] = true;
			unset( $app['exception_handler'] );
		}

		return $app;
	}

	protected function assert404( Response $response ) {
		$this->assertSame( 404, $response->getStatusCode() );
	}

}