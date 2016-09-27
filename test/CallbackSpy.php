<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Deployment\Tests;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class CallbackSpy {

	private $calls = [];

	public function doCallback() {
		$this->calls[] = func_get_args();
	}

	public function getCallCount(): int {
		return count( $this->calls );
	}

	public function wasCalledWith( $args ): bool {
		return array_search( $args, $this->calls ) !== false;
	}

	public static function createCallable() {
		$self = new self;
		return [ $self, 'doCallback' ];
	}
}