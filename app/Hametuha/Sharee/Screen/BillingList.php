<?php

namespace Hametuha\Sharee\Screen;


use Hametuha\Pattern\TableScreen;
use Hametuha\Sharee;
use Hametuha\Sharee\Models\RevenueModel;
use Hametuha\Sharee\Table\BillingListTable;

/**
 * Display billing list
 * @package sharee
 * @property-read BillingListTable $table
 */
class BillingList extends TableScreen {

	protected $slug = 'user-billing';

	protected $parent = 'users.php';

	protected $table_class = BillingListTable::class;

	protected $has_search = false;

	/**
	 * Get page title.
	 *
	 * @return string
	 */
	protected function get_title() {
		return __( 'User Billing', 'sharee' );
	}

	/**
	 * Register page
	 */
	public function admin_init() {
		add_action( 'wp_ajax_user_billing', [ $this, 'ajax_handler' ] );
		add_action( 'wp_ajax_user_billing_csv', [ $this, 'csv_handler' ] );
	}

	/**
	 * Override this function to enqueue assets.
	 *
	 * @param string $page
	 */
	public function admin_enqueue_script( $page ) {
		if ( 'users_page_user-billing' !== $page ) {
			return;
		}
		wp_enqueue_script( 'sharee-billing-list-helper' );
		wp_localize_script(
			'sharee-billing-list-helper',
			'ShareeBilling',
			[
				'endpoint'     => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'user-billing' ),
				'defaultError' => __( 'Something is wrong on server. Please try again later.', 'sharee' ),
				// translators: %s is date format.
				'transferDate' => sprintf( __( 'Please specify transfer date in 4 digits: e.g. %s' ), date_i18n( 'md' ) ),
			]
		);
	}

	/**
	 * Ajax Handler
	 */
	public function ajax_handler() {
		try {
			if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce' ), 'user-billing' ) ) {
				throw new \Exception( __( 'Invalid Access', 'sharee' ), 401 );
			}
			list( $year, $month ) = array_map(
				function ( $key ) {
					$var = filter_input( INPUT_POST, $key );
					return is_numeric( $var ) ? (int) $var : 0;
				},
				[ 'year', 'month' ]
			);
			$model                = RevenueModel::get_instance();
			$types                = $model->type_to_be_billed();
			$done                 = $model->fix_billing(
				filter_input(
					INPUT_POST,
					'user_ids',
					FILTER_DEFAULT,
					[
						'flags' => FILTER_REQUIRE_ARRAY,
					]
				),
				$types,
				$year,
				$month
			);
			if ( ! $done ) {
				throw new \Exception( __( 'No billing found.', 'sharee' ), 404 );
			}
			wp_send_json(
				[
					'success' => true,
					// translators: %s indicates amount of records, %d is number of records.
					'message' => sprintf( __( '%s fixed. Reload window.', 'sharee' ), sprintf( _n( '%d record is', '%d records are', $done, 'sharee' ), $done ) ),
				]
			);
		} catch ( \Exception $e ) {
			wp_send_json_error(
				[
					'message' => $e->getMessage(),
				],
				$e->getCode()
			);
		}
	}

	/**
	 * CSV Handler
	 */
	public function csv_handler() {
		try {
			if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce' ), 'user-billing' ) ) {
				throw new \Exception( __( 'Invalid Access', 'sharee' ), 401 );
			}
			list( $year, $month ) = array_map(
				function ( $key ) {
					$var = filter_input( INPUT_POST, $key );
					return is_numeric( $var ) ? (int) $var : 0;
				},
				[ 'year', 'month' ]
			);
			if ( ! preg_match( '#^\d{6}$#u', $year . $month ) ) {
				// translators: %1$s is year, %2$s is month.
				throw new \Exception( sprintf( __( 'Please specify year and month. %1$s-%2$s', 'sharee' ), $year, $month ), 400 );
			}
			$model = RevenueModel::get_instance();
			$types = $model->type_to_be_billed();
			$list  = $model->get_billing_list(
				$year,
				$month,
				filter_input(
					INPUT_POST,
					'user_ids',
					FILTER_DEFAULT,
					[
						'flags' => FILTER_REQUIRE_ARRAY,
					]
				)
			);
			if ( ! $list ) {
				throw new \Exception( __( 'No billing found.', 'sharee' ), 404 );
			}
			$date = filter_input( INPUT_POST, 'date' );
			if ( ! preg_match( '#\d{4}#u', $date ) ) {
				// translators: %s is digit example.
				throw new \Exception( sprintf( __( 'Please specify transfer date in 4 digits: e.g. %s', 'sharee' ), date_i18n( 'md' ) ), 404 );
			}
			header( 'Content-Type: application/octet-stream' );
			header( sprintf( 'Content-Disposition: attachment; filename=billing-%s.csv', date_i18n( 'YmdHis' ) ) );
			header( 'Content-Transfer-Encoding: binary' );
			$f = fopen( 'php://output', 'w' );
			foreach ( $list as $line ) {
				$account = new Sharee\Master\Account( $line->object_id );
				fputcsv(
					$f,
					[
						3, // Service.
						$date, // Date.
						$account->get_value( 'group_code' ), // Bank Number.
						$account->get_value( 'branch_code' ), // Branch Number.
						$account->get_value( 'type' ), // Account Type.
						$account->get_value( 'number' ), // Account Number.
						$account->get_value( 'name' ), // Name.
						ceil( $line->total ), // Total amount.
						$line->object_id, // User ID.
					]
				);
			}
			fclose( $f );
			exit;
		} catch ( \Exception $e ) {
			$message = esc_html( $e->getMessage() );
			echo <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
    </head>
    <body>
    <h1 id="message">{$message}</h1>
    <script>
    var message = document.getElementById('message').innerHTML;
    alert(message);
    </script>
    </body>
</html>

HTML;
			exit;
		}
	}


	/**
	 * Do something before table.
	 */
	protected function before_table() {
		parent::before_table();
		$model = RevenueModel::get_instance();
		list( $status, $year, $monthnum, $type, $page_num ) = $this->table->get_current_properties();
		$summary = $model->get_billing_summary( $year, $monthnum );
		if ( $this->table->summary ) :
			?>
			<table class="sharee-summary-table">
				<caption><?php esc_html_e( 'Summary of Current Criteria', 'sharee' ); ?></caption>
				<thead>
				<tr>
					<th><?php esc_html_e( 'Found Count', 'sharee' ); ?></th>
					<th><?php esc_html_e( 'Deducting', 'sharee' ); ?></th>
					<th><?php esc_html_e( 'Total Amount', 'sharee' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td class="number"><?php echo number_format_i18n( $this->table->summary->record_number ); ?></td>
					<td class="number"><?php echo $model->format( $this->table->summary->deducting ); ?></td>
					<td class="number"><?php echo $model->format( $this->table->summary->total ); ?></td>
				</tr>
				</tbody>
			</table>
			<?php
		endif;
	}

	/**
	 * Do something after table.
	 */
	protected function after_table() {
		parent::after_table();
		?>
		<div style="display: none">
			<form action="<?php echo admin_url( 'admin-ajax.php' ); ?>" target="sharee-csv-downloader" method="post">
				<input type="hidden" name="action" value="user_billing_csv">
				<?php wp_nonce_field( 'user-billing' ); ?>
				<input type="hidden" name="year" value="">
				<input type="hidden" name="month" value="">
				<input type="hidden" name="date" value="" />
				<p></p>
			</form>
			<iframe id="sahree-csv-downloader" name="sharee-csv-downloader"></iframe>
		</div>
		<?php
	}
}
