<?php

namespace WMDE\Fundraising\Deployment\Tests;

use WMDE\Fundraising\Deployment\PDOReleaseState;

class DeploymentQueueTest extends \PHPUnit_Framework_TestCase {
	
	const BRANCH_NAME = 'testBranch';
	const FIRST_RELEASE = 'deadbeef';
	const SECOND_RELEASE = 'd0gf00d';
	const THIRD_RELEASE = 'badcafe';

	/**
	 * @var \PDO
	 */
	private $db;

	protected function setUp() {
		$this->db = new \PDO( 'sqlite::memory:' );
		$this->db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->setupDBTable();
	}

	private function setupDBTable() {
		$this->db->exec( file_get_contents( __DIR__ . '/../db/schema.sql' ) );
	}

	public function testGivenNewRelaseState_getLatestReleaseReturnsFalse() {
		$releaseState = new PDOReleaseState( $this->db );
		$this->assertSame( [], $releaseState->getLatestReleases() );
	}

	public function testWhenAddingARelease_itIsUndeployed() {
		$releaseState = new PDOReleaseState( $this->db );
		$releaseState->addRelease( self::BRANCH_NAME, self::FIRST_RELEASE );
		$this->assertTrue( $releaseState->hasUndeployedReleases( self::BRANCH_NAME ) );
	}

	public function testWhenAddingARelease_deploymentInProcessReturnsFalse() {
		$releaseState = new PDOReleaseState( $this->db );
		$releaseState->addRelease( self::BRANCH_NAME, self::FIRST_RELEASE );
		$this->assertFalse( $releaseState->deploymentInProcess( self::BRANCH_NAME ) );
	}

	public function testGivenSeveralReleases_getLatestReleasesReturnsTheMostRecent() {
		$releaseState = new PDOReleaseState( $this->db );
		$releaseState->addRelease( self::BRANCH_NAME, self::FIRST_RELEASE, '2016-01-02 09:00:00' );
		$releaseState->addRelease( self::BRANCH_NAME, self::SECOND_RELEASE, '2016-01-02 10:00:00' );
		$releaseState->addRelease( self::BRANCH_NAME, self::THIRD_RELEASE, '2016-01-02 11:00:00' );

		$expectedReleases = [ self::BRANCH_NAME => self::THIRD_RELEASE ];
		$this->assertEquals( $expectedReleases, $releaseState->getLatestReleases() );
	}

	public function testWhenDeployingARelease_deploymentInProcessReturnsTrue() {
		$releaseState = new PDOReleaseState( $this->db );
		$releaseState->addRelease( self::BRANCH_NAME, self::FIRST_RELEASE );
		$releaseState->markDeploymentAsStarted( self::FIRST_RELEASE );
		$this->assertTrue( $releaseState->deploymentInProcess( self::BRANCH_NAME ) );
	}

	public function testWhenAddingAndDeployingARelease_itIsNotUndeployed() {
		$releaseState = new PDOReleaseState( $this->db );
		$releaseState->addRelease( self::BRANCH_NAME, self::FIRST_RELEASE );
		$releaseState->markDeploymentAsStarted( self::FIRST_RELEASE );
		$this->assertFalse( $releaseState->hasUndeployedReleases( self::BRANCH_NAME ) );
	}

	public function testWhenEndingADeployment_deploymentInProcessReturnsFalse() {
		$releaseState = new PDOReleaseState( $this->db );
		$releaseState->addRelease( self::BRANCH_NAME, self::FIRST_RELEASE );
		$releaseState->markDeploymentAsStarted( self::FIRST_RELEASE );
		$releaseState->markDeploymentAsFinished( self::FIRST_RELEASE );
		$this->assertFalse( $releaseState->deploymentInProcess( self::BRANCH_NAME ) );
		$this->assertFalse( $releaseState->hasUndeployedReleases( self::BRANCH_NAME ) );
	}

	public function testWhenEndingADeployment_previousUndeployedReleasesAreMarkedAsEnded() {
		$releaseState = new PDOReleaseState( $this->db );
		$releaseState->addRelease( self::BRANCH_NAME, self::FIRST_RELEASE, '2016-01-02 09:00:00' );
		$releaseState->addRelease( self::BRANCH_NAME, self::SECOND_RELEASE, '2016-01-02 10:00:00' );
		$releaseState->addRelease( self::BRANCH_NAME, self::THIRD_RELEASE, '2016-01-02 11:00:00' );
		$releaseState->markDeploymentAsStarted( self::THIRD_RELEASE );
		$releaseState->markDeploymentAsFinished( self::THIRD_RELEASE );

		$this->assertFalse( $releaseState->deploymentInProcess( self::BRANCH_NAME ) );
		$this->assertFalse( $releaseState->hasUndeployedReleases( self::BRANCH_NAME ) );
	}

	public function testWhenEndingADeployment_subsequentReleasesAreNotTouched() {
		$releaseState = new PDOReleaseState( $this->db );
		$releaseState->addRelease( self::BRANCH_NAME, self::FIRST_RELEASE, '2016-01-02 09:00:00' );
		$releaseState->addRelease( self::BRANCH_NAME, self::SECOND_RELEASE, '2016-01-02 10:00:00' );
		$releaseState->addRelease( self::BRANCH_NAME, self::THIRD_RELEASE, '2016-01-02 11:00:00' );
		$releaseState->markDeploymentAsStarted( self::SECOND_RELEASE );
		$releaseState->markDeploymentAsFinished( self::SECOND_RELEASE );

		$this->assertFalse( $releaseState->deploymentInProcess( self::BRANCH_NAME ) );
		$this->assertTrue( $releaseState->hasUndeployedReleases( self::BRANCH_NAME ) );
		$expectedReleases = [ self::BRANCH_NAME => self::THIRD_RELEASE ];
		$this->assertEquals( $expectedReleases, $releaseState->getLatestReleases() );
	}

}