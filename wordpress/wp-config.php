<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache


/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '$:i5G1Ng+K)(=_LI&f.*+WI^*UZvJw(EJ}wxSo?Ws|`U4-)&BZ)+HG[GjI#m-znk');
define('SECURE_AUTH_KEY',  'G~;ob})3-%Z5Y+cojQyK5Ty#NCc~}%KKX4>6)}>_ytzWAH$oL<H-! rr6WumNm5>');
define('LOGGED_IN_KEY',    ')xXTe@=EJnPXT_e*F]=DP6J!^j41B>p-k !;/+D{~ysi$dV9i2}eBF.-/Rz+gtK;');
define('NONCE_KEY',        '4_}-|nF+K9@*|X@StYgA54ouU^etT_3|ZTuQCNUFXakW)#j&rT.6c+(Ww#:qGA~l');
define('AUTH_SALT',        'wfc?7uQ2`G;k NqN[+kmsedHy|-}-dYCG.-6|C`N*]]q8/5nF<bxIKi>|W-M:8T0');
define('SECURE_AUTH_SALT', ')bS`K+.?SYxlH@K-9``4^uiuyw|#:$EIr/ f%Hr9p[bZ:@o}/]o-yW$7ay6t>o4B');
define('LOGGED_IN_SALT',   '_=#uwKb}H1 Ts*t N#(^d(lbA0W3~+vT~VrICW)5ubTAnpqWr0dxmA5T4!e_G;n/');
define('NONCE_SALT',       '>8m0`Z7Nk>|Q#;!uWR0FEF#EP7GYHsN>nR]np[c2kmk1TpF/1~uAfUp!_#v_7{Vb');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');