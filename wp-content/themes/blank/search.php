<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package Shape
 * @since Shape 1.0
 */

get_header(); ?>

        <section id="primary" class="search-page">

            <div id="content" class="container site-content" role="main">

            <?php if ( have_posts() ) : ?>

                <header>
                    <h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'shape' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
                </header>
                
                <?php while ( have_posts() ) : the_post(); ?>

                        <div class="col-md-4">
                            <div class="search-result">
                                <a href="<?php the_permalink(); ?>">
                                    <h3> <?php the_title(); ?> <small> - <?php echo get_post_type(get_the_ID()); ?> </small> </h3> 
                                </a>
                            </div>
                        </div>

                <?php endwhile; ?>

            <?php else : ?>

                <header class="page-header">
                    <h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'shape' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
                </header><!-- .page-header -->

                <h1 class="no-results">No Results Found</h1>

            <?php endif; ?>

            </div><!-- #content .site-content -->

        </section><!-- #primary .content-area -->


<?php get_footer(); ?>