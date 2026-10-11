<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'sticker' );

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
define( 'AUTH_KEY',         'iaXU2|unT{LZ6oH+yL?)oLIcf ABq$%b&ebOWRb4F&9%$$X^TNrK/Mv;{rdr<]SY' );
define( 'SECURE_AUTH_KEY',  'bim/!_KorZI^[vGiB!d-V@4$(^M~zz^8Z<t_N]~5i5PL_yw/k{r@}/;6t~.tVnT#' );
define( 'LOGGED_IN_KEY',    'sZ?1^8C!/Cagf]T)^J8nnL%OF4bqEDD#AmcehBzGnKmsuX.r&AN>b=ma#ddUnd.V' );
define( 'NONCE_KEY',        '5m&T[S$8Q*jVMR~wS#[~RLn4K?h*$NP?DK;E*`i>>A]M36SF26D&ry6KS~WO*kco' );
define( 'AUTH_SALT',        '#%T/E#OH~N`p</7/##n^!%ZX!2Bjew0^&g$!am;8}_=t+,` (9[WU4!0rH{1O=T0' );
define( 'SECURE_AUTH_SALT', '.~a?c[G4;es[Y.rBo:[GgAFkk&.SoP|B:/Tzh+FXLSh&f#tU6w]d2c)!EtU]GRAo' );
define( 'LOGGED_IN_SALT',   'U7P{w8t+5mbQ&zw*#:ZtrW.`b/ap!N(RT~huI0:u?G1T1qKT5vAC^UK%j?AyP7H.' );
define( 'NONCE_SALT',       'f:}>r.?LW91Dz;5x9]4]/sHv*vo|8bAf`x*bMN[*a+HPUQ9&gP8Viy)},<:[$Ci#' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
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
