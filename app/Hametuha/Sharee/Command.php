<?php

namespace Hametuha\Sharee;

use Hametuha\Sharee\Master\Address;
use Hametuha\Sharee\Models\RevenueModel;

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

	/**
	 * Preview revenue search result
	 *
	 * @synopsis [--year=<year>] [--month=<month>] [--page=<page>] [--object_id=<object_id>] [--per_page=<per_page>] [--status=<status>] [--start=<start>] [--end=<end>] [--type=<type>]
	 * @param array $args
	 * @param array $assoc
	 */
	public function revenue( $args, $assoc = [] ) {
		global $wpdb;
		$model = RevenueModel::get_instance();
		$assoc = wp_parse_args( $assoc, $model->default_search_args() );
		foreach ( [ 'object_id', 'type' ] as $key ) {
			if ( ! $assoc[ $key ] ) {
				continue;
			}
			if ( false !== strpos( $assoc[ $key ], ',' ) ) {
				$assoc[ $key ] = explode( ',', $assoc[ $key ] );
			}
		}
		$revenues = $model->search( $assoc );
		$query    = implode( "\n", array_map( 'trim', explode( "\n", (string) $wpdb->last_query ) ) );
		if ( ! $revenues ) {
			\WP_CLI::line( $query );
			\WP_CLI::error( 'No result found.' );
		}
		$table = new \cli\Table();
		$table->setHeaders( array_keys( get_object_vars( $revenues[0] ) ) );
		foreach ( $revenues as $revenue ) {
			$row = [];
			foreach ( get_object_vars( $revenue ) as $var => $value ) {
				$row[] = $value;
			}
			$table->addRow( $row );
		}
		$table->display();
		\WP_CLI::line( '' );
		\WP_CLI::line( $query );
		\WP_CLI::line( '' );
		\WP_CLI::success( 'Done!' );
	}

}
