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
define('AUTH_KEY',         've9Du-pjlx7+2x8{k1~(}<#`S.o0ZM&_4b1B?@VjNPb!%rHB^NA.4hwF_M>*YMVD');
define('SECURE_AUTH_KEY',  ')fZF&7{ !DFhxae`KMe,peG)|Q}DA[HapNa{*[4>DSbJvjbwxTZVRH)%?r8Y+Tx!');
define('LOGGED_IN_KEY',    'B5.M)0-VRJuNUBjE*eX a>lqzS^N;D2-*E?}S1Ek,#mL3uh}uF{T11 Us.!izIIv');
define('NONCE_KEY',        '.YYM&?hN5,%QLboDZjtB*&{K2=%1+|AEO(%Z% p6rf&#KaPLjmtW?9hB*90/(gMj');
define('AUTH_SALT',        'M`@Gv`Ud6.stMAw5m//tqcFLl~2H7(`k+d=-HQ0V;sS]y+/rCYds*+JSN-I _;2F');
define('SECURE_AUTH_SALT', 'CLC< kAN.ytM) BI qo3TMOp0WPLd^YQ:SQf>cNzAI6aw=Q?$#x0nY|` %OB8{^-');
define('LOGGED_IN_SALT',   'svhW#A[$eTqax:O+-XK)bcp6Jv21A3tbB`ChHZnl9qeG|q3]C.O-^?cx-Y1gXttE');
define('NONCE_SALT',       'Rc35CSHetcuWG$5S|*g&M|nk,PMMGI(tN 8tj1oUOBBCJ*b):Cy.q.3bU&;U91pW');

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
