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
define('DB_NAME', 'twitchpressstreamlabs');

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
define('AUTH_KEY',         ':YYdC]}is~>+Xc0i(#yi=tBU6 ye=1`5z@0 J<O3] O<=x}d=]!{1lEH*0)p{:NG');
define('SECURE_AUTH_KEY',  'j5&fK$97fo^Ct(%9VC-*&lQ`oN//=<haLYGXM.@F4r+H-k<WX*7TYG+fzI4Qjy@U');
define('LOGGED_IN_KEY',    'YA!zh;q{`?R3`bM&_#cl`sw~HXT5~Oa]R:PI)8/bbkL2u*m~x#+(KQdV#p#g|W/M');
define('NONCE_KEY',        'BK8O:P7uEws6,~%t)V^X/:S@Fm%Ha7R2`jN&8#fM[BrvzVOV(g=LNh53/LWdl.nj');
define('AUTH_SALT',        'TFv$c{tG&Ob;_Hi.@t;$f=@^+Ya#,DDY6EY:/zvA=&=Myk_}9dRmKm&!q$5zW3xn');
define('SECURE_AUTH_SALT', 'rSPZclRyyVI1)e@)vu!z3wLwKKJh$.`;Onr,yi74TWWgg0sC}&QIhkh)9h*%#[Q:');
define('LOGGED_IN_SALT',   'eks+7k^bBA{&3br2.j76TulK0:3,i-y1MVJwf$@m1vD.%+`.]`#eboo+=YCAeAzo');
define('NONCE_SALT',       'xFkM=Pe{l|d$dF0S!LCg1[h4;:4j+-<T>s7.GaX4<(oCb5B.x`84o9TT#zKCv?pz');

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
