<?php

namespace Hametuha\Sharee\Hooks;


use Hametuha\Hashboard;
use Hametuha\SingletonPattern\Singleton;
use Hametuha\Sharee;
use Hametuha\Sharee\Master\Account;
use Hametuha\Sharee\Master\Address;

/**
 * Add Hashboard screen.
 *
 * @package Hametuha\Sharee\Hooks
 */
class HbAccountScreen extends Singleton {

	/**
	 * Do something in constructor.
	 */
	protected function init() {
		add_filter( 'hashboard_screen_children', [ $this, 'add_screen' ], 10, 2 );
		add_filter( 'hashboard_page_description', [ $this, 'billing_description' ], 10, 3 );
		add_action( 'hashboard_before_fields_rendered', [ $this, 'render_status_fields' ], 10, 3 );
		add_filter( 'hashboard_field_groups', [ $this, 'field_groups' ], 10, 4 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 10, 2 );
	}

	/**
	 * Add Screen child
	 *
	 * @param array  $children
	 * @param string $slug
	 * @return array
	 */
	public function add_screen( $children, $slug ) {
		if ( 'account' === $slug ) {
			$children['billing'] = __( 'Billing', 'sharee' );
		}
		return $children;
	}

	/**
	 * Change description.
	 *
	 * @param $desc
	 * @param \Hametuha\Hashboard\Pattern\Screen $screen
	 * @param string $page
	 * @return string
	 */
	public function billing_description( $desc, $screen, $page ) {
		if ( 'billing' === $page && 'account' === $screen->slug() ) {
			$desc = __( 'This is your billing information. To get rewarded, please fill up all forms.', 'sharee' );
		}
		return $desc;
	}

	/**
	 * Enqueue scripts
	 *
	 */
	public function enqueue_scripts( $screen = null, $child = '' ):void {
		if ( ! Hashboard::is_page( 'account' ) ) {
			return;
		}

		$api_key  = defined( 'SHAREE_BANK_API_KEY' ) ? SHAREE_BANK_API_KEY : '';
		$yolp_key = defined( 'YOLP_API_KEY' ) ? YOLP_API_KEY : '';

		$base_url = Sharee::get_instance()->root_url;
		wp_register_style( 'select2', $base_url . '/assets/vendor/select2.min.css', [], '4.1.0' );
		wp_enqueue_style( 'select2-bootstrap-5-theme', $base_url . '/assets/vendor/select2-bootstrap-5-theme.min.css', [ 'select2' ], '1.3.0' );
		wp_register_script( 'select2', $base_url . '/assets/vendor/select2.min.js', [ 'jquery' ], '4.1.0', true );
		wp_enqueue_script( 'sharee-bank-helper' );
		wp_localize_script(
			'sharee-bank-helper',
			'BankHelper',
			[
				'apiKey'       => apply_filters( 'sharee_bank_api_key', $api_key ),
				'yolpKey'      => apply_filters( 'sharee_yolp_api_key', $yolp_key ),
				'yolpError'    => __( 'Sorry, but no result found.', 'sharee' ),
				'noBranchCode' => __( 'Bank is not specified.', 'sharee' ),
			]
		);
	}

