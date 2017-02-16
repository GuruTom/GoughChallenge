<?php get_header(); ?>

	<section class="sub-page">

		<div class="container">

			<?php if ( have_posts() ): while ( have_posts() ) : the_post(); ?>

				<h2><?php the_title(); ?></h2>

				<div class="content">

					<?php the_content(); ?>

				</div>

			<?php endwhile; endif; ?>

		</div>

	</section>


<?php get_footer(); ?>