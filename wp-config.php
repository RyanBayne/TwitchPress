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
define('DB_NAME', 'twitchpresssyncmerge');

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
define('AUTH_KEY',         '[cwEvC#v*twT-y2wp>$0MJ0ua/]e9tU(m3{2zQ7=q=q[SY$c+H&M:Kl*?e~S3z)n');
define('SECURE_AUTH_KEY',  'p]myFPTIX<@!K?ZgR&VSDp&G<h{02k,jNVEIBY}{MvLR>?oNSSCf{d:(9BF|>aI|');
define('LOGGED_IN_KEY',    'FYDK<;LIN`FY:v!D,r}7(c&$ =pIv{d_4!C</ j7`>e;9%em9MuZ] WBUBr^f!H$');
define('NONCE_KEY',        '/(Y9!5F3klomcy13xR<q3{!Q8)RlqKqM%XtK,xEpS*]c8;3xCe_b#jwr&<Z+GS_J');
define('AUTH_SALT',        'O$INO}mWU<P8 W!0a;5`Nzg{.mn#s;&(B+6tGM]QcU**a$by#/` 2*[i Oe35>nb');
define('SECURE_AUTH_SALT', 'K,GXBd?{Uet%Myx#[atI3[?/>|&Wv%!LP* NTY Fj]2Svdbj7OOu@_w0j%3z,}Z&');
define('LOGGED_IN_SALT',   'aluUa{iCg*X&_2vmg_geOANi~HpiV<8,XcBKD{7`bH>s]bG:y`Hw2RS*HphkJO#`');
define('NONCE_SALT',       '(m75=gtO.%YbRS6xtKi?`CI*o;aZEG=WV8MX9_#>iCM]zcV2`%1e#79VspJqoy|6');

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
