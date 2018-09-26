<?php
/**
 * Class SampleTest
 *
 * @package wpametu
 */

/**
 * Sample test case.
 */
class FunctionsTest extends WP_UnitTestCase {

	/**
	 * Create table
	 */
	public static function setUpBeforeClass() {
		\Hametuha\Sharee\Models\RevenueModel::get_instance()->create_table();
		\Hametuha\Sharee\Models\RevenueMetaModel::get_instance()->create_table();
	}


	/**
	 * Test for range function.
	 */
	function test_range() {
		// Check range
		$model = \Hametuha\Sharee\Models\RevenueModel::get_instance();
		list( $start, $end ) = $model->get_month_range( 2018, 1 );
		$this->assertEquals( '2018-01-01 00:00:00', $start );
		$this->assertEquals( '2018-01-31 23:59:59', $end );
		// Check leap year
		list( $start, $end ) = $model->get_month_range( 2004, 2 );
		$this->assertEquals( '2004-02-01 00:00:00', $start );
		$this->assertEquals( '2004-02-29 23:59:59', $end );
		// Check exceptional leap year.
		list( $start, $end ) = $model->get_month_range( 1900, 2 );
		$this->assertEquals( '1900-02-01 00:00:00', $start );
		$this->assertEquals( '1900-02-28 23:59:59', $end );
	}

	/**
	 * Test for basic insertion
	 */
	function test_crud() {
		$model = \Hametuha\Sharee\Models\RevenueModel::get_instance();
		// Save revenue.
		$revenue_id = $model->add_revenue( 'task', 1, 1000 );
		var_dump( $revenue_id );
		$this->assertTrue( is_numeric( $revenue_id ) );
		// Get revenue
		$revenue = $model->get( $revenue_id );
		$this->assertObjectHasAttribute( 'revenue_id', $revenue );
		// Update revenue;
		$model->update_status( $revenue_id, 1 );
		$revenue = $model->get( $revenue_id );
		$this->assertEquals( '1', $revenue->status );
		// Check log.
		$log = $model->revenue_meta->get_logs( $revenue_id );
		$this->assertEquals( 1, count( $log ) );
		// Delete revenue.
		$model->delete( [
			'revenue_id' => $revenue_id,
		] );
		$revenue = $model->get( $revenue_id );
		$this->assertNull( $revenue );
	}
}

