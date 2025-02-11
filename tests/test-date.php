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
class DateTest extends UnitTestCase {

	/**
	 * Test date format.
	 */
	function test_format_date() {
		$instance = \Hametuha\Sharee\Hooks\AdditionalUserQuery::get_instance();
		$this->assertEquals( '2018-01-01 00:00:00', $instance->ensure_datetime( '2018-01' ), 'Year month valid.' );
		$this->assertEquals( '2018-01-31 23:59:59', $instance->ensure_datetime( '2018-01', 'Y-m-t', '23:59:59' ), 'Year month valid.' );
		$this->assertEquals( '2018-01-01 00:00:00', $instance->ensure_datetime( '2018-01-01' ), 'Date valid.' );
		$this->assertEquals( '2022-02-22 22:22:22', $instance->ensure_datetime( '2022-02-22 22:22:22' ), 'DateTime valid.' );
		$this->assertEquals( true, (bool) preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $instance->ensure_datetime( 'now' ) ), 'Now valid.' );
		$this->assertEquals( '', $instance->ensure_datetime( 'invalid' ), 'Invalid date.' );
	}

}

