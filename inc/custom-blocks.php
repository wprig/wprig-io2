<?php
/**
 * Custom blocks registration and functionality.
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom blocks and block templates.
 */
function register_custom_blocks() {
	// Register the documentation template block
	if ( function_exists( 'register_block_type' ) ) {
		register_block_type(
			'wprig/documentation-template',
			array(
				'render_callback' => __NAMESPACE__ . '\render_documentation_template_block',
				'attributes'      => array(
					'className' => array(
						'type' => 'string',
					),
				),
			)
		);
	}
}
add_action( 'init', __NAMESPACE__ . '\register_custom_blocks' );

/**
 * Render the documentation template block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @return string  Rendered block HTML.
 */
function render_documentation_template_block( $attributes, $content ) {
	// Get post content
	global $post;
	$post_content = $post->post_content;

	// Get all headers from the content to build the "On This Page" menu
	$headers = array();
	$dom = new \DOMDocument();
	libxml_use_internal_errors( true ); // Suppress HTML5 parsing errors
	$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $post_content );
	libxml_clear_errors();

	// Get all header tags
	foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $header_tag ) {
		$elements = $dom->getElementsByTagName( $header_tag );

		foreach ( $elements as $element ) {
			$level = (int) substr( $header_tag, 1, 1 ); // Extract level number from tag name
			$text = $element->textContent;
			$id = $element->hasAttribute( 'id' ) ? $element->getAttribute( 'id' ) : '';

			// If no ID exists, generate one (should not happen as it's processed by wprig_add_heading_ids)
			if ( empty( $id ) ) {
				$id = wprig_generate_heading_id( $text );
			}

			$headers[] = array(
				'level' => $level,
				'text'  => $text,
				'id'    => $id,
			);
		}
	}

	// Start building the template
	ob_start();
	?>
	<div class="wprig-doc-single">
		<div class="doc-grid">
			<!-- Left sidebar - Documentation menu -->
			<div class="doc-left">
				<div class="menu">
					<h2>Documentation</h2>
					<?php
					// Left sidebar menu would go here - perhaps categories or related docs
					// This is a placeholder for now
					?>
					<div class="cat">
						<div class="cat-title">Getting Started</div>
						<ul>
							<li><a href="#">Introduction</a></li>
							<li><a href="#">Installation</a></li>
						</ul>
					</div>
				</div>
			</div>

			<!-- Center - Documentation content -->
			<div class="doc-center">
				<article>
					<?php echo apply_filters( 'the_content', $post_content ); ?>
				</article>
			</div>

			<!-- Right sidebar - "On This Page" menu -->
			<div class="doc-right">
				<div class="toc">
					<h2>On This Page</h2>
					<?php if ( ! empty( $headers ) ) : ?>
						<ul class="toc-list">
							<?php foreach ( $headers as $header ) : ?>
								<li data-level="<?php echo esc_attr( $header['level'] ); ?>">
									<a href="#<?php echo esc_attr( $header['id'] ); ?>"><?php echo esc_html( $header['text'] ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p>No sections found.</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Helper function to generate a sanitized ID from header text.
 * This is a copy of the function in functions.php to ensure it's available in the namespace.
 *
 * @param string $text The header text to convert to an ID.
 * @return string The sanitized ID.
 */
function wprig_generate_heading_id( $text ) {
	return sanitize_title( $text );
}
