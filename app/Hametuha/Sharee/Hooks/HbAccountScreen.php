<?php

namespace Hametuha\Sharee\Hooks;


use Hametuha\Pattern\Singleton;
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
			$children[ 'billing' ] = __( 'Billing', 'sharee' );
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
		wp_enqueue_script( 'hametuha-hb-status-holder' );
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
				<div class="hb-status-display" data-endpoint="<?php echo esc_attr( $endpoint ) ?>">
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
		// 銀行口座
		//
		$billing_fields = [
			'label'       => '振込先',
			'description' => '入金先情報を入力してください。 東京三菱UFJ銀行だと振り込み手数料が安くなるので、破滅派的に助かります。',
			'submit'      => '保存',
			'action'      => rest_url( '/hametuha/v1/user/billing/bank' ),
			'method'      => 'POST',
			'fields'      => [],
		];
		foreach ( hametuha_bank_account( $user->ID ) as $key => $value ) {
			$label = '';
			$placeholder = '';
			$options = [];
			$row = '';
			$type = 'text';
			$default = '';
			switch ( $key ) {
				case 'group':
					$label = '銀行名';
					$row = 'open';
					break;
				case 'branch':
					$label = '支店名';
					$row = 'close';
					break;
				case 'type':
					$label = '口座種別';
					$type = 'select';
					$options = [
						'普通' => '普通',
						'当座' => '当座',
					];
					$default = '普通';
					$row = 'open';
					break;
				case 'number':
					$label = '口座番号';
					$row = 'close';
					break;
				case 'name':
					$label = '口座名義';
					break;
			}
			$field = [
				'type' => $type,
				'label' => $label,
				'value' => $value,
				'placeholder' => $placeholder,
				'group' => $row,
				'col' => $row ? 2 : 1,
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
		// 請求先
		//
		$address_fields = [
			'label'       => __( 'Billing Information', 'sharee' ),
			'description' => apply_filters( 'sharee_billing_info_desc', __( 'This information is required to send reward to you.', 'sharee' ) ),
			'submit'      => __( 'Save', 'sharee' ),
			'action'      => rest_url( '/sharee/v1/address/me' ),
			'method'      => 'POST',
			'fields'      => [],
		];
		$address = new Address( $user->ID );
		foreach ( Address::settings() as $key => $data ) {
			$label       = $data['label'];
			$placeholder = '';
			$options     = [];
			$row         = '';
			$type        = 'text';
			$col         = 1;
			$optional    = false;
			switch ( $key ) {
				case 'name':
				    $placeholder = __( 'e.g. John Doe', 'sharee' );
					$row = 'open';
					$col = 2;
					break;
				case 'number':
					$row = 'close';
					$col = 2;
					break;
				case 'address':
					$placeholder = __( 'e.g. Minami Aoyama 2-11-13, Minatoku Tokyo', 'sharee' );
					break;
				case 'address2':
					$row = 'open';
					$col = 2;
					$placeholder = __( 'e.g. Minami Aoyama Bld. 4F', 'sharee' );
					break;
                case 'tel':
					$col = 2;
					$optional = true;
                    break;
                case 'country':
                    $should_display_company = apply_filters( 'sharee_require_country', false );
                    var_dump( $should_display_company );
                    if ( ! $should_display_company ) {
                        continue 2;
                    }
                    break;
				case 'zip':
					$row = 'close';
					$placeholder = 'e.g. 107-0062';
					$col = 2;
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
			];
			$address_fields['fields'][ '_billing_'. $key ] = $field;
		}
		$args = [
			'billing' => $billing_fields,
			'address' => $address_fields,
		];
		return $args;
	}

}
