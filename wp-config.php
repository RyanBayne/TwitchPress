<?php
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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'twitchpressbeta');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'V=<>(;(L<jyHEJIltZ]WXW_xDd^1tP{4$=^d}lej,If?pNjT>Qr}o0>EJ-#S%Vh`');
define('SECURE_AUTH_KEY',  'SU:eI<2$6T >A[,6}?H]Vh/|iB:NNR+,V<]1>25t>|$AU|B(y>-9ZE~5%k?+)YiW');
define('LOGGED_IN_KEY',    'KP&;^p,y9,>/CXxEiw5nqvyJvYB7l~IW7@hb[;%tpZFT0lgCbj)v@L^4wY`6S5ju');
define('NONCE_KEY',        '^-p^4F^WH!9{*% d.C(0zYA(`~Au7)=++$,CAixF1vYj>sy6(GbQfCrU*M@20$|T');
define('AUTH_SALT',        'I#sq`5q@EM06xT5n:dhu;E&;3=hf- QMGee@%CgaeAPsJ7}0SG<6|Ia?W+5@ LtW');
define('SECURE_AUTH_SALT', '{iW_=0VWlAm|X*{4UM<b;^PgOhahDCL]UuKYKc8|9<A8,[QD/k3 &VR!.K)PtAdV');
define('LOGGED_IN_SALT',   '27udZJDsBo-|0p2[CXs~1O_0omE+oeh<ZI2H`(E =<qTNAIsBim|EpV.{)8;LC:x');
define('NONCE_SALT',       ',(qh09=U!LWyK<6khtV{-acxsNSk-6?Z+_]P3rW6PR]5kMEap%ZbPgD1Qn+au[G=');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
