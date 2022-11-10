<?php
/**
 * Class SampleTest
 *
 * @package wpametu
 */

use Hametuha\Sharee\Tests\UnitTestCase;

/**
 * Sample test case.
 */
class FunctionsTest extends UnitTestCase {

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
		$revenue_id = $this->revenue->add_revenue( 'task', 1, 1000, [
			'deducting' => 100,
		] );
		if ( is_wp_error( $revenue_id ) ) {
			throw new \Exception( $revenue_id->get_error_message() );
		}
		$revenue_id_2 = $this->revenue->add_revenue( 'task', 1, 1000, [
			'deducting' => 0,
		] );
		$this->assertTrue( is_numeric( $revenue_id ) && 0 < $revenue_id, 'Revenue 1 is added.' );
		$this->assertTrue( is_numeric( $revenue_id_2 ) && 0 < $revenue_id_2, 'Revenue 2 is added.' );
		// Get revenue
		$revenue = $this->revenue->get( $revenue_id );
		$this->assertObjectHasAttribute( 'revenue_id', $revenue );
		// Update revenue;
		$updated = $this->revenue->fix_billing( [1] );
		$this->assertEquals( 2, $updated, 'All revenue is fixed.' );
		// Really updated?
		$revenue = $this->revenue->get( $revenue_id );
		$this->assertEquals( '1', $revenue->status );
		// Get fixed billings.
		$fixed_deducting      = $this->revenue->get_fixed_billing( date_i18n( 'Y' ), 0, [], false );
		$fixed_with_deducting = $this->revenue->get_fixed_billing( date_i18n( 'Y' ), 0, [], true );
		$this->assertEquals( 2000, (int) $fixed_deducting[0]->total, 'Extract all billing.' );
		$this->assertEquals( 1000, (int) $fixed_with_deducting[0]->total, 'Extract billing with deducting.' );
		// Check log.
		$this->revenue->update_status( $revenue_id, -1 );
		$log = $this->revenue->revenue_meta->get_logs( $revenue_id );
		$this->assertEquals( 1, count( $log ), 'Log found.' );
		// Delete revenue.
		$this->revenue->delete( [
			'revenue_id' => $revenue_id,
		] );
		$this->revenue->delete( [
			'revenue_id' => $revenue_id_2,
		] );
		$revenue = $this->revenue->get( $revenue_id );
		$this->assertNull( $revenue, 'Revenue is removed.' );
	}
}

