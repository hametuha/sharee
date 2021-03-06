<?php

namespace Hametuha\Sharee\Rest;



use Hametuha\Sharee\Master\Address;
use Hametuha\Sharee\Pattern\RestPattern;

/**
 * Representing User meta
 *
 * @package Hametuha\Sharee\Rest
 */
class AddressApi extends RestPattern {

	protected $route = 'address/(?P<user_id>\d+|me)/';

	/**
	 * Should return arguments.
	 *
	 * @param string $http_method
	 * @return array
	 */
	protected function get_args( $http_method ) {
		$args = $this->get_user_args();
		switch ( $http_method ) {
			case 'POST':
				foreach ( Address::settings() as $key => $data ) {
					$arg       = wp_parse_args(
						$data,
						[
							'label'    => '',
							'required' => false,
							'callback' => null,
							'type'     => 'text',
						]
					);
					$api_value = [
						'type'        => $arg['type'],
						'description' => isset( $data['description'] ) ? $data['description'] : $arg['label'],
						'required'    => $arg['required'],
					];
					if ( $arg['callback'] ) {
						$api_value['validate_callback'] = $arg['callback'];
					}
					if ( isset( $data['default'] ) ) {
						$api_value['default'] = $arg['default'];
					}
					$args[ $key ] = $api_value;
				}
				break;
		}
		return $args;
	}

	/**
	 * Get user's address information.
	 *
	 * @param \WP_REST_Request
	 * @return \WP_REST_Response
	 */
	public function handle_get( $request ) {
		$user_id = $this->get_user_id( $request );
		$address = new Address( $user_id );
		$success = $address->validate();
		$message = $success
			? __( 'Your billing address is valid.', 'sharee' )
			: __( 'Your billing address is invalid. Please fill required information.', 'sharee' );
		return new \WP_REST_Response(
			[
				'success' => $success,
				'message' => $message,
				'data'    => $address->to_array(),
			]
		);
	}

	/**
	 * Process handle request
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function handle_post( $request ) {
		$address = new Address( $this->get_user_id( $request ) );
		foreach ( Address::settings() as $key => $data ) {
			$address->update( $key, $request->get_param( $key ) );
		}
		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => __( 'Your billing information has been updated.', 'sharee' ),
				'data'    => $address->to_array(),
			]
		);
	}

	/**
	 * Get permission callback.
	 *
	 * @param \WP_REST_Request $request
	 * @return bool|\WP_Error
	 */
	public function permission_callback( $request ) {
		return $this->user_permission_callback( $request );
	}
}
