<?php

namespace WMDE\Fundraising\Deployment\Tests;

use WMDE\Fundraising\Deployment\TopLevelFactory;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TestEnvironment {

	public static function newInstance(): self {
		$instance = new self( 'sqlite::memory:' );

		$instance->factory->getPdo()->exec( file_get_contents( __DIR__ .'/../db/schema.sql' ) );

		return $instance;
	}

	private $factory;

	private function __construct( string $dbDsn ) {
		$this->factory = new TopLevelFactory( $dbDsn );
	}

	public function getFactory(): TopLevelFactory {
		return $this->factory;
	}

}
