<?php

namespace Hametuha\Sharee\Tests;

use Hametuha\Sharee\Models\RevenueModel;

/**
 * Stub command.
 */
class StubCommands extends \WP_CLI_Command {

	/**
	 * Register dummy records.
	 *
	 * @synopsis <limit>
	 * @param array $args Command arguments.
	 * @return void
	 */
	public function sales( $args ) {
		list( $limit ) = $args;
		\WP_CLI::line( sprintf( 'Creating %d dummy records...', $limit ) );
		// Get users.
		$users = new \WP_User_Query( [
			'count' => 10,
		] );
		$user_ids = array_map( function( \WP_User $user ) {
			return $user->ID;
		}, $users->get_results() );
		$model  = RevenueModel::get_instance();
		$labels = $model->get_labels();
		$faker  = \Faker\Factory::create();
		$types  = array_keys( $labels );
		$done   = 0;
		for ( $i = 0; $i < $limit; $i++ ) {
			$price    = random_int( 100, 50000 );
			$with_tax = 1 < random_int( 1, 4 );
			$type     = $types[ random_int( 0, count( $types ) - 1 ) ];
			$user_id  = $user_ids[ random_int( 0, count( $user_ids ) - 1 ) ];
			if ( $with_tax ) {
				$deducting = floor( $price * 0.1021 );
				$tax       = floor( $price * 0.1 );
				$price     = $price + $tax - $deducting;
			} else {
				$tax       = floor( $price * 0.1 );
				$deducting = 0;
				$price     = $price + $tax;
			}
			$description = sprintf( 'Pay for %s', $faker->name() );
			$status = random_int( 0, 2 ) - 1;
			$today   = new \DateTime( 'now', wp_timezone() );
			$today->sub( new \DateInterval( sprintf( 'P%dD', random_int( 1, 365*3 ) ) ) );
			$args = [
				'unit'        => random_int( 1, 5 ),
				'tax'         => $tax,
				'deducting'   => $deducting,
				'description' => $description,
				'status'      => $status,
				'created'     => $today->format( 'Y-m-d H:i:s' ),
			];
			if ( 1 === $status ) {
				$args['fixed'] = current_time( 'mysql', true );
			}
			$result = $model->add_revenue( $type, $user_id, $price, $args );
			if ( $result && ! is_wp_error( $result ) ) {
				echo '.';
				$done++;
			} else {
				echo 'x';
			}
		}
		\WP_CLI::line( '' );
		\WP_CLI::success( sprintf( 'Done! %d records inserted.', $done ) );
	}


}
