<?php
/**
 * Single template for Documentation (CPT: documentation)
 *
 * 3-column layout similar to Swagger docs:
 * - Left: Menu of all published documentation posts organized by category (taxonomy: doc_cat)
 * - Center: Current post content
 * - Right: On-page table of contents generated from headings with IDs in the content
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

use WP_Query;

get_header();

// Ensure core content styles are printed if registered by the theme.
if ( function_exists( '\\WP_Rig\\WP_Rig\\wp_rig' ) ) {
	// If the theme exposes the template tag for styles, prefer that.
	wp_rig()->print_styles( 'wprig-content' );
} else {
	// Fallback to direct print if not available for some reason.
	wp_print_styles( array( 'wprig-content' ) );
}

/**
 * Extract an array of headings with IDs from a block of HTML.
 * Each item: [ 'id' => string, 'text' => string, 'level' => int (1..6) ]
 *
 * @param string $html
 * @return array<int,array{id:string,text:string,level:int}>
 */
function wprig_doc_extract_headings_with_ids( $html ) {
	$items = array();

	if ( empty( $html ) || ! is_string( $html ) ) {
		return $items;
	}

	// Load HTML with DOMDocument. Suppress warnings for malformed HTML from WP content.
	$internal_errors = libxml_use_internal_errors( true );
	$dom             = new \DOMDocument();

	// Ensure proper encoding handling.
	$wrapped = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $html . '</body></html>';
	$dom->loadHTML( $wrapped );

	$xpath = new \DOMXPath( $dom );
	for ( $level = 1; $level <= 6; $level++ ) {
		$nodes = $xpath->query( '//h' . $level . '[@id]' );
		if ( ! $nodes ) {
			continue;
		}
		foreach ( $nodes as $node ) {
			/** @var \DOMElement $node */
			$id   = trim( (string) $node->getAttribute( 'id' ) );
			$text = trim( (string) $node->textContent );
			if ( '' === $id || '' === $text ) {
				continue;
			}
			$items[] = array(
				'id'    => $id,
				'text'  => $text,
				'level' => (int) $level,
			);
		}
	}

	// Restore libxml error setting.
	libxml_clear_errors();
	libxml_use_internal_errors( $internal_errors );

	return $items;
}

