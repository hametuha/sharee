<?php
/**
 * Local login hellper.
 */

// Prevent direct access
if ( ! defined('ABSPATH' ) ) {
    exit;
}

if ( 'local' !== wp_get_environment_type() ) {
	// Only on local environment.
	return;
}

// HAMETUHA_LOGGED_IN_AS
if ( ! defined( 'HAMETUHA_LOGGED_IN_AS' ) || empty( HAMETUHA_LOGGED_IN_AS ) ) {
	return;
}

/**
 * Override user id if constants set.
 */
add_filter( 'determine_current_user', function( $user_id ) {

	// Get user.
	$user = get_user_by( 'login', HAMETUHA_LOGGED_IN_AS );
	if ( ! $user ) {
		return $user_id;
	}

	// 既に同じユーザーIDが設定されている場合は何もしない
	if ( $user_id && (int) $user_id === (int) $user->ID ) {
		return $user_id;
	}

	// 新しいユーザーでログイン
	wp_set_current_user( $user->ID, $user->user_login );
	wp_set_auth_cookie( $user->ID, true );
	return $user->ID;
}, 30 );

/**
 * ローカル環境用：auth_redirect() のオーバーライド
 *
 * Chrome DevTools MCPのようにクッキーを保持できない環境で、
 * wp-adminにアクセスできるようにする。
 *
 * ローカル環境でのみこの簡易版を使用し、本番環境ではWordPressコアの実装を使用。
 */
if ( ! function_exists( 'auth_redirect' ) ) {
	function auth_redirect() {
		// ログイン済みなら認証OK
		if ( get_current_user_id() ) {
			return;
		}

		// 未ログインならログインページへリダイレクト
		nocache_headers();

		$redirect = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$login_url = wp_login_url( $redirect, true );

		wp_redirect( $login_url );
		exit;
	}
}
