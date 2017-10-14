<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package new_theme_name
 */

?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<div class="site-info">
			<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'new_theme_name' ) ); ?>"><?php
				/* translators: %s: CMS name, i.e. WordPress. */
				printf( esc_html__( 'Proudly powered by %s', 'new_theme_name' ), 'WordPress' );
			?></a>
			<span class="sep"> | </span>
			<?php
				/* translators: 1: Theme name, 2: Theme author. */
				printf( esc_html__( 'Theme: %1$s by %2$s.', 'new_theme_name' ), 'new_theme_name', '<a href="http://underscores.me/">Underscores.me</a>' );
			?>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

<!--[if lt IE 9]>
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/libs/html5shiv/es5-shim.min.js"></script>
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/libs/html5shiv/html5shiv.min.js"></script>
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/libs/html5shiv/html5shiv-printshiv.min.js"></script>
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/libs/respond/respond.min.js"></script>
<![endif]-->

</body>
</html>