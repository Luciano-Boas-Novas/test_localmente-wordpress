<?php

class RDSMLogFileHelper {

  	public static function write_to_log_file($value) {
	  	global $wp_filesystem;
	  	
	  	if ( ! $wp_filesystem ) {
		    require_once( ABSPATH . 'wp-admin/includes/file.php' );
		    WP_Filesystem();
		}

	  	$file_path = RDSM_LOG_FILE_PATH . get_option('rdsm_refresh_token');
	    $time = gmdate( "F jS Y, H:i P", time() );
	    $log = "#$time\r\n$value\r\n";

	    if ( $wp_filesystem->exists($file_path) ) {
		    $wp_filesystem->put_contents( $file_path, $log, FS_CHMOD_FILE | FILE_APPEND );
		    self::limit_log_file( $file_path );
		}
  	}

  	public static function get_log_file() {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
		    require_once( ABSPATH . 'wp-admin/includes/file.php' );
		    WP_Filesystem();
		}

		$file_path = RDSM_LOG_FILE_PATH . get_option('rdsm_refresh_token');
		return $wp_filesystem->get_contents_array($file_path);
  	}

  	public static function has_error() {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
		    require_once( ABSPATH . 'wp-admin/includes/file.php' );
		    WP_Filesystem();
		}

		$file_path = RDSM_LOG_FILE_PATH . get_option('rdsm_refresh_token');
		$file_content = $wp_filesystem->get_contents($file_path);

  		return (strpos($file_content, "errors") !== false);
  	}

  	private static function limit_log_file($file_path) {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
		    require_once( ABSPATH . 'wp-admin/includes/file.php' );
		    WP_Filesystem();
		}

		$file = $wp_filesystem->get_contents_array($file_path);

		for ($i = 0;count($file) > RDSM_LOG_FILE_LIMIT;$i++) {
		  	unset($file[$i]);
		}

		$wp_filesystem->put_contents($file_path, implode("", $file));
  	}

  	public static function clear_log_file() {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
		    require_once( ABSPATH . 'wp-admin/includes/file.php' );
		    WP_Filesystem();
		}

  		return $wp_filesystem->put_contents(RDSM_LOG_FILE_PATH . get_option('rdsm_refresh_token'), "");
  	}
}
?>
