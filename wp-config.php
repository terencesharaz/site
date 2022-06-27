<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_portfolio_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'c=DzluQg_Tug:ml*-AK0`b<br*PKZgJ>q8#jbXGBOR-s6ONs9n46mWpHc(`p0j04' );
define( 'SECURE_AUTH_KEY',  'k(;pA1WfAW~0P-7:Xm^2sfa:H(N2Rxn5D+8k-b#PRvfON!Z-5 zx)_%6!O{()U7J' );
define( 'LOGGED_IN_KEY',    '5rf|jSK=ep@`xG(=Kypl`5V-(J]bl/z>}HE}c14nv1GOaHx3d97z~d 0p_wN6=|h' );
define( 'NONCE_KEY',        'aT! `Omyss~n70h@=:RUq:y#7(P/m=G5=|7vke0^.e>y+U#cOYmcyo8?NS>H1gJ3' );
define( 'AUTH_SALT',        'PlD++]P_qcF6X2WR)h[V~|B.+#J9*L48sS7b&8@FUJ:.&NP+chVVJj](#WsG}+<.' );
define( 'SECURE_AUTH_SALT', 'OMVD,X)hY_=s@CVRd`wPo^V-5@RLruP6S.j,X9BfO.3C>D*dB0?^z[X=tn|XL:Qr' );
define( 'LOGGED_IN_SALT',   'imF9BkU?dO)oPYi{B6d#nXY%w_5hS40^(=w._X7~{lD6uR1s93aUd_a`Bk>o3|S/' );
define( 'NONCE_SALT',       'qDgN]]MobD?/KhY,bk@ivkRkN4oX:8d,i,=.Vghy|rNQppC.^<`s;n0ny!)7og-J' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
