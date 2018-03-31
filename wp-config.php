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
define('DB_NAME', 'subscribers');

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
define('AUTH_KEY',         '*/*J>Gwm/iwZwB85;x<q&~Tsh u{U[rXf]mmdyH@yo_e=#`%2U)VwH/YDLlEBIR*');
define('SECURE_AUTH_KEY',  '6(f+iuW(74NxJeFOt>vrs4!/w^)8x.>3 ,p@?h4KwAQPFS6P9$AHM|jd8!>^MOFf');
define('LOGGED_IN_KEY',    'RKC_ #/{pHQl11Z.>5a{/Xb.gY~K0Pt>9OkJ0]ssITGhs_@)apl#QkLicR`hOEQ&');
define('NONCE_KEY',        'SL$PnEx#A+I=~@TAyn9IeLuI_U+p,{zBrMsF <5m5U#l8MkL$4vlnHkl,@w!dgZL');
define('AUTH_SALT',        '@|6I3umnu@(;/Qr*qU_?-|14QvP=CSP@yLs^?*b|#gh#qZqwwU_|Wb4{9&N1l<}:');
define('SECURE_AUTH_SALT', 's*gV.=2-+At}|Q:gK@E|BbK^Ja8Vv)e>!e# H1fb#u!({agGRrt](MjNnkfg;EY?');
define('LOGGED_IN_SALT',   'OfyZ=$?No>2E#ekF~b.d>O}4Qz76Zw@TuN-dV4~Pm-1;;nRx%)rFo5xKp7ker,of');
define('NONCE_SALT',       'UMR6$-X`o+!{`d^-q,(`)!y{ty&~4[`qUxmtx=R`3-6}E Hub-.YmgqS7Ilu3:/9');

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
