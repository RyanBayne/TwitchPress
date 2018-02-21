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
define('DB_NAME', 'twitchpressgithub');

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
define('AUTH_KEY',         ')W[ZO]-wXxU`.UQPQ^=I7|zh7^H5!8$dL*WDCv_:dBYPHqrT^_o}rvld{F@eQ2Vu');
define('SECURE_AUTH_KEY',  '.% JWs%yrI~p![wV? RPH[R~a#-jlpe=w~.~Aw&y(jS?K6:$nv:a#n+_1gf?#yZ1');
define('LOGGED_IN_KEY',    '&zE=YXR2Fat_?ILX)+]:6!qPNg$8C3,U[Qo;$Usn7s=S)}BnJ NpWAITM/wI2_wj');
define('NONCE_KEY',        '-/}MnjGmMxyE(ya`%X*pJq&`,_DHMpV&!vF4<Akjd<N!qlS5*yV7ogVTj|-~VN-E');
define('AUTH_SALT',        'u&s0#JdrwQW+oc({#xg)ZUxImkoWK3QHBeeS=vf2)?Ywk*TV|J=uOudHK,6@E$Af');
define('SECURE_AUTH_SALT', 'AV?{9EF04Riv<NR}5K+a)Xq&XS<MT.gNeeO011)<Oi50:s.LheGg(o&Y@m}mlgXj');
define('LOGGED_IN_SALT',   '+V@bzx8 2|IvA-8w k-v,qg}0&eM P_u;=7k!ZCC[tyuH9C_1}L[>6p4sxO  _e+');
define('NONCE_SALT',       '/;rm1D>>NVq/p1Fm:Z?LT_lC+/6}C0xF.f,enb]0D)9Tw*Vo>Fs!|J]MHQ_#h_HR');

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
