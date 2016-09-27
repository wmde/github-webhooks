<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Deployment\Tests\EdgeToEdge;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
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
		$app = $this->createApplication( $debug );

		if ( is_callable( $onAppCreated ) ) {
			call_user_func( $onAppCreated, $app );
		}

		return new Client( $app );
	}

	private function createApplication( bool $debug ) : Application {
		$app = require __DIR__ . ' /../../app/bootstrap.php';

		require __DIR__ . ' /../../app/routes.php';

		if ( $debug ) {
			$app['debug'] = true;
			unset( $app['exception_handler'] );
		}

		$app['dsn'] = 'sqlite::memory:';

		$app['db']->exec( file_get_contents( __DIR__ .'/../../db/schema.sql' ) );

		return $app;
	}

	protected function assert404( Response $response ) {
		$this->assertSame( 404, $response->getStatusCode() );
	}

}