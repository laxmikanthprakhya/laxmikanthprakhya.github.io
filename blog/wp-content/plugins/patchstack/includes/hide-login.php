<?php

// Do not allow the file to be called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class is used to hide the login page, if it's enabled.
 */
class P_Hide_Login extends P_Core {

	/**
	 * Whether we should use PHP to redirect the login.
	 *
	 * @var boolean
	 */
	protected $wp_login_php = false;

	/**
	 * Add the actions required to hide the login page.
	 *
	 * @param Patchstack $core
	 * @return void
	 */
	public function __construct( $core ) {
		parent::__construct( $core );

		if ( $this->get_option( 'patchstack_license_free', 0 ) == 1 ) {
			return;
		}

		// Determine if the proper WordPress version is installed.
		global $wp_version;
		if ( version_compare( $wp_version, '4.0-RC1-src', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notices_incompatible' ) );
			add_action( 'network_admin_notices', array( $this, 'admin_notices_incompatible' ) );
			return;
		}

		// No need to continue if it is not enabled.
		if ( ! get_site_option( 'patchstack_mv_wp_login' ) || ! get_site_option( 'patchstack_rename_wp_login' ) ) {
			return;
		}

		// We need to load the plugin library for multisite.
		if ( is_multisite() && ( ! function_exists( 'is_plugin_active_for_network' ) || ! function_exists( 'is_plugin_active' ) ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		// Register the filters and actions for the functionality.
		add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );
		add_filter( 'network_site_url', array( $this, 'network_site_url' ), 10, 3 );
		add_filter( 'wp_redirect', array( $this, 'wp_redirect' ), 10, 2 );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 9999 );
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
		add_action( 'init', array( $this, 'deny_default_login_page' ) );
	}

	/**
	 * Deny access to wp-login if rewrite wp-admin option is enabled.
	 *
	 * @return void
	 */
	public function deny_default_login_page() {
		if ( get_site_option( 'patchstack_mv_wp_login' ) && strpos( strtolower( $_SERVER['REQUEST_URI'] ), 'wp-login.php' ) !== false ) {
			die( 'Forbidden!' );
		}
	}

	/**
	 * Send the email that contains the new login page URL.
	 *
	 * @return boolean If the email was sent or not.
	 */
	public function send_email() {
		global $current_user;
		$subject = __( 'New Login URL', 'patchstack' );
		$message = '<br /><br />Your login page is now  here: <strong> <a href="' . get_site_url() . '/' . get_site_option( 'patchstack_rename_wp_login' ) . '">' . get_site_url() . '/' . get_site_option( 'patchstack_rename_wp_login' ) . '</strong></a>';
		return wp_mail( $current_user->user_email, $subject, $message );
	}

	/**
	 * Get the current site's URL.
	 *
	 * @param string  $url
	 * @param string  $path
	 * @param string  $scheme
	 * @param integer $blog_id
	 * @return string
	 */
	public function site_url( $url, $path, $scheme, $blog_id ) {
		return $this->filter_wp_login_php( $url, $scheme );
	}

	/**
	 * Determine if the current page is a login/registration page.
	 *
	 * @return void
	 */
	public function plugins_loaded() {
		global $pagenow;
		$stop    = false;
		$request = parse_url( $_SERVER['REQUEST_URI'] );

		// If the current page is wp-login.php
		if ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-login.php' ) !== false || untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) ) && ! is_admin() ) {
			$this->wp_login_php     = true;
			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );
			$pagenow                = 'index.php';
			$stop                   = true;
		}

		// If the current page is the renamed login page.
		if ( ! $stop && ( untrailingslashit( $request['path'] ) === home_url( $this->new_login_slug(), 'relative' ) || ( ! get_site_option( 'permalink_structure' ) && isset( $_GET[ $this->new_login_slug() ] ) && empty( $_GET[ $this->new_login_slug() ] ) ) ) ) {
			$pagenow = 'wp-login.php';
			$stop    = true;
		}

