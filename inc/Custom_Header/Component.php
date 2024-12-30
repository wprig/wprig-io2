<?php
/**
 * WP_Rig\WP_Rig\Custom_Header\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Custom_Header;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use function add_action;
use function add_theme_support;
use function apply_filters;
use function get_header_textcolor;
use function get_theme_support;
use function display_header_text;
use function esc_attr;

/**
 * Class for adding custom header support.
 *
 * @link https://developer.wordpress.org/themes/functionality/custom-headers/
 */
class Component implements Component_Interface, Templating_Component_Interface {

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug(): string {
		return 'custom_header';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_action( 'after_setup_theme', array( $this, 'action_add_custom_header_support' ) );
	}

	/**
	 * Gets template tags to expose as methods on the Template_Tags class instance, accessible through `wp_rig()`.
	 *
	 * @return array Associative array of $method_name => $callback_info pairs. Each $callback_info must either be
	 *               a callable or an array with key 'callable'. This approach is used to reserve the possibility of
	 *               adding support for further arguments in the future.
	 */
	public function template_tags(): array {
		return array(
			'index_header'       => array( $this, 'index_header' ),
		);
	}

	/**
	 * Adds support for the Custom Logo feature.
	 */
	public function action_add_custom_header_support() {
		add_theme_support(
			'custom-header',
			apply_filters(
				'wp_rig_custom_header_args',
				array(
					'default-image'      => '',
					'default-text-color' => '000000',
					'width'              => 1600,
					'height'             => 250,
					'flex-height'        => true,
					'wp-head-callback'   => array( $this, 'wp_head_callback' ),
				)
			)
		);
	}

	/**
	 * Outputs extra styles for the custom header, if necessary.
	 */
	public function wp_head_callback() {
		$header_text_color = get_header_textcolor();

		if ( get_theme_support( 'custom-header', 'default-text-color' ) === $header_text_color ) {
			return;
		}

		if ( ! display_header_text() ) {
			echo '<style type="text/css">.site-title, .site-description { position: absolute; clip: rect(1px, 1px, 1px, 1px); }</style>';
			return;
		}

		echo '<style type="text/css">.site-title a, .site-description { color: #' . esc_attr( $header_text_color ) . '; }</style>';
	}

	public function index_header() {
		if ( is_home() && ! is_front_page() ) {
			?>
			<header>
				<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
			</header>
			<?php
		} elseif ( is_search() ) {
			?>
			<header class="page-header">
				<h1 class="page-title">
					<?php
					/* translators: %s: search query. */
					printf( esc_html__( 'Search Results for: %s', 'wprig' ), '<span>' . get_search_query() . '</span>' );
					?>
				</h1>
			</header><!-- .page-header -->
			<?php
		} elseif ( is_archive() ) {
			?>
			<header class="page-header">
				<?php
				the_archive_title( '<h1 class="page-title">', '</h1>' );
				the_archive_description( '<div class="archive-description">', '</div>' );
				?>
			</header><!-- .page-header -->
			<?php
		}
	}
}
