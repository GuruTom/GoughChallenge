<?php get_header(); ?>

<section class="page-404">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h1>404 Error - Page Not Found!</h1>
				<div class="icon"> <i class="fa fa-bug"></i> </div>
				<p>
					We cannot find the page you are looking for. It may have been removed or never existed. 
				</p>
				<p>
					Let's take you back to <a href="<?php echo bloginfo('url') ?>"> <i class="fa fa-home"></i> </a> or you can notify us <a href="<?php echo bloginfo('url') ?>/contact"> <i class="fa fa-envelope"></i> </a>
				</p>
			</div>
		</div>
	</div>
</section>


<?php get_footer(); ?>