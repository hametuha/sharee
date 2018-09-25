<?php
namespace Hametuba\Sharee\Models;


use Hametuha\Pattern\Model;

/**
 * Class RevenueModel
 *
 * @package sharee
 */
class RevenueModel extends Model {

	protected $version = '0.8.0';

	protected $name    = 'revenues';

	protected $default_placeholder = [
		'sales_id' => '%d',
		'sales_type' => '%s',
		'object_id' => '%d',
		'price' => '%f',
		'unit' => '%f',
		'tax' => '%f',
		'deducting' => '%f',
		'total' => '%f',
		'status' => '%s',
		'description' => '%s',
		'created' => '%s',
		'fixed'   => '%s',
		'updated' => '%s',
	];

	/**
	 * Get label for types
	 *
	 * @return array
	 */
	public function get_labels() {
		return apply_filters( 'hametuha_share_labels', [] );
	}

	public function get_status() {
		return apply_filters(  );
	}


	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'label':
				return $this->get_labels();
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}


}