		// If the current page is registration page.
		if ( ! $stop && ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-register.php' ) !== false || untrailingslashit( $request['path'] ) === site_url( 'wp-register', 'relative' ) ) && ! is_admin() ) ) {
			$this->wp_login_php     = true;
			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );
			$pagenow                = 'index.php';
		}
	}

	/**
	 * Determine if we should redirect the user upon visiting new/old login page.
	 *
	 * @return void
	 */
	public function wp_loaded() {
		global $pagenow;
		$request = parse_url( $_SERVER['REQUEST_URI'] );

		// Redirect when admin page is requested but no admin access.
		if ( is_admin() && ! is_user_logged_in() && ! defined( 'DOING_AJAX' ) && $pagenow !== 'admin-post.php' && ( isset( $_GET ) && empty( $_GET['adminhash'] ) && $request['path'] !== '/wp-admin/options.php' ) ) {
			wp_safe_redirect( home_url( '/404' ) );
			exit;
		}

		// If the current page is the login page, redirect to 404.
		if ( $pagenow === 'wp-login.php' && $request['path'] !== $this->user_trailingslashit( $request['path'] ) && get_site_option( 'permalink_structure' ) ) {
			wp_safe_redirect( home_url( '/404' ) );
			exit;
		}

		// Other possible login pages that we need to block by default.
		$login_pages = array(
			home_url( 'wp-login.php', 'relative' ),
			home_url( 'login', 'relative' ),
			site_url( 'login', 'relative' ),
		);
		if ( in_array( untrailingslashit( $_SERVER['REQUEST_URI'] ), $login_pages, true ) ) {
			wp_safe_redirect( home_url( '/404' ) );
			exit;
		}

		// Determine if we should redirect the user to the new login page.
		if ( $this->wp_login_php ) {
			if ( ( $referer = wp_get_referer() ) && strpos( $referer, 'wp-activate.php' ) !== false && ( $referer = parse_url( $referer ) ) && ! empty( $referer['query'] ) ) {
				parse_str( $referer['query'], $referer );

				// When a user is self-created.
				if ( ! empty( $referer['key'] ) && ( $result = wpmu_activate_signup( $referer['key'] ) ) && is_wp_error( $result ) && ( $result->get_error_code() === 'already_active' || $result->get_error_code() === 'blog_taken' ) ) {
					wp_safe_redirect( $this->new_login_url() . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
					exit;
				}
			}
			$this->wp_template_loader();
		}

		@require_once ABSPATH . 'wp-includes/post.php';

		// There must be a better way
		$one     = get_home_url() . '/' . $request['path'] . '/';
		$two     = get_home_url() . '/' . get_site_option( 'patchstack_rename_wp_login' ) . '/';
		$one     = str_replace( get_home_url(), '', $one );
		$two_url = parse_url( get_home_url() );
		$two     = str_replace( $two_url['scheme'] . '://' . $two_url['host'], '', $two );
		$one     = str_replace( '//', '/', $one );
		$two     = str_replace( '//', '/', $two );

		// Show the login page if not already logged in.
		if ( $one == $two ) {
			global $error, $interim_login, $action, $user_login;

			if ( is_user_logged_in() && ! isset( $_REQUEST['action'] ) ) {
				wp_safe_redirect( admin_url() );
				exit;
			}
			@require_once ABSPATH . 'wp-login.php';
			exit;
		}
	}

	/**
	 * Load the template loader.
	 *
	 * @return void
	 */
	private function wp_template_loader() {
		global $pagenow;
		$pagenow = 'index.php';

		if ( ! defined( 'WP_USE_THEMES' ) ) {
			define( 'WP_USE_THEMES', true );
		}

		wp();
		if ( $_SERVER['REQUEST_URI'] === $this->user_trailingslashit( str_repeat( '-/', 10 ) ) ) {
			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/wp-login-php/' );
		}

		@require_once ABSPATH . WPINC . '/template-loader.php';
		exit;
	}

	/**
	 * Filter the wp-login.php URL.
	 *
	 * @param string $url
	 * @param string $scheme
	 * @return string
	 */
	public function filter_wp_login_php( $url, $scheme = null ) {
		// Attempt to retrieve the URL.
		if ( strpos( $url, 'wp-login.php' ) !== false ) {
			if ( is_ssl() ) {
				$scheme = 'https';
			}

			$args = explode( '?', $url );
			if ( isset( $args[1] ) ) {
				parse_str( $args[1], $args );
				if ( isset( $args['login'] ) ) {
					$args['login'] = rawurlencode( $args['login'] );
				}
				$url = add_query_arg( $args, $this->new_login_url( $scheme ) );
			} else {
				$url = $this->new_login_url( $scheme );
			}
		}

		return $url;
	}

	/**
	 * Get the network site URL.
	 *
	 * @param string $url
	 * @param string $path
	 * @param string $scheme
	 * @return string
	 */
	public function network_site_url( $url, $path, $scheme ) {
		return $this->filter_wp_login_php( $url, $scheme );
	}

	/**
	 * Redirect the user to given location.
	 *
	 * @param string  $location
	 * @param integer $status
	 * @return string
	 */
	public function wp_redirect( $location, $status ) {
		return $this->filter_wp_login_php( $location );
	}

	/**
	 * Show a notice that WordPress needs to be upgraded.
	 *
	 * @return void
	 */
	public function admin_notices_incompatible() {
		echo wp_kses( '<div class="error notice is-dismissible"><p>' . __( 'Patchstack: Please upgrade to the latest version of WordPress to activate', 'patchstack' ) . '</p></div>', $this->allowed_html );
	}

	/**
	 * Get the new login slug.
	 *
	 * @return string
	 */
	private function new_login_slug() {
		if ( $slug = get_site_option( 'patchstack_rename_wp_login' ) ) {
			return $slug;
		} elseif ( ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) && ( $slug = get_site_option( 'patchstack_rename_wp_login', 'login' ) ) ) ) {
			return $slug;
		} elseif ( $slug = 'login' ) {
			return $slug;
		}
	}

	/**
	 * Get the new login URL.
	 *
	 * @param string $scheme
	 * @return void
	 */
	public function new_login_url( $scheme = null ) {
		if ( get_site_option( 'permalink_structure' ) ) {
			return $this->user_trailingslashit( home_url( '/', $scheme ) . $this->new_login_slug() );
		} else {
			return home_url( '/', $scheme ) . $this->new_login_slug();
		}
	}

	/**
	 * Determine if we have to use trailing slashes.
	 *
	 * @return boolean
	 */
	private function use_trailing_slashes() {
		return substr( get_site_option( 'permalink_structure' ), - 1, 1 ) === '/';
	}

	/**
	 * Use trailing slashes, if needed.
	 *
	 * @param string $string
	 * @return void
	 */
	private function user_trailingslashit( $string ) {
		return $this->use_trailing_slashes() ? trailingslashit( $string ) : untrailingslashit( $string );
	}
}
