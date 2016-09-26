<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Deployment\Tests;

use WMDE\Fundraising\Deployment\PayloadHandlers\PayloadHandler;
use WMDE\Fundraising\Deployment\PayloadHandlerDispatcher;

class PayloadHandlerDispatcherTest extends \PHPUnit_Framework_TestCase {

	public function testDispatchCallsEveryHandlerWithPayload() {
		$payload = (object) [ 'ref' => 'refs/heads/master' ];
		$firstHandler = $this->createMock( PayloadHandler::class );
		$secondHandler = $this->createMock( PayloadHandler::class );
		$firstHandler->expects( $this->once() )
			->method( 'handlePayload' )
			->with( $payload );
		$secondHandler->expects( $this->once() )
			->method( 'handlePayload' )
			->with( $payload );

		$dispatcher = new PayloadHandlerDispatcher( $firstHandler, $secondHandler );
		$dispatcher->dispatch( $payload );
	}
}
