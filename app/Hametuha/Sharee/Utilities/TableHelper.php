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
		$year = filter_input( INPUT_GET, 'year' ) ?: date_i18n( 'Y' );
		if ( ! is_numeric( $year ) ) {
			$year = 0;
		}
		$month = filter_input( INPUT_GET, 'monthnum' ) ?: date_i18n( 'n' );
		if ( ! is_numeric( $month ) ) {
			$month = 0;
		}
		$user = filter_input( INPUT_GET, 'user_id' ) ?: 0;
		return [
			$status,
			$year,
			$month,
			$type,
			max( 1, $this->get_pagenum() ),
			$user,
		];
	}

	/**
	 * Display filter input element
	 *
	 * @param bool $with_month If false, no month selector.
	 */
	protected function filter_inputs( $with_month = true ) {
		$model = RevenueModel::get_instance();
		list( $status, $year, $month, $type, $page_num ) = $this->get_current_properties();
		?>
		<select name="year">
			<option value="all"<?php selected( 'all', $year ); ?>><?php esc_html_e( 'Every Year', 'sharee' ); ?></option>
			<?php foreach ( $model->available_years() as $i ) : ?>
				<option value="<?php echo $i; ?>"<?php selected( $i === (int) $year ); ?>>
					<?php
					// phpcs:disable WordPress.WP.I18n.NoEmptyStrings
					// translators: %04d is year.
					echo sprintf( _x( '%04d', 'year', 'sharee' ), $i );
					// phpcs:enable WordPress.WP.I18n.NoEmptyStrings
					?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php if ( $with_month ) : ?>
		<select name="monthnum">
			<option value="all"<?php selected( 'all', $month ); ?>><?php esc_html_e( 'Every Months', 'sharee' ); ?></option>
			<?php for ( $i = 1; $i <= 12; $i ++ ) : ?>
				<option value="<?php echo $i; ?>"<?php selected( $i === (int) $month ); ?>>
					<?php echo mysql2date( 'M', sprintf( '%04d-%02d-01', date_i18n( 'Y' ), $i ) ); ?>
				</option>
			<?php endfor; ?>
		</select>
		<?php endif; ?>
		<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'sharee' ); ?>" />
		<?php
	}
}
