<?php

namespace Hametuha\Sharee;

use Hametuha\Sharee\Master\Address;

/**
 * Command interface for sharee.
 *
 * @package sharee
 */
class Command extends \WP_CLI_Command {

	/**
	 * Get billing address
	 *
	 * ## EXAMPLE
	 *
	 *   wp sharee address 3
	 *
	 * @param array $args
	 * @synopsis <user_id>
	 */
	public function address( $args ) {
		list( $user_id ) = $args;
		$address = new Address( $user_id );
		$data = $address->to_array();
		if ( ! $data ) {
			\WP_CLI::error( 'No data found.' );
		}
		$table = new \cli\Table();
		$table->setHeaders( [ 'Key', 'Value' ] );
		foreach ( $data as $key => $val ) {
			$table->addRow( [ $key, $val ] );
		}
		$table->display();
	}

}
