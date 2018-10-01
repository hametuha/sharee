<?php

namespace Hametuha\Sharee\Pattern;


use Hametuha\Pattern\Master\RestResultObject;

/**
 * Class MetaMaster
 *
 * @package Hametuha\Sharee\Pattern
 */
abstract class MetaMaster implements RestResultObject {

	protected $type = 'user';

	protected $prefix = '';

	/**
	 *
	 *
	 * @var null|\WP_Post|\WP_User
	 */
	protected $object = null;

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * Constructor
	 *
	 * @param $id
	 */
	public function __construct( $id ) {
		switch ( $this->type ) {
			case 'user':
				$this->object = new \WP_User( $id );
				break;
			case 'post':
				$this->object = get_post( $id );
				break;
		}
		if ( $this->object ) {
			$this->set_meta();
		}
	}

	/**
	 * Update meta
	 */
	protected function set_meta(){
		$data = [];
		foreach ( $this->get_setting() as $key => $label ) {
			$data[ $key ] = $this->get( $key );
		}
		$this->data = $data;
	}

	/**
	 * Delete meta fields
	 *
	 * @param string $key
	 * @return bool
	 */
	protected function delete( $key ) {
		$deleted = (bool) call_user_func_array( $this->call( 'delete' ), [ $this->object->ID, $this->get_meta_key_name( $key ) ] );
		$this->data[ $key ] = '';
		return $deleted;
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return int
	 */
	public function update( $key, $value ) {
		$updated = (bool) call_user_func_array( $this->call( 'update' ), [ $this->object->ID, $this->get_meta_key_name( $key ), $value ] );
		$this->data[ $key ] = $value;
		return $updated;
	}

	/**
	 * Get meta data
	 *
	 * @param string $key
	 * @return string
	 */
	public function get( $key ) {
		return (string) call_user_func_array( $this->call( 'get' ), [ $this->object->ID, $this->get_meta_key_name( $key ), true ] );
	}

	/**
	 * Returns function name
	 *
	 * @param string $method
	 * @return string
	 */
	public function call( $method ) {
		return "{$method}_{$this->type}_meta";
	}

	/**
	 * Should return array for keys.
	 *
	 * @return array
	 */
	abstract protected function get_setting();

	/**
	 * Returns REST ready resulting array.
	 *
	 * @return array
	 */
	public function to_array() {
		return $this->data;
	}

	/**
	 * Get data
	 *
	 * @param string $key
	 * @return string
	 */
	public function get_value( $key ) {
		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : '';
	}


	/**
	 * Get prefix name.
	 *
	 * @param string $key
	 * @return string
	 */
	protected function get_meta_key_name( $key ) {
		return $this->prefix . $key;
	}

	/**
	 * Get setting array
	 *
	 * @return array
	 */
	public static function settings() {
		$self = new static( 0 );
		return $self->get_setting();
	}

}
