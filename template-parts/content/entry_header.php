<?php
/**
 * Template part for displaying a post's header
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;
global $post;
?>

<header class="entry-header<?php echo (has_post_thumbnail() ? ' has-thumb' : '')?>">

	<?php if ( has_post_thumbnail() ) { ?>
	<div class="entry-thumbnail">
		<?php get_template_part( 'template-parts/content/entry_thumbnail', get_post_type() ); ?>
	</div>
	<?php } ?>

	<div class="entry-top-details">
	<?php
	get_template_part( 'template-parts/content/entry_title', get_post_type() );
	if($post->post_type !== 'page'){
		get_template_part( 'template-parts/content/entry_meta', get_post_type() );
	}
	?>
	</div>
</header><!-- .entry-header -->
