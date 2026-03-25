<?php
namespace TWRF;

class Autoloader {
	public function __construct() {
		spl_autoload_register( array( $this, 'autoload' ) );
	}

	public function autoload( $class ) {
		$prefix = 'TWRF\\';
		if ( strpos( $class, $prefix ) !== 0 ) {
			return;
		}

		$class_name = substr( $class, strlen( $prefix ) );
		$file = TWRF_PLUGIN_DIR . 'includes/class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}