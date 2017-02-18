<?php get_header(); ?>
<?php $dir = get_template_directory_uri(); ?>

<div class="home-page">

	<section class="intro">
		<div class="container">
			<div class="content">
				<h1>Gough Code Challenge</h1>
				<h2>By Thomas Withers</h2>
			</div>
			<div class="row">
				<div class="col-md-4 col-centered">
					<?php echo do_shortcode('[Calculator]'); ?>
				</div>
			</div>
		</div>
	</section>



</div>
<?php get_footer(); ?>
