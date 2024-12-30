<?php
/**
 * The template for displaying category archives.
 *
 * When active, applies to all category archives.
 * To target a specific category, rename file to category-{slug/id}.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#category
 *
 * @package wprig
 */

get_header(); ?>
<style>
.details a.follow strong, .faq-block > div, body {
  margin: 0;
  padding: 0;
}
.faq-block {
  max-width: 700px;
  width: 100%;
  height: 100%;
  float: left;
  padding: 15px;
  z-index: 10;
  background-color: white;
  overflow: hidden;
}
@media screen and (max-width: 599px) {
  .faq-block {
    max-width: 100%;
  }
}
.faq-block > div {
  display: block;
  position: relative;
  padding: 0 0 0 35px;
  color: black;
}
.faq-block > div:not(:last-of-type) {
  margin: 0 0 10px;
}
.faq-block > div:nth-child(1):before {
  content: "1";
  width: 20px;
  font-weight: bold;
  text-align: center;
  position: absolute;
  left: 0;
  top: 0;
  padding: 15px 7.5px;
  border-right: 1px solid rgba(128, 128, 128, 0.25);
  margin: 0;
  color: black;
}
.faq-block > div:nth-child(2):before {
  content: "2";
  width: 20px;
  font-weight: bold;
  text-align: center;
  position: absolute;
  left: 0;
  top: 0;
  padding: 15px 7.5px;
  border-right: 1px solid rgba(128, 128, 128, 0.25);
  margin: 0;
  color: black;
}
.faq-block > div:nth-child(3):before {
  content: "3";
  width: 20px;
  font-weight: bold;
  text-align: center;
  position: absolute;
  left: 0;
  top: 0;
  padding: 15px 7.5px;
  border-right: 1px solid rgba(128, 128, 128, 0.25);
  margin: 0;
  color: black;
}
.faq-block > div:nth-child(4):before {
  content: "4";
  width: 20px;
  font-weight: bold;
  text-align: center;
  position: absolute;
  left: 0;
  top: 0;
  padding: 15px 7.5px;
  border-right: 1px solid rgba(128, 128, 128, 0.25);
  margin: 0;
  color: black;
}
.faq-block > div:nth-child(5):before {
  content: "5";
  width: 20px;
  font-weight: bold;
  text-align: center;
  position: absolute;
  left: 0;
  top: 0;
  padding: 15px 7.5px;
  border-right: 1px solid rgba(128, 128, 128, 0.25);
  margin: 0;
  color: black;
}
.faq-block > div input + label {
  cursor: pointer;
  display: block;
  padding: 15px 15px;
  transition: all 0.25s ease-in-out 0.5s, color 0.25s ease-in-out 0.5s;
  color: black;
}
.faq-block > div input ~ div {
  visibility: hidden;
  max-height: 0;
  opacity: 0;
  transition: all 0.5s ease-in-out 0.2s, opacity 0.25s ease-in-out 0.25s, padding 0s ease-in-out 0s;
  width: calc(100% + 35px);
  margin-left: -35px;
}
.faq-block > div input ~ div p {
  padding: 15px;
  border-top: 1px solid rgba(128, 128, 128, 0.25);
}
.faq-block > div input:checked + label {
  transition: background-color 0s ease-in-out 0s;
  color: black;
}
.faq-block > div input:checked ~ div {
  display: block;
  opacity: 1;
  visibility: visible;
  max-height: 200px;
  transition: all 0.5s ease-in-out 0.2s, opacity 0.25s ease-in-out 0.5s, padding 0s ease-in-out 0s;
}

.details {
  width: calc(100% - 30px);
  float: right;
  margin: 15px;
  padding: 15px;
  background-color: #3399ff;
}
@media screen and (max-width: 599px) {
  .details {
    float: left;
    width: calc(100% - 30px);
    margin: 15px;
  }
}
.details * {
  display: inline-block;
  margin: 7.5px 0;
  line-height: 20px;
  color: white;
}
.details h1 {
  font-size: 36px;
  font-weight: bold;
  line-height: 40px;
  padding: 0;
  margin: 0;
  clear: both;
}
.details a {
  clear: both;
  font-family: "FontAwesome", Sans-Serif;
  font-size: 14px;
  text-decoration: none;
}
.details a.follow {
  float: left;
  font-weight: bold;
  background-color: #333333;
  margin: 15px 0 0;
  padding: 7.5px 15px;
  cursor: pointer;
}
.details a.follow:before {
  margin: 0 7.5px 0 0;
}
.details a.follow strong {
  color: #99ccff;
}
.details a.follow:hover {
  background-color: #66b3ff;
}
.details a.follow:hover strong {
  color: #4d4d4d;
}
</style>
<?php wp_print_styles( array( 'wprig-course-index' ) ); ?>
	<main id="primary" class="site-main">

	<?php
	if ( have_posts() ) :

		/* Display the appropriate header when required. */
		//wprig_index_header();
		WP_Rig\WP_Rig\wp_rig()->index_header();

		?>

		<section class="course-grid">

		<?php
		/* Start the Loop */
		while ( have_posts() ) :
			the_post();

			/*
			 * Include the component stylesheet for the content.
			 * This call runs only once on index and archive pages.
			 * At some point, override functionality should be built in similar to the template part below.
			 */
			wp_print_styles( array( 'wprig-content' ) ); // Note: If this was already done it will be skipped.

			/*
			 * Include the Post-Type-specific template for the content.
			 * If you want to override this in a child theme, then include a file
			 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
			 */
			get_template_part( 'template-parts/content', 'grid' );

		endwhile;

		?>

	</section><!-- .course-grid -->



		<?php
		the_posts_navigation();

	else :

		get_template_part( 'template-parts/content', 'none' );

	endif;
	?>

	</main><!-- #primary -->

<?php
get_sidebar();
get_footer();
