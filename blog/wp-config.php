<?php
define( 'WP_CACHE', true );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u158269705_xyhen' );

/** MySQL database username */
define( 'DB_USER', 'u158269705_dybuz' );

/** MySQL database password */
define( 'DB_PASSWORD', 'tuDuXetyGe' );

/** MySQL hostname */
define( 'DB_HOST', 'mysql' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'i2@(lh.3;+SBat]SCCRk(yxSNBKR|U4BLl5_!HU!US@m-[sx*?|r,fPpF;-[EVpt' );
define( 'SECURE_AUTH_KEY',   'chE933Bsr& ~e[J{GkS..`t}HYXkSp=4:FqdcB;;?wHs:EQ8m$|blP4#+i*odI-@' );
define( 'LOGGED_IN_KEY',     'A{o53j!@IM? EJ.u;YZFe{R2L/*chO5~U?Hpq Jc1@FOk+5JLs_xRIauJo#*1(`G' );
define( 'NONCE_KEY',         'J_ex(+th7|2tZN4AtOrl;p@4UKi.JQ#/h:WBCjD?O0Mk}]bgu0JCkW+3GQqCf>28' );
define( 'AUTH_SALT',         'c{aE^?2` 4zz1lko3^;L:P>6+eFg}41*JQn?gv-AGRG0k)#RUK471T]hU!u[ga: ' );
define( 'SECURE_AUTH_SALT',  '>1SmG+)q,q=Ya<gH84.Y=Qu_x!<UEd:Gy}F.v;5<>MIyM2$f1_&b,)kvjHdgk;jH' );
define( 'LOGGED_IN_SALT',    'x&=@GZl|~yr&Z.ha2j{=[mSGS7;/{y;CKsJ]9`W` h*VJ@X26v>n.]q!ifle@86}' );
define( 'NONCE_SALT',        'j#a/<@H)1=Do+c}&$[AN~gOOzd0;lbl-a;`]h(GoU<hMdP^c*TJ?|#ynifdYeYQC' );
define( 'WP_CACHE_KEY_SALT', 'I/B.rE[~P.Y=LK)CH7DmXO|Va(Y&*j$9,zR0Kr|$C{S55|nLMi3x*t#AP|bzEvwi' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
