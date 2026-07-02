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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'kFhe@yV9Z ll#h$il[*I];oT9[In4#-^ZwPO5azLLfUl-NNuEb/z=5UG<.O^mJ|w' );
define( 'SECURE_AUTH_KEY',   '=5>YysNx|1<;LpwJE,HtC>+,h]33Brf`T%g`dO}-]W88|%>i3dlqW7!K-tIBETvz' );
define( 'LOGGED_IN_KEY',     '_lS>GaCJGpK(HT=yr:3N!?%WuZ!q+|CUqix+7V56~Q1A06TV`eJlwuq>5gf}-:Bs' );
define( 'NONCE_KEY',         '}5@c~_9a.8xy||+~exDV#L)cLO418hifWy:rfNtOu68|Ft))?*ow%mAkpE&7|2B[' );
define( 'AUTH_SALT',         ',`OOAa2]M(9Rg(<%.~7DG$7D<tPGE,3a:b,PwE8u67]C*]dfdLNPa`7SOPd1D}1O' );
define( 'SECURE_AUTH_SALT',  'J>!,8A^h_*@1Id8I>t5TY3 0$]*tKp|I5ddy[0QHhg3/Ix<j#GIL-Z;BWuMI,3?C' );
define( 'LOGGED_IN_SALT',    'uLZ,IX+ 8L@.i%0k]Z6c>Bo+LJ=ia3!g/XyQWJ*n3n_G1yCh90=QQu~O+DI{:DuQ' );
define( 'NONCE_SALT',        'VwM5l=5:v^lIOPt}[brno&T+3v)w:CEnE~,qN%y){6F5HM>[MfwxnxOXUq9O~W2+' );
define( 'WP_CACHE_KEY_SALT', ' t8Ch,7EYe:qI4@qK!z0?v^^Dm6n1}|8TsbDvts(?NYp6Dcn20z9J_Aijto|-mpS' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
