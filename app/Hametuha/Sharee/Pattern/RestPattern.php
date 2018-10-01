<?php

namespace Hametuha\Sharee\Pattern;


use Hametuha\Pattern\RestApi;
use Hametuha\Sharee\Models\RevenueMetaModel;
use Hametuha\Sharee\Models\RevenueModel;

/**
 * REST API Patter
 *
 * @package sharee
 * @property-read RevenueModel     $revenue
 * @property-read RevenueMetaModel $revenue_meta
 */
abstract class RestPattern extends RestApi {

	protected $version = '1';

	protected $namespace = 'sharee';

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'revenues':
				return RevenueModel::get_instance();
				break;
			case 'revenue_meta':
				return RevenueMetaModel::get_instance();
				break;
			default:
				return null;
		}
	}

	/**
	 * Get user ID
	 *
	 * @param \WP_REST_Request $request
	 * @return int
	 */
	protected function get_user_id( $request ) {
		$user_id = $request->get_param( 'user_id' );
		if ( 'me' === $user_id ) {
			return get_current_user_id();
		} else {
			return (int) $user_id;
		}
	}

	/**
	 * Get arguments for user_id
	 *
	 * @return array
	 */
	protected function get_user_args() {
		return [
			'user_id' => [
				'type' => 'int|string',
				'required' => true,
				'validate_callback' => function( $var ) {
					return ( 'me' === $var ) || is_numeric( $var );
				},
				'description' => 'User ID or "me".',
			],
		];
	}

	/**
	 * Common request callback
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	protected function user_permission_callback( $request ) {
		$user_id = $request->get_param( 'user_id' );
		if ( 'me' === $user_id || get_current_user_id() === (int) $user_id ) {
			return current_user_can( 'read' );
		} else {
			switch ( $request->get_method() ) {
				case 'GET':
					return current_user_can( 'list_users' );
					break;
				default:
					return current_user_can( 'edit_users' );
					break;
			}
		}
	}
}
