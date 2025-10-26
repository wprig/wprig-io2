<?php
/**
 * WP Rig functions and definitions
 *
 * This file must be parseable by PHP 5.2.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package wp_rig
 */

 /**
  * Add LiveReload script in development mode.
  */

define( 'WP_RIG_MINIMUM_WP_VERSION', '5.4' );
define( 'WP_RIG_MINIMUM_PHP_VERSION', '8.0' );

// Bail if requirements are not met.
if ( version_compare( $GLOBALS['wp_version'], WP_RIG_MINIMUM_WP_VERSION, '<' ) || version_compare( phpversion(), WP_RIG_MINIMUM_PHP_VERSION, '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
	return;
}

// Include WordPress shims.
require get_template_directory() . '/inc/wordpress-shims.php';

// Setup autoloader (via Composer or custom).
if ( file_exists( get_template_directory() . '/vendor/autoload.php' ) ) {
	require get_template_directory() . '/vendor/autoload.php';
} else {
	/**
	 * Custom autoloader function for theme classes.
	 *
	 * @access private
	 *
	 * @param string $class_name Class name to load.
	 * @return bool True if the class was loaded, false otherwise.
	 */
	function _wp_rig_autoload( $class_name ) {
		$namespace = 'WP_Rig\WP_Rig';

		if ( 0 !== strpos( $class_name, $namespace . '\\' ) ) {
			return false;
		}

		$parts = explode( '\\', substr( $class_name, strlen( $namespace . '\\' ) ) );

		$path = get_template_directory() . '/inc';
		foreach ( $parts as $part ) {
			$path .= '/' . $part;
		}
		$path .= '.php';

		if ( ! file_exists( $path ) ) {
			return false;
		}

		require_once $path;

		return true;
	}
	spl_autoload_register( '_wp_rig_autoload' );
}

// Load the `wp_rig()` entry point function.
require get_template_directory() . '/inc/functions.php';

// Add custom WP CLI commands.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once get_template_directory() . '/wp-cli/wp-rig-commands.php';
}

// Initialize the theme.
call_user_func( 'WP_Rig\WP_Rig\wp_rig' );

/**
 * Inject Tiny LiveReload client when browsing through the modern dev proxy.
 * Primary signal is the X-WPRIG-DEV request header set by the proxy.
 * As a fallback, detect proxied requests via X-Forwarded-Host pointing to localhost:3000.
 * This does not rely on WP_DEBUG.
 */
if ( ! function_exists( 'wprig_is_dev_proxy_request' ) ) {
	function wprig_is_dev_proxy_request() {
		$has_custom_header = ! empty( $_SERVER['HTTP_X_WPRIG_DEV'] );
		$xfh = isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ? (string) $_SERVER['HTTP_X_FORWARDED_HOST'] : '';
		// Accept any localhost forwarded host regardless of port (supports custom devPort)
		$is_localhost_forward = ( false !== stripos( $xfh, 'localhost' ) ) || ( false !== stripos( $xfh, '127.0.0.1' ) );
		$has_cookie = isset( $_COOKIE['wprig_dev'] ) && $_COOKIE['wprig_dev'] === '1';
		//return true;
		return $has_custom_header || $is_localhost_forward || $has_cookie;
	}
}

add_action( 'wp_head', function () {
	if ( wprig_is_dev_proxy_request() ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static local URL for dev only
		echo "\n<script src=\"//localhost:35729/livereload.js?snipver=1\"></script>\n";
	}
} );
add_action( 'admin_head', function () {
	if ( wprig_is_dev_proxy_request() ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static local URL for dev only
		echo "\n<script src=\"http://localhost:35729/livereload.js?snipver=1\"></script>\n";
	}
} );

/**
 * Generate a sanitized ID from header text.
 *
 * @param string $text The header text to convert to an ID.
 * @return string The sanitized ID.
 */
function wprig_generate_heading_id( $text ) {
	// Use WordPress built-in sanitize_title function to create slug-like IDs
	return sanitize_title( $text );
}

/**
 * Add IDs to header tags in post content if they don't already have one.
 *
 * @param string $content The post content.
 * @return string The modified content with header IDs.
 */
function wprig_add_heading_ids( $content ) {
	// Check if we're on a single documentation page
	if ( !is_singular( 'page' ) || !has_block( 'wprig/documentation-template' ) ) {
		return $content;
	}

	// Use DOMDocument to parse and modify the HTML
	if ( !empty( $content ) ) {
		// Create a new DOMDocument
		$dom = new DOMDocument();

		// Preserve utf-8 encoding
		$dom->preserveWhiteSpace = false;

		// Load the content with specific options to handle UTF-8
		// Use LIBXML_HTML_NOIMPLIED to prevent adding html/body tags
		libxml_use_internal_errors( true ); // Suppress HTML5 parsing errors
		$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		// Track used IDs to avoid duplicates
		$used_ids = [];

		// Find all header tags (h1, h2, h3, etc.)
		foreach ( [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ] as $header_tag ) {
			$headers = $dom->getElementsByTagName( $header_tag );

			// Iterate through found headers
			foreach ( $headers as $header ) {
				// Check if the header already has an ID
				if ( !$header->hasAttribute( 'id' ) ) {
					// Get text content of the header
					$text = $header->textContent;

					// Generate an ID
					$id = wprig_generate_heading_id( $text );

					// Ensure the ID is unique
					$original_id = $id;
					$counter = 1;
					while ( in_array( $id, $used_ids ) ) {
						$id = $original_id . '-' . $counter;
						$counter++;
					}

					// Add the ID to the header
					$header->setAttribute( 'id', $id );

					// Add to used IDs list
					$used_ids[] = $id;
				} else {
					// Add the existing ID to the used list to prevent duplicates
					$used_ids[] = $header->getAttribute( 'id' );
				}
			}
		}

		// Save the modified HTML (removing the XML declaration)
		$content = preg_replace( '~<?xml encoding="utf-8" \?>~', '', $dom->saveHTML() );
	}

	return $content;
}

// Apply the function to post content before it's displayed
add_filter( 'the_content', 'wprig_add_heading_ids', 5 ); // Priority 5 ensures it runs before other content filters
