<?php get_header(); ?>

<section class="news-archive">
	
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h2>News Archive</h2>
			</div>
		</div> <!-- End of row -->
		
		<div class="row">
			<div class="col-md-8 col-sm-8">
				<?php $searchQuery = get_search_query(); ?>
				
				<?php if(strlen($searchQuery) > 0) { ?>
					<p class="search-notice"><?php printf( __( 'Search Results for: %s', 'shape' ), '<span>' . get_search_query() . '</span>' ); ?></p>
				<?php } ?>

				<?php wp_reset_query(); ?>
				<?php if( have_posts() ) : while( have_posts() ) : the_post(); ?>
				
					<article class="purple">
						<h2><a href="<?php the_permalink(); ?>"> <?php the_title(); ?>  </a></h2>
						<span>Posted on <?php the_time('D-m-Y'); ?> by <a href="#"> <?php the_author(); ?> </a></span>
						<p>
							<?php the_excerpt(); ?>
						</p>

						<a href="<?php the_permalink(); ?>" class="readmore">Read more <span>(+)</span></a>
					</article>

				<?php endwhile; else: ?>
					<p class="no-result">No results found for this search</p>
				<?php endif; ?>
				<?php wp_reset_query(); ?>
			</div> <!-- End of col 8 -->

			<div class="col-md-3 col-sm-3 col-md-offset-1 sidebar">
				<div class="section">
					<div class="search-bar">
						<form role="search" method="get" class="search-form" action="<?php echo home_url( '/news' ); ?>">
							<label>
								<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search …', 'placeholder' ) ?>" value="<?php echo get_search_query() ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'label' ) ?>" />
								<button><span> <i class="fa fa-search"></i> </span></button>
							</label>
						</form>
					</div>
				</div>
				<div class="section">
					<h4>Categories</h4>

					<?php 
						    $args = array(
							'show_option_all'    => '',
							'orderby'            => 'name',
							'order'              => 'ASC',
							'style'              => 'list',
							'show_count'         => 0,
							'hide_empty'         => 1,
							'use_desc_for_title' => 1,
							'child_of'           => 0,
							'feed'               => '',
							'feed_type'          => '',
							'feed_image'         => '',
							'exclude'            => '',
							'exclude_tree'       => '',
							'include'            => '',
							'hierarchical'       => 1,
							'title_li'           => __( '' ),
							'show_option_none'   => __( '' ),
							'number'             => null,
							'echo'               => 1,
							'depth'              => 0,
							'current_category'   => 0,
							'pad_counts'         => 0,
							'taxonomy'           => 'category',
							'walker'             => null
						    );
						    wp_list_categories( $args ); 
					?>
				</div>

			</div> <!-- End of col 4 -->


		</div> <!-- End of row-->
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