<?php
namespace Hametuha;


use Hametuha\Pattern\Singleton;

/**
 * Entry point for share
 *
 * @package sharee
 * @property string $root_dir
 * @property string $root_url
 */
class Sharee extends Singleton {

	/**
	 * Executed in constructor
	 */
	protected function init() {
		$this->load_text_domain();
		foreach ( [ 'Hooks', 'Models', 'Rest', 'Screen' ] as $dir ) {
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
				if ( ! apply_filters( 'sharee_default_initialize', true, $class_name ) ) {
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
	 * Getter
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'root_dir':
				return dirname( dirname( __DIR__ ) );
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
