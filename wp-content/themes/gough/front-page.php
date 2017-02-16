<?php get_header(); ?>
<?php $dir = get_template_directory_uri(); ?>

<section class="landing-page">
	<div class="hero parallax-window" data-parallax="scroll" data-image-src="<?php the_field('header_image'); ?>">
		<div class="container">
			
			<div class="nav-wrapper">
				<div class="nav pull-right">
					<div class="slider-menu">
						<ul class="list-inline list-unstyled">
							<li><a href="#" data-scrollto="#about">About</a></li>
							<li><a href="#" data-scrollto="#buy">Buy</a></li>
							<li><a href="#" data-scrollto="#author">Author</a></li>
						</ul>
					</div>
					<div class="dropdown">
						<div class="inner">
							<img src="<?php echo $dir; ?>/img/dropdown.png"/>
						</div>
					</div>
				</div>
			</div>


			<img src="<?php the_field('logo'); ?>" class="logo img-responsive"/>
			
			<div class="row content">
				<div class="col-md-6 col-sm-6"></div>
				<div class="col-md-6 col-sm-6">
					<p><?php the_field('content'); ?></p>
				</div>
			</div>
		</div>
	</div>
	<div class="dotted-bg parallax-window" data-parallax="scroll" data-image-src="<?php the_field('texture_image'); ?>">
		<div class="pizza-slider" id="about">
			<div class="container">
				<div class="row">
					<div class="col-md-6 col-sm-6">
						<div class="pizza-for">
							<?php if(have_rows('slider')): ?>
								<?php while(have_rows('slider')): the_row(); ?>
									<div><img src="<?php the_sub_field('image'); ?>" class="img-responsive"/></div>
								<?php endwhile; ?>
							<?php endif; ?>
						</div>
					</div>
					<div class="col-md-6 col-sm-6">
						<div class="pizza-nav">
							<?php if(have_rows('slider')): ?>
								<?php while(have_rows('slider')): the_row(); ?>
									<div>
										<?php if(get_sub_field('content')) the_sub_field('content'); ?>
									</div>
								<?php endwhile; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="buy" id="buy">
			<div class="container">
				<div class="row">
					<div class="col-md-6 col-sm-6">
						<img src="<?php echo $dir; ?>/img/copytext.png" class="img-responsive first"/>
					</div>
					<div class="col-md-6 col-sm-6">
						<img src="<?php echo $dir; ?>/img/copybook.png" class="img-responsive buy-image"/>
					</div>
				</div>
			</div>
		</div>
		<div class="contact">
			<div class="container">
				<?php echo do_shortcode('[contact-form-7 id="4" title="Contact form 1"]'); ?>
			</div>
		</div>
		<div class="author text-center" id="author">
			<div class="container">
				<?php if(get_field('author_text')) the_field('author_text'); ?>
				<p class="owner">Sian Lenegan <span>Founder, Sixth Story</span></p>
			</div>
		</div>
		<div class="footer">
			<a href="http://sixthstory.co.uk" target="_blank">
				<img src="<?php echo $dir ?>/img/footer.png" class="img-responsive"/>
				<p>www.sixthstory.co.uk</p>
			</a>

			<div class="social-images">
				<a href="https://twitter.com/sixth_story?lang=en" target="_blank"><img src="<?php echo $dir; ?>/img/icon_twitter.png" data-hover="<?php echo $dir; ?>/img/icon_twitter_h.png"></a>
				<a href="https://instagram.com/sixthstory/" target="_blank"><img src="<?php echo $dir; ?>/img/icon_instagram.png" data-hover="<?php echo $dir; ?>/img/icon_instagram_h.png"></a>
				<a href="https://www.facebook.com/SixthStory/" target="_blank"><img src="<?php echo $dir; ?>/img/icon_facebook.png" data-hover="<?php echo $dir; ?>/img/icon_facebook_h.png"></a>
			</div>

			<p class="copyright">Copyright &copy; <?php echo date("Y"); ?> Content Pizza</p>

		</div>
	</div>
</section>



<?php get_footer(); ?>