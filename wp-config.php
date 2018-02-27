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
define('DB_NAME', 'twitchpressapi6');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', '');

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
define('AUTH_KEY',         'pWUb%&QE9og=gN`fT+/D%2Z,0dQ0(@ymNwf;,KR3Px0A{^2,8bnp6XeoT9`o40Uv');
define('SECURE_AUTH_KEY',  'U8oQ*^/#{xv<dk$VsX(YW9kb^tOp`PY&aRxh8@s7gTM%}Y`]+wbC!%|%M:YC[F=j');
define('LOGGED_IN_KEY',    'mIM[YgJ5PA&|g`O_/LU|3U9uoZ`[FfnfI(I@+4-y~,}{kb|o8H@N4d, F!R-0R@D');
define('NONCE_KEY',        'GVz-JAd>3^_qCz1BA6wn<~xKu~zb]: 7e%.}}@gk?V|[,~!.n>bfp4l=Bw}0]gLl');
define('AUTH_SALT',        '5.}p{0sfx2v95L%He_o$}V!wcTo{F%sAFz!hCZ*t};gEf9-6LiZ69 N.,aVsX_,7');
define('SECURE_AUTH_SALT', ':p(=t9eOre/mAG^@2gg=UQUA5+EBwQOC93>*Ioat?E{}34xU/()w`-K+o*X33*Fo');
define('LOGGED_IN_SALT',   'Efvv+`)c|>`4ZFPd/u8[*!Wi:OxCMc}o][_LEiR(>yo/Y81_AFl(PV.UwvQ6ZZp(');
define('NONCE_SALT',       'cT4B*5 {l@=QY5tq-jIm36phQ>CQG1ivD5j>gM1m)1Mct>?{cWo1O=mz;oT`6]%K');

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
