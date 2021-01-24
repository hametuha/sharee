<?php
/**
 * Class SampleTest
 *
 * @package wpametu
 */

/**
 * Sample test case.
 */
class FunctionsTest extends \Hametuha\Sharee\Tests\UnitTestCase {

	/**
	 * Test for range function.
	 */
	function test_range() {
		// Check range
		list( $start, $end ) = $this->revenue->get_month_range( 2018, 1 );
		$this->assertEquals( '2018-01-01 00:00:00', $start );
		$this->assertEquals( '2018-01-31 23:59:59', $end );
		// Check leap year
		list( $start, $end ) = $this->revenue->get_month_range( 2004, 2 );
		$this->assertEquals( '2004-02-01 00:00:00', $start );
		$this->assertEquals( '2004-02-29 23:59:59', $end );
		// Check exceptional leap year.
		list( $start, $end ) = $this->revenue->get_month_range( 1900, 2 );
		$this->assertEquals( '1900-02-01 00:00:00', $start );
		$this->assertEquals( '1900-02-28 23:59:59', $end );
	}

	/**
	 * Test for basic insertion
	 */
	function test_crud() {
		// Save revenue.
		$revenue_id = $this->revenue->add_revenue( 'task', 1, 1000 );
		if ( is_wp_error( $revenue_id ) ) {
			throw new \Exception( $revenue_id->get_error_message() );
		}
		$this->assertTrue( is_numeric( $revenue_id ) );
		// Get revenue
		$revenue = $this->revenue->get( $revenue_id );
		$this->assertObjectHasAttribute( 'revenue_id', $revenue );
		// Update revenue;
		$this->revenue->update_status( $revenue_id, 1 );
		$revenue = $this->revenue->get( $revenue_id );
		$this->assertEquals( '1', $revenue->status );
		// Check log.
		$log = $this->revenue->revenue_meta->get_logs( $revenue_id );
		$this->assertEquals( 1, count( $log ) );
		// Delete revenue.
		$this->revenue->delete( [
			'revenue_id' => $revenue_id,
		] );
		$revenue = $this->revenue->get( $revenue_id );
		$this->assertNull( $revenue );
	}
}