	/**
	 * Display status field before billing info.
	 *
	 * @param string $slug
	 * @param string $page
	 * @param string $field_name
	 */
	public function render_status_fields( $slug, $page, $field_name ) {
		if ( 'account' !== $slug || 'billing' !== $page ) {
			return;
		}
		switch ( $field_name ) {
			case 'billing':
				$endpoint = rest_url( 'sharee/v1/bank/me' );
				break;
			case 'address':
				$endpoint = rest_url( 'sharee/v1/address/me' );
				break;
		}
		?>
		<div class="row">
			<div class="col s12">
				<div class="hb-status-display" data-endpoint="<?php echo esc_attr( $endpoint ); ?>">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Add field groups
	 *
	 * @param array    $args
	 * @param \WP_User $user
	 * @param string   $group
	 * @param string   $page
	 * @return array
	 */
	public function field_groups( $args, $user, $group, $page ) {
		if ( 'account' !== $group || 'billing' !== $page ) {
			return $args;
		}
		//
		// Bank Account
		//
		$billing_fields = [
			'label'       => __( 'Bank Account', 'sharee' ),
			'description' => apply_filters( 'sharee_bank_account_desc', __( 'Bank account is required to be rewarded. Only available for Japan.', 'sharee' ) ),
			'submit'      => __( 'Save', 'sharee' ),
			'action'      => rest_url( 'sharee/v1/bank/me' ),
			'method'      => 'POST',
			'fields'      => [],
		];
		$account        = new Account( $user->ID );
		foreach ( Account::settings() as $key => $data ) {
			$label       = $data['label'];
			$placeholder = '';
			$options     = [];
			$description = '';
			$row         = '';
			$type        = 'text';
			$col         = 1;
			$optional    = false;
			$value       = $account->get_value( $key );
			$default     = '';
			switch ( $key ) {
				case 'group':
					$col  = 1.5;
					$row  = 'open';
					$type = 'select';
					if ( $value ) {
						$options = [
							$value => $value,
						];
					}
					$description = __( 'Type and search your bank. Bank code will be automatically input if you found your bank.', 'sharee' );
					break;
				case 'group_code':
					$col  = 3;
					$row  = 'close';
					$type = 'number';
					break;
				case 'branch':
					$col  = 1.5;
					$row  = 'open';
					$type = 'select';
					if ( $value ) {
						$options = [
							$value => $value,
						];
					}
					$description = __( 'Once you select a bank, you can search it\'s branches.', 'sharee' );
					break;
				case 'branch_code':
					$col  = 3;
					$row  = 'close';
					$type = 'number';
					break;
				case 'type':
					$type    = 'select';
					$row     = 'open';
					$col     = 3;
					$options = [
						'1' => _x( 'Saving', 'bank_type', 'sharee' ),
						'2' => _x( 'Checking', 'bank_type', 'sharee' ),
						'4' => _x( 'Deposite', 'bank_type', 'sharee' ),
					];
					$default = '1';
					$row     = 'open';
					break;
				case 'number':
					$label = '口座番号';
					$col   = 1.5;
					$row   = 'close';
					$type  = 'number';
					break;
				case 'name':
					$label       = __( 'Account Name', 'sharee' );
					$description = __( 'Please write your name in Katakana.', 'sharee' );
					break;
			}
			$field = [
				'name'        => $key,
				'type'        => $type,
				'label'       => $label,
				'value'       => $value,
				'placeholder' => $placeholder,
				'group'       => $row,
				'col'         => $col,
				'description' => $description,
			];
			if ( $options ) {
				$field['options'] = $options;
			}
			if ( $default ) {
				$field['default'] = $default;
			}
			$billing_fields['fields'][ '_bank_' . $key ] = $field;
		}

		//
		// Billing Information
		//
		$address_fields = [
			'label'       => __( 'Billing Information', 'sharee' ),
			'description' => apply_filters( 'sharee_billing_info_desc', __( 'This information is required to send reward to you.', 'sharee' ) ),
			'submit'      => __( 'Save', 'sharee' ),
			'action'      => rest_url( '/sharee/v1/address/me' ),
			'method'      => 'POST',
			'fields'      => [],
		];
		$address        = new Address( $user->ID );
		foreach ( Address::settings() as $key => $data ) {
			$label       = $data['label'];
			$placeholder = '';
			$options     = [];
			$row         = '';
			$type        = 'text';
			$col         = 1;
			$optional    = false;
			$description = '';
			switch ( $key ) {
				case 'name':
					$placeholder = __( 'e.g. John Doe', 'sharee' );
					$row         = 'open';
					$col         = 2;
					break;
				case 'number':
					$row         = 'close';
					$col         = 2;
					$optional    = true;
					$placeholder = 'T0000000000000';
					$description = _x( 'If you have a valid tax number, please input.', 'tax-number-detail', 'sharee' );
					break;
				case 'address':
					$placeholder = __( 'e.g. Minami Aoyama 2-11-13, Minatoku Tokyo', 'sharee' );
					break;
				case 'address2':
					$row         = 'open';
					$col         = 2;
					$placeholder = __( 'e.g. Minami Aoyama Bld. 4F', 'sharee' );
					break;
				case 'tel':
					$col      = 2;
					$optional = true;
					break;
				case 'country':
					$should_display_company = apply_filters( 'sharee_require_country', false );
					if ( ! $should_display_company ) {
						continue 2;
					}
					break;
				case 'zip':
					$row         = 'close';
					$placeholder = 'e.g. 107-0062';
					$description = sprintf( '<a href="#" id="sharee-zip-search">%s</a>', __( 'Enter address from zip code.', 'sharee' ) );
					$col         = 2;
					break;
			}
			$field = [
				'name'        => $key,
				'label'       => $label,
				'value'       => $address->get_value( $key ),
				'placeholder' => $placeholder,
				'group'       => $row,
				'type'        => $type,
				'col'         => $col,
				'optional'    => $optional,
				'description' => $description,
			];
			$address_fields['fields'][ '_billing_' . $key ] = $field;
		}
		$args = [
			'billing' => $billing_fields,
			'address' => $address_fields,
		];
		return $args;
	}
}
