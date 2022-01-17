<?php

// Do not allow the file to be called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core class is used as a base class for all the other classes.
 * This will allow us to declare certain global methods/variables.
 */
class P_Core {

	/**
	 * This will allow us to communicate between classes.
	 *
	 * @var Patchstack
	 */
	public $plugin;

	/**
	 * Whether or not the site is a multisite.
	 *
	 * @var boolean
	 */
	private $is_multi_site = false;

	/**
	 * Allowed HTML for the wp_kses function used to render certain paragraphs of texts.
	 * 
	 * @var array
	 */
	public $allowed_html = array(
		'a'        => array(
			'href'     => array(),
			'title'    => array(),
			'target'    => array()
		),
		'p'        => array(
			'style'    => array()
		),
		'span'     => array(
			'style'    => array()
		),
		'br'       => array(),
		'strong'   => array(),
		'b'        => array(),
		'i'		   => array(
			'style'    => array()
		),
		'label'	   => array(
			'for'      => array(),
			'style'    => array()
		),
		'input'    => array(
			'type' 	   => array(),
			'class'	   => array(),
			'name'	   => array(),
			'id'  	   => array(),
			'value'    => array(),
			'checked'  => array(),
			'style'    => array()
		),
		'textarea' => array(
			'rows'     => array(),
			'id'       => array(),
			'name'     => array()
		),
		'select'   => array(
			'name'     => array(),
			'id'       => array(),
			'data-selected' => array()
		),
		'option'   => array(
			'value'    => array(),
			'selected' => array()
		),
		'table'    => array(
			'class'    => array(),
			'style'    => array()
		),
		'thead'    => array(),
		'th'       => array(
			'style'    => array()
		),
		'tr' 	   => array(),
		'td' 	   => array(),
		'div' 	   => array(
			'class'    => array(),
			'style'    => array()
		)
	);

	/**
	 * @param Patchstack $plugin
	 * @return void
	 */
	public function __construct( $plugin ) {
		$this->plugin        = $plugin;
		$this->is_multi_site = is_multisite();
	}

	/**
	 * In case of multisite we want to determine if there's a difference between the
	 * network setting and site setting and if so, use the site setting.
	 *
	 * @param string $name
	 * @param mixed  $default
	 * @return mixed
	 */
	public function get_option( $name, $default = false ) {
		// We always want to return the site option on the default settings management page.
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'patchstack-multisite-settings' && is_super_admin() ) {
			return get_site_option( $name, $default );
		}

		// Get the setting of the current site.
		$secondary = get_option( $name, $default );

		// Get the setting of the network and in case there's a difference,
		// return the value of site.
		$main = get_site_option( $name, $default );
		return $main != $secondary ? $secondary : $main;
	}

	/**
	 * In case we need to retrieve the option of a specific site, we can use this.
	 * It will determine if it's on a multisite environment and if so, use get_blog_option.
	 *
	 * @param int    $site_id
	 * @param string $name
	 * @param mixed  $default
	 * @return mixed
	 */
	public function get_blog_option( $site_id, $name, $default = false ) {
		if ( $this->is_multi_site ) {
			return get_blog_option( $site_id, $name, $default );
		}

		return get_option( $name, $default );
	}

	/**
	 * In case we need to update the option of a specific site, we can use this.
	 * It will determine if it's on a multisite environment and if so, use update_blog_option.
	 *
	 * @param int    $site_id
	 * @param string $name
	 * @param mixed  $value
	 * @return mixed
	 */
	public function update_blog_option( $site_id, $name, $value ) {
		if ( $this->is_multi_site ) {
			return update_blog_option( $site_id, $name, $value );
		}

		return update_option( $name, $value );
	}

	/**
	 * Determine if the license is active and not expired.
	 *
	 * @return boolean
	 */
	public function license_is_active() {
		if ( get_option( 'patchstack_license_activated', 0 ) ) {
			return true;
		}

		$expiry = get_option( 'patchstack_license_expiry', '' );
		if ( $expiry != '' && ( strtotime( $expiry ) < ( time() + ( 3600 * 24 ) ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if a given PHP function is disabled or not.
	 *
	 * @param string $name Name of the function to check.
	 * @return boolean Whether or not the function is available to call.
	 */
	public function function_available( $name ) {
		$safe_mode = ini_get( 'safe_mode' );
		if ( $safe_mode && strtolower( $safe_mode ) != 'off' ) {
			return false;
		}

		// Determine if the function is available.
		if ( in_array( $name, array_map( 'trim', explode( ',', ini_get( 'disable_functions' ) ) ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Attempt to get the client IP by checking all possible IP (proxy) headers.
	 *
	 * @return string
	 */
	public function get_ip() {
		// IP address header override set?
		$override = get_site_option( 'patchstack_firewall_ip_header', '' );
		if ( $override != '' && isset( $_SERVER[ $override ] ) ) {
			return $_SERVER[ $override ];
		}

		// IP address headers which should have priority and be used regardless of other headers.
		$priority = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_SUCURI_CLIENTIP' );
		foreach ( $priority as $header ) {
			if ( isset( $_SERVER[ $header ] ) && filter_var( $_SERVER[ $header ], FILTER_VALIDATE_IP ) !== false ) {
				return $_SERVER[ $header ];
			}
		}

		// Special case for hosts that have a weird configuration.
		if ( $this->function_available( 'php_uname' ) ) {
			$uname = @php_uname();

			// Bluehos and Hostmonster store the real IP in $_SERVER['REMOTE_ADDR'] but the proxy IP in HTTP_X_FORWARDED_FOR.t
			if ( strpos( $uname, 'bluehost' ) !== false || strpos( $uname, 'hostmonster' ) !== false ) {
				return $_SERVER['REMOTE_ADDR'];
			}

			// Hostgator stores the real IP in $_SERVER['REMOTE_ADDR'] but the proxy IP in HTTP_X_FORWARDED_FOR.
			if ( ( strpos( $uname, 'websitewelcome' ) || strpos( $uname, 'hostgator' ) ) && isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && $_SERVER['HTTP_X_FORWARDED_FOR'] != $_SERVER['REMOTE_ADDR'] ) {
				return $_SERVER['REMOTE_ADDR'];
			}
		}

		// In order of priority, try to get the IP address.
		$allowed = array( 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'SUCURI_RIP', 'REMOTE_ADDR' );
		foreach ( $allowed as $header ) {
			if ( isset( $_SERVER[ $header ] ) && filter_var( $_SERVER[ $header ], FILTER_VALIDATE_IP ) !== false ) {
				return $_SERVER[ $header ];
			}
		}

		return '127.0.0.1';
	}
}
