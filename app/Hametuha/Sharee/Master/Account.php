<?php

namespace Hametuha\Sharee\Master;


use Hametuha\Sharee\Pattern\MetaMaster;

/**
 * Account master
 *
 * @package Hametuha\Sharee\Master
 */
class Account extends MetaMaster {

	protected $prefix = '_bank_';

	/**
	 * Get setting array
	 *
	 * @return array
	 */
	protected function get_setting() {
		return [
			'group' => [
				'label' => __( 'Bank Name', 'sharee' ),
				'required' => true,
			],
			'group_code' => [
				'label' => __( 'Bank Code', 'sharee' ),
				'required' => true,
				'callback' => function( $var ) {
					return is_numeric( $var );
				},
			],
			'branch' => [
				'label' => __( 'Branch Name', 'sharee' ),
				'required' => true,
			],
			'branch_code' => [
				'label' => __( 'Branch Code', 'sharee' ),
				'required' => true,
				'callback' => function( $var ) {
					return is_numeric( $var );
				},
			],
			'type' => [
				'label' => __( 'Account Type', 'sharee' ),
				'type' => 'number',
				'enum' => [ 1, 2, 4 ],
				'required' => true,
			],
			'number' => [
				'label' => __( 'Account Number', 'sharee' ),
				'required' => true,
				'callback' => function( $var ) {
					return is_numeric( $var );
				},
			],
			'name' => [
				'label' => __( 'Account Name', 'sharee' ),
				'required' => true,
			],
		];
	}

	/**
	 * Return format
	 *
	 * @return string
	 */
	public function format_line() {
		$values = [];
		foreach ( [ 'group', 'branch', 'type', 'number', 'name' ] as $key ) {
			$value = $this->get_value( $key );
			switch ( $key ) {
				case 'type':
					switch ( $value ) {
						case 2:
							$values[] = _x( 'Checking', 'bank_type', 'sharee' );
							break;
						case 4:
							$values[] = _x( 'Deposite', 'bank_type', 'sharee' );
							break;
						default:
							$values[] = _x( 'Saving', 'bank_type', 'sharee' );
							break;
					}
					break;
				default:
					$values[] = $value;
					break;
			}
		}
		return implode( ' ', $values );
	}


	/**
	 * Check if object if valid.
	 *
	 * @return bool
	 */
	public function validate() {
		$valid = true;
		foreach ( [ 'group', 'branch', 'name' ] as $key ) {
			if ( ! $this->data[ $key ] ) {
				$valid = false;
				break;
			}
		}
		foreach ( [ 'number', 'group_code', 'branch_code', 'type' ] as $key ) {
			if ( ! preg_match( '#^\d+$#u', $this->data[ $key ] ) ) {
				$valid = false;
				break;
			}
		}
		return (bool) apply_filters( 'sharee_validate_account', $valid, $this->object, $this->data );
	}
}