?>
<main id="primary" class="site-main">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
		$current_post_id = get_the_ID();
		$content_html    = apply_filters( 'the_content', get_the_content() );
		$toc_items       = wprig_doc_extract_headings_with_ids( $content_html );
		$taxonomy        = 'doc_cat';
		?>

		<style>
			/* Scoped styles for documentation single layout */
			.wprig-doc-single { --gap: 24px; --border: #e5e7eb; --muted: #6b7280; }
			.wprig-doc-single .doc-grid { display: grid; grid-template-columns: 1fr; gap: var(--gap); align-items: start; }
			.wprig-doc-single .doc-left, .wprig-doc-single .doc-right { background: #fff; border: 1px solid var(--border); border-radius: 8px; padding: 16px; }
			.wprig-doc-single .doc-left, .wprig-doc-single .doc-right { position: relative; }
			.wprig-doc-single .doc-left .menu, .wprig-doc-single .doc-right .toc { max-height: calc(100vh - 160px); overflow: auto; position: sticky; top: 80px; }
			.wprig-doc-single .doc-center article { background: #fff; border: 1px solid var(--border); border-radius: 8px; padding: 24px; }
			.wprig-doc-single .doc-left h2, .wprig-doc-single .doc-right h2 { font-size: 14px; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); margin: 0 0 8px; }
			.wprig-doc-single .doc-left .cat { margin: 16px 0 0; }
			.wprig-doc-single .doc-left .cat-title { font-size: 13px; font-weight: 600; margin: 8px 0; color: #111827; }
			.wprig-doc-single .doc-left ul { list-style: none; padding: 0; margin: 0; }
			.wprig-doc-single .doc-left li { margin: 0; }
			.wprig-doc-single .doc-left a { display: block; padding: 6px 8px; border-radius: 6px; color: #1f2937; text-decoration: none; }
			.wprig-doc-single .doc-left a:hover { background: #f3f4f6; }
			.wprig-doc-single .doc-left a.is-current { background: #111827; color: #fff; }

			.wprig-doc-single .doc-right .toc-list { list-style: none; padding: 0; margin: 0; }
			.wprig-doc-single .doc-right .toc-list li { margin: 2px 0; }
			.wprig-doc-single .doc-right .toc-list a { display: block; padding: 4px 6px; color: #1f2937; text-decoration: none; border-radius: 4px; }
			.wprig-doc-single .doc-right .toc-list a:hover { background: #f3f4f6; }
			.wprig-doc-single .doc-right .toc-list li[data-level="2"] { margin-left: 0; }
			.wprig-doc-single .doc-right .toc-list li[data-level="3"] { margin-left: 10px; }
			.wprig-doc-single .doc-right .toc-list li[data-level="4"] { margin-left: 18px; }
			.wprig-doc-single .doc-right .toc-list li[data-level="5"] { margin-left: 26px; }
			.wprig-doc-single .doc-right .toc-list li[data-level="6"] { margin-left: 34px; }

			@media (min-width: 1024px) {
				.wprig-doc-single .doc-grid { grid-template-columns: 280px minmax(0, 1fr) 300px; }
			}
		</style>

		<section class="wprig-doc-single">
			<div class="doc-grid">
				<aside class="doc-left" aria-label="Documentation navigation">
					<h2><?php echo esc_html__( 'Docs', 'wp-rig' ); ?></h2>
					<nav class="menu" role="navigation">
						<?php
						$terms = get_terms( array(
							'taxonomy'   => $taxonomy,
							'hide_empty' => true,
							'orderby'    => 'name',
							'order'      => 'ASC',
						) );
						if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) :
							foreach ( $terms as $term ) :
								?>
								<div class="cat">
									<div class="cat-title"><?php echo esc_html( $term->name ); ?></div>
									<?php
									$docs = new WP_Query( array(
										'post_type'      => 'documentation',
										'posts_per_page' => -1,
										'orderby'        => 'title',
										'order'          => 'ASC',
										'tax_query'      => array(
											array(
												'taxonomy' => $taxonomy,
												'field'    => 'term_id',
												'terms'    => (int) $term->term_id,
											),
										),
									) );
									if ( $docs->have_posts() ) :
										echo '<ul>';
										while ( $docs->have_posts() ) : $docs->the_post();
											$link   = get_permalink();
											$title  = get_the_title();
											$is_cur = ( get_the_ID() === $current_post_id );
											?>
											<li>
												<a class="<?php echo $is_cur ? 'is-current' : ''; ?>" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
											</li>
											<?php
										endwhile;
										echo '</ul>';
									endif;
									wp_reset_postdata();
									?>
								</div>
								<?php
							endforeach;
						else :
							echo '<p>' . esc_html__( 'No documentation categories found.', 'wp-rig' ) . '</p>';
						endif;
						?>
					</nav>
				</aside>

				<div class="doc-center">
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<header class="entry-header">
							<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
						</header>
						<div class="entry-content">
							<?php the_content(); ?>
						</div>
					</article>
				</div>

				<aside class="doc-right" aria-label="On this page">
					<h2><?php echo esc_html__( 'On this page', 'wp-rig' ); ?></h2>
					<nav class="toc" role="navigation">
						<?php if ( ! empty( $toc_items ) ) : ?>
							<ul class="toc-list">
								<?php foreach ( $toc_items as $item ) : ?>
									<li data-level="<?php echo (int) $item['level']; ?>">
										<a href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<p class="muted"><?php echo esc_html__( 'No headings found in this article.', 'wp-rig' ); ?></p>
						<?php endif; ?>
					</nav>
				</aside>
			</div>
		</section>

	<?php endwhile; endif; ?>
</main><!-- #primary -->
<?php
get_footer();
