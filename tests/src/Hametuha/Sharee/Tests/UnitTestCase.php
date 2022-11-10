<?php

namespace Hametuha\Sharee\Tests;

use Hametuha\Sharee\Models\RevenueMetaModel;
use Hametuha\Sharee\Models\RevenueModel;

/**
 * Utility class for UnitTest
 *
 * @package sharee
 * @property-read RevenueMetaModel $revenue_meta Revenue meta model.
 * @property-read RevenueModel     $revenue      Revenue model.
 */
abstract class UnitTestCase extends \WP_UnitTestCase {

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'revenue':
				return RevenueModel::get_instance();
			case 'revenue_meta':
				return RevenueMetaModel::get_instance();
			default:
				return parent::__get( $name );
		}
	}
}
