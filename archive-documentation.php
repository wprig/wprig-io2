<?php
/**
 * Template Name: Documentation Template
 * Template Post Type: post
 *
 * When active, by adding the heading above and providing a custom name
 * this template becomes available in a drop-down panel in the editor.
 *
 * Filename can be anything.
 *
 * @link https://developer.wordpress.org/themes/template-files-section/page-template-files/#creating-page-templates-for-specific-post-types
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;
use WP_Query;
get_header();

wp_print_styles( array( 'wprig-front-page' , 'wprig-content' , 'wprig-documentation-index', 'wprig-course-index' ) );

?>
	<main id="primary" class="site-main">
		<section class="documentation-header">
			<div class="documentation-tagline">
				Documentation
			</div>
			<div class="documentation-search">
				<?php get_search_form();?>
			</div>
		</section>
		<section class="documentation-categories">
			<?php
			$taxonomy = 'doc_cat';
			$terms = get_terms($taxonomy); // Get all terms of a taxonomy

			if ( $terms && !is_wp_error( $terms ) ) :
				?>
				<div class="cat-grid">
					<?php foreach ( $terms as $term ) { ?>
						<div class="cat-item"><a href="<?php echo get_term_link($term->slug, $taxonomy); ?>"><?php echo $term->name; ?></a></div>
					<?php } ?>
				</div>
			<?php endif;?>

		</section>
		<section class="posts-by-cat">
			<div class="entry-content">
				<?php
				$categories = get_terms( array(
					'taxonomy' => 'doc_cat',
				) );

				foreach ( $categories as $category ) {
					?>
					<div class="box-category">
						<a href="<?php echo get_term_link($category->slug, $taxonomy); ?>" class="doc-cat-link">
							<h2 class="doc-category-link"><?php echo $category->name;?></h2>
						</a>
					</div>

					<?php
					$docs = new WP_Query( array(
						'post_type' => 'documentation',
						'tax_query' => array(
							array(
								'terms'    => $category->term_id,
								'taxonomy' => 'doc_cat',
							),
						),
					) );

					if( $docs->have_posts() ) {
						while ( $docs->have_posts() ) : $docs->the_post();
							?>
							<article id="post-<?php the_ID(); ?>" class="doc-item">
								<a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark">
									<div class="doc-cta">
										<?php the_title( '<h3 class="doc-title" rel="bookmark">', '</h3>' ); ?>
									</div>
								</a>
							</article>

						<?php
						endwhile;
					}

					wp_reset_postdata();

				}
				?>
			</div>
		</section>
		<section class="doc-grid">
			<div class="entry-content">
				<?php
				while ( have_posts() ) {
					the_post();

					get_template_part( 'template-parts/content-documentation', get_post_type() );
				}
				?>
			</div>
		</section>
	</main><!-- #primary -->
	<?php
get_sidebar();
get_footer();
