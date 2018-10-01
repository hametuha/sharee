<?php

namespace Hametuha\Sharee\Utilities;

use Hametuha\Sharee\Models\RevenueModel;

/**
 * Table helper
 *
 * @package sharee
 * @method int get_pagenume()
 */
trait TableHelper {

	/**
	 * Get current properties.
	 *
	 * @return array An array of [ $status, $year, $monthnum, $type, $page_num ]
	 */
	public function get_current_properties() {
		$status = filter_input( INPUT_GET, 'status' ) ?: 'all';
		if ( 'all' === $status ) {
			$status = '';
		}
		$type = filter_input( INPUT_GET, 'type' ) ?: 'all';
		if ( 'all' === $type ) {
			$type = '';
		}
		$year  = filter_input( INPUT_GET, 'year' ) ?: date_i18n( 'Y' );
		if ( ! is_numeric( $year ) ) {
		    $year = 0;
        }
		$month = filter_input( INPUT_GET, 'monthnum' ) ?: date_i18n( 'n' );
		if ( ! is_numeric( $month ) ) {
		    $month = 0;
        }
		return [
			$status,
            $year,
            $month,
			$type,
			max( 1, $this->get_pagenum() ),
		];
	}

	/**
	 * Display filter input element
	 */
	protected function filter_inputs() {
		$model = RevenueModel::get_instance();
		list( $status, $year, $month, $type, $page_num ) = $this->get_current_properties();
		?>
		<select name="year">
			<option value="all"<?php selected( 'all', $year ) ?>><?php esc_html_e( 'Every Year', 'sharee' ) ?></option>
			<?php foreach ( $model->available_years() as $i ) : ?>
				<option value="<?= $i ?>"<?php selected( $i == $year ) ?>><?= sprintf( _x( '%04d', 'year', 'sharee' ), $i ) ?></option>
			<?php endforeach; ?>
		</select>
		<select name="monthnum">
			<option value="all"<?php selected( 'all', $month ) ?>><?php esc_html_e( 'Every Months', 'sharee' ) ?></option>
			<?php for ( $i = 1; $i <= 12; $i ++ ) : ?>
				<option value="<?= $i ?>"<?php selected( $i == $month ) ?>>
					<?= mysql2date( 'M', sprintf( '%04d-%02d-01', date_i18n( 'Y' ), $i ) ) ?>
				</option>
			<?php endfor; ?>
		</select>
		<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'sharee' ) ?>" />
		<?php
	}

}
