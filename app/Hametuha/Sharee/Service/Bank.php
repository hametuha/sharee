<?php

namespace Hametuha\Sharee\Service;


class Bank {

	/**
	 * Detect if user's bank account is ready.
	 *
	 * @param int $user_id
	 * @return bool
	 */
	public static function is_ready( $user_id ) {
		return hametuha_bank_ready( $user_id );
	}

	/**
	 * Check if user is ready to be paid.
	 *
	 * @param int $user_id
	 * @return bool
	 */
	public static function billing_ready( $user_id ) {
		return hametuha_billing_ready( $user_id );
	}

	/**
	 *
	 *
	 * @param int $user_id
	 * @return array
	 */
	public static function get_account( $user_id ) {
		return hametuha_bank_account( $user_id );
	}

}
