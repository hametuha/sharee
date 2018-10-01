<?php

namespace Hametuha\Sharee\Master;




use Hametuha\Sharee\Pattern\MetaMaster;

/**
 * Get address object.
 *
 * @package Hametuha\Sharee\Models
 */
class Address extends MetaMaster {

	protected $prefix = '_billing_';

	/**
	 * Should return array for keys.
	 *
	 * @return array
	 */
	protected function get_setting() {
		return [
			'name'     => [
				'label'    => __( 'Business Name', 'sharee' ),
				'required' => true,
			],
			'number'   => [
				'label'    => __( 'Tax Number', 'sharee' ),
				'callback' => function( $var ) {
					return is_numeric( $var ) || empty( $var );
				},
			],
			'address'  => [
				'label'   => __( 'Address', 'sharee' ),
				'default' => '',
			],
			'address2' => [
				'label'   => __( 'Building', 'sharee' ),
				'default' => '',
			],
			'zip'      => [
				'label'    => __( 'Zip Code', 'sharee' ),
				'callback' => function( $var ) {
					return preg_match( '#^[0-9\-]+$#u', $var ) ?: new \WP_Error( 'malformat', sprintf( __( 'Invalid format: %s Only number and hyphen is available.', 'sharee' ), $var ) );
				},
			],
			'country'  => [
				'label'   => __( 'Country', 'sharee' ),
			],
			'tel'      => [
				'label' => __( 'Tel', 'sharee' ),
				'type'  => 'tel',
				'callback' => function( $var ) {
					return preg_match( '#^[0-9\- +]*$#u', $var ) ?: new \WP_Error( 'malformat', __( 'Invalid format. Only number, +, and hyphen(-) is available.', 'sharee' ) );
				},
			],
		];
	}

	/**
	 * Check if object if valid.
	 *
	 * @return bool
	 */
	public function validate() {
		$valid = true;
		foreach ( [ 'zip', 'address', 'name', 'number' ] as $key ) {
			if ( ! $this->data[ $key ] ) {
				$valid = false;
				break;
			}
		}
		return (bool) apply_filters( 'sharee_validate_address', $valid, $this->object, $this->data );
	}

}
