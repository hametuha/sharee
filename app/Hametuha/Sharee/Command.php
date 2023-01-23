<?php

namespace Hametuha\Sharee;

use cli\Table;
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
		$address         = new Address( $user_id );
		$data            = $address->to_array();
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

	/**
	 * Remove tax numbers.
	 *
	 * @synopsis [--user_id=<user_id>] [--dry-run]
	 * @param array $args    Arguments.
	 * @param array $options Command options.
	 * @return void
	 */
	public function clean_tax_number( $args, $options ) {
		$user_id = $options['user_id'] ?? 0;
		if ( $user_id && ! is_numeric( $user_id ) ) {
			\WP_CLI::error( 'User id should be numeric: ' . $user_id );
		}
		$dry_run  = $options['dry-run'] ?? false;
		$query_args = [
			'count_total' => true,
			'meta_query'  => [
				[
					'key'     => '_billing_number',
					'compare' => 'EXISTS',
				],
				[
					'key'     => '_billing_number',
					'compare' => '!=',
					'value'   => '',
				],
			],
		];
		$user_query = new \WP_User_Query( $query_args );
		if ( $dry_run ) {
			$table = new Table();
			$table->setHeaders( [ 'ID', 'Display Name', 'TAX Number' ] );
			foreach ( $user_query->get_results() as $user ) {
				$table->addRow( [ $user->ID, $user->display_name, get_user_meta( $user->ID, '_billing_number', true ) ?: '---' ] );
			}
			$table->display();
			\WP_CLI::success( sprintf( '%d users found.', $user_query->get_total() ) );
			return;
		}
		$done = 0;
		foreach ( $user_query->get_results() as $user ) {
			delete_user_meta( $user->ID, '_billing_number' );
			$done++;
		}
		\WP_CLI::success( sprintf( 'Deleted %d users tax number.', $done ) );
	}
}
