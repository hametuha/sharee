<?php
namespace Hametuha;


use Hametuha\Pattern\Singleton;
use Hametuha\Sharee\Command;

/**
 * Entry point for share
 *
 * @package sharee
 * @property string $root_dir
 * @property string $root_url
 */
class Sharee extends Singleton {

	const VERSION = '0.8.0';

	/**
	 * Executed in constructor
	 */
	protected function init() {
		add_action( 'init', [ $this, 'load_text_domain' ], 1 );
		// Register global assets
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		// Register autoloader
		add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ] );
		// Register command
		if ( defined( 'WP_CLI' ) && \WP_CLI ) {
			\WP_CLI::add_command( 'sharee', Command::class );
			if ( class_exists( 'Hametuha\Sharee\Tests\StubCommands' ) ) {
				\WP_CLI::add_command( 'sharee-test', \Hametuha\Sharee\Tests\StubCommands::class );
			}
		}
	}

	/**
	 * Load all files.
	 */
	public function after_setup_theme() {
		// Load all files.
		$default_off = [];
		if ( ! self::should_enable( 'billing' ) ) {
			$default_off[] = 'BillingList';
			$default_off[] = 'HbAccountScreen';
		}
		$dirs = [
			'Hooks'  => false,
			'Models' => false,
			'Rest'   => false,
			'Screen' => true,
		];
		foreach ( $dirs as $dir => $only_in_admin ) {
			if ( $only_in_admin && ! is_admin() ) {
				continue;
			}
			$path = __DIR__ . '/Sharee/' . $dir;
			if ( ! is_dir( $path ) ) {
				continue;
			}
			foreach ( scandir( $path ) as $file ) {
				if ( ! preg_match( '#^([^._].*)\.php$#u', $file, $match ) ) {
					continue;
				}
				$class_name = "Hametuha\\Sharee\\{$dir}\\{$match[1]}";
				if ( ! class_exists( $class_name ) ) {
					continue;
				}
				$default_on = ! in_array( $match[1], $default_off, true );
				$default_on = apply_filters( 'sharee_default_initialize', $default_on, $class_name );
				if ( ! $default_on ) {
					continue;
				}
				call_user_func( [ $class_name, 'get_instance' ] );
			}
		}
	}



	/**
	 * Load text domain
	 *
	 * @return bool
	 */
	public function load_text_domain() {
		$mo = sprintf( 'sharee-%s.mo', get_user_locale() );
		return load_textdomain( 'sharee', $this->root_dir . '/languages/' . $mo );
	}

	/**
	 * Load admin global assets.
	 */
	public function admin_enqueue_scripts() {
		$path = '/assets/css/admin.css';
		wp_enqueue_style( 'sharee-admin-style', $this->root_url . $path, [], md5_file( $this->root_dir . $path ) );
	}

	/**
	 * Check if service should be enabled.
	 *
	 * @param string $service
	 * @return bool
	 */
	public static function should_enable( $service ) {
		return (bool) apply_filters( 'sharee_should_enable', false, $service );
	}

	/**
	 * Getter
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'root_dir':
				return dirname( __DIR__, 2 );
				break;
			case 'root_url':
				return str_replace( ABSPATH, home_url( '/' ), $this->root_dir );
				break;
			default:
				return null;
				break;
		}
	}
}
