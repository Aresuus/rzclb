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
define('DB_NAME', 'rzclub_official');

/** MySQL database username */
define('DB_USER', 'rzclub_official');

/** MySQL database password */
define('DB_PASSWORD', 'mangol08061991');

/** MySQL hostname */
define('DB_HOST', 'rzclub.mysql.tools');

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
define('AUTH_KEY',         'zvyE(Z2uf5ldV#L32YLcGTN1gi(O6tmHcCL#Wpw!v82!iRheACosF!nDUMM^N)Gf');
define('SECURE_AUTH_KEY',  '4(eWKDs8LeJ517DFT&682%36eAN2qMkMJqjdu@S!cS1w!tmE!&@G*tR6pCVdh09V');
define('LOGGED_IN_KEY',    'I655sL1anUABK@KNuBL9UrBjbUZ4F9x7rESAkeT%YF2cPIbD2Mo7tRnk0vG7^Xqt');
define('NONCE_KEY',        'Cb)4LlnL@XVvm9Gb1N!k@Tu5s3)5s@M#KPZvqV5KpBWTqk)I0@Ul!8!2!PWZrJM&');
define('AUTH_SALT',        'd!6RpmG%vnCIIhO8v6yVGTqQ!(zv66K^vQHNOYySn8AwklgQeuddX!tqa2zJU0Lo');
define('SECURE_AUTH_SALT', 'GLrh6TjiSj^OQgu6(TPtz&WH&HNhM&M23%#5%TbvSPU1mMojATZno7y(RH1(9!(5');
define('LOGGED_IN_SALT',   'H8NkroG3xXjIo&9PrLy0Es)wqhXH%Y3jv)#*bLdOE(&OaY*(HX7(MaY)34ht6Tcg');
define('NONCE_SALT',       'AX6aeZDu)XJ!zn@wTZzDiBKd7s(Bf7QKC0bu!FwUA!Mb^ckPywcksTK(iu1C2Xr!');
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
define( 'WP_MEMORY_LIMIT', '256M' );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

define( 'WP_ALLOW_MULTISITE', true );

define ('FS_METHOD', 'direct');