<?php

namespace WMDE\Fundraising\Deployment\Tests;

use WMDE\Fundraising\Deployment\PDOReleaseState;
use WMDE\Fundraising\Deployment\ReleaseStateWriter;

class PDOReleaseStateTest extends \PHPUnit_Framework_TestCase {
	
	const BRANCH_NAME = 'testBranch';
	const FIRST_RELEASE = 'deadbeef';
	const SECOND_RELEASE = 'd0gf00d';
	const THIRD_RELEASE = 'badcafe';

	/**
	 * @var \PDO
	 */
	private $db;

	/**
	 * @var ReleaseStateWriter
	 */
	private $releaseStateWriter;

	protected function setUp() {
		$this->db = new \PDO( 'sqlite::memory:' );
		$this->db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->releaseStateWriter = new ReleaseStateWriter( $this->db );

		$this->setupDBTable();
	}

	private function setupDBTable() {
		$this->db->exec( file_get_contents( __DIR__ . '/../db/schema.sql' ) );
	}

	public function testGivenNewRelaseState_itHasNoUndeployedReleases() {
		$releaseState = new PDOReleaseState( $this->db, self::BRANCH_NAME );
		$this->assertFalse( $releaseState->hasUndeployedReleases() );
	}

	public function testWhenAddingARelease_itIsUndeployed() {
		$releaseState = new PDOReleaseState( $this->db, self::BRANCH_NAME );
		$this->insertFirstRelease();

		$this->assertTrue( $releaseState->hasUndeployedReleases() );
	}

	public function testWhenAddingARelease_deploymentInProcessReturnsFalse() {
		$releaseState = new PDOReleaseState( $this->db, self::BRANCH_NAME );
		$this->insertFirstRelease();

		$this->assertFalse( $releaseState->deploymentInProcess() );
	}

	public function testGivenSeveralReleases_getLatestReleasesReturnsTheMostRecent() {
		$releaseState = new PDOReleaseState( $this->db, self::BRANCH_NAME );
		$this->insertThreeReleases();

		$this->assertEquals( self::THIRD_RELEASE, $releaseState->getLatestReleaseId() );
	}

	public function testWhenAReleaseIsMarkedForDeployment_deploymentInProcessReturnsTrue() {
		$releaseState = new PDOReleaseState( $this->db, self::BRANCH_NAME );
		$this->insertFirstRelease();

		$releaseState->markDeploymentAsStarted( self::FIRST_RELEASE );
		$this->assertTrue( $releaseState->deploymentInProcess() );
	}

	public function testWhenAddingAndDeployingARelease_itIsNotUndeployed() {
		$releaseState = new PDOReleaseState( $this->db, self::BRANCH_NAME );
		$this->insertFirstRelease();
		$releaseState->markDeploymentAsStarted( self::FIRST_RELEASE );

		$this->assertFalse( $releaseState->hasUndeployedReleases() );
	}

	public function testWhenEndingADeployment_deploymentInProcessReturnsFalse() {
		$releaseState = new PDOReleaseState( $this->db, self::BRANCH_NAME  );
		$this->insertFirstRelease();
		$releaseState->markDeploymentAsStarted( self::FIRST_RELEASE );
		$releaseState->markDeploymentAsFinished( self::FIRST_RELEASE );

		$this->assertFalse( $releaseState->deploymentInProcess() );
		$this->assertFalse( $releaseState->hasUndeployedReleases() );
	}

	public function testWhenEndingADeployment_previousUndeployedReleasesAreMarkedAsEnded() {
		$releaseState = new PDOReleaseState( $this->db, self::BRANCH_NAME  );
		$this->insertThreeReleases();
		$releaseState->markDeploymentAsStarted( self::THIRD_RELEASE );
		$releaseState->markDeploymentAsFinished( self::THIRD_RELEASE );

		$this->assertFalse( $releaseState->deploymentInProcess() );
		$this->assertFalse( $releaseState->hasUndeployedReleases() );
	}

	public function testWhenEndingADeployment_subsequentReleasesAreNotTouched() {
		$releaseState = new PDOReleaseState( $this->db, self::BRANCH_NAME  );
		$this->insertThreeReleases();
		$releaseState->markDeploymentAsStarted( self::SECOND_RELEASE );
		$releaseState->markDeploymentAsFinished( self::SECOND_RELEASE );

		$this->assertFalse( $releaseState->deploymentInProcess() );
		$this->assertTrue( $releaseState->hasUndeployedReleases() );
		$this->assertEquals( self::THIRD_RELEASE, $releaseState->getLatestReleaseId() );
	}

	public function testWhenMarkingADeploymentAsFailed_ItCanBeDeployedAgain() {
		$releaseState = new PDOReleaseState( $this->db, self::BRANCH_NAME  );
		$this->insertFirstRelease();
		$releaseState->markDeploymentAsStarted( self::FIRST_RELEASE );
		$releaseState->markDeploymentAsFailed( self::FIRST_RELEASE );

		$this->assertFalse( $releaseState->deploymentInProcess() );
		$this->assertTrue( $releaseState->hasUndeployedReleases() );
	}

	private function insertFirstRelease() {
		$this->releaseStateWriter->addRelease( self::BRANCH_NAME, self::FIRST_RELEASE );
	}

	private function insertThreeReleases() {
		$this->releaseStateWriter->addRelease( self::BRANCH_NAME, self::FIRST_RELEASE, '2016-01-02 09:00:00' );
		$this->releaseStateWriter->addRelease( self::BRANCH_NAME, self::SECOND_RELEASE, '2016-01-02 10:00:00' );
		$this->releaseStateWriter->addRelease( self::BRANCH_NAME, self::THIRD_RELEASE, '2016-01-02 11:00:00' );
	}

}
