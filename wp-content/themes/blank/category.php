<?php get_header(); ?>

<section class="breadcrumbs">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<a href="<?php echo home_url(); ?>/news">Blog</a>
				<a href="#"> <?php single_cat_title( '', true ); ?>  </a>
			</div>
		</div>
	</div>
</section>

<section class="news-archive">
		
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h2> <?php single_cat_title( '', true ); ?> </h2>
			</div>
		</div> <!-- End of row -->

		<div class="row">
			<div class="col-md-12">
				<?php wp_reset_query(); ?>
				<?php if( have_posts() ) : while( have_posts() ) : the_post(); ?>
					
					<article>
						<h2><a href="<?php the_permalink(); ?>"> <?php the_title(); ?>  </a></h2>
						<span>Posted on <?php the_time('D-m-Y'); ?> by <a href="#"> <?php the_author(); ?> </a></span>
						<p>
							<?php the_excerpt(); ?>
						</p>

						<a href="<?php the_permalink(); ?>" class="readmore">Read more <span>(+)</span></a>
					</article>

				<?php endwhile; else: ?>
					<h1>No Posts Found</h1>
				<?php endif; ?>
				<?php wp_reset_query(); ?>

			</div>

		</div> <!-- End of row -->
	</div> <!-- End of container -->

</section>

<section class="pagination">
	<div class="container">
		<?php echo paginate_links(); ?>
	</div>
</section>

<section class="page-end">
	<div class="wrapper container">
		<div class="row">
			<div class="col-md-12">
				<h3> <?php the_field('page_bottom_title', 'option'); ?> </h3>
				<p> <?php the_field('page_bottom_text', 'option'); ?> </p>
			</div>
		</div>
	</div>
</section>



<?php get_footer(); ?>