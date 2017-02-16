<?php get_header(); ?>

<section class="breadcrumbs">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<a href="<?php echo home_url(); ?>/news">Blog</a>
				<a href="#"> <?php the_title(); ?> </a>
			</div>
		</div>
	</div>
</section>

<section class="single">
	
	<div class="container">
		<div class="row">
			<div class="col-md-12 single-post">
			
			<h1> <?php the_title(); ?> </h1>

			<?php while( have_posts() ) : the_post(); ?>
				<small> Posted on <?php the_time('d-m-Y'); ?> </small>
				<p> <?php the_content(); ?> </p>
			<?php endwhile; ?>

			</div>

		</div> <!-- End of row -->
	</div> <!-- End of container -->

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