<?php 
namespace theme;
?>

        <!-- 
            @link microformats.org/wiki/hatom
            @link microformats.org/wiki/hcard
            @link microformats.org/wiki/hentry
            @link stackoverflow.com/a/7295013/770127
        -->
        <section class="hfeed">
            
            <?php get_template_part( 'loop-meta' ); ?>

            <?php if ( have_posts() ) : ?>

                <?php while ( have_posts() ) : the_post(); ?>

                    <?php do_action( '@before_entry' ); ?>

                    <article<?php echo rtrim(' ' . apply_filters( '@entry_attributes', 'class="hentry"' ) ); ?>>

                        <?php do_action( '@open_entry' ); ?>
                        
                        <header>
                            <h1 class="entry-title">
                                <a rel="bookmark" href="<?php the_permalink(); ?>" title="Permalink &raquo;"><?php the_title(); ?></a>
                            </h1>
                            <div class="byline">
                                <address class="author vcard">
                                    <a rel="author" class="url n" href="<?php the_author(); ?>" title="<?php the_author(); ?>"><?php the_author(); ?></a>
                                </address>
                                <time datetime="<?php the_time('Y-m-d'); ?>" title="<?php echo get_the_date(); ?>"><?php echo get_the_date(); ?></time>
                            </div>
                        </header>

                        <?php if ( is_singular() ) { ?>
                        <div class="entry-content">
                            <?php the_content( __('continue &rarr;') ); ?>
                            <?php wp_link_pages(); ?>
                        </div><!-- /.entry-content -->
                        
                        <?php } else { ?>
                        <div class="entry-summary">
                            <?php the_excerpt(); ?>
                        </div><!-- /.entry-summary -->
                        <?php } ?>
                        
                        <?php is_singular() and comments_template( '/comments.php', true ); ?>

                        <?php do_action( '@close_entry' ); ?>

                    </article><!-- /.hentry -->
                    
                    <?php do_action( '@after_entry' ); ?>

                <?php endwhile; ?>

            <?php else : ?>

                <?php get_template_part( 'loop-error' ); ?>

            <?php endif; ?>

        </section><!-- .hfeed -->

        <?php get_template_part( 'loop-nav' ); ?>
        
