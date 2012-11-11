<?php
namespace theme;

# markup to display at the top of the .hfeed ( see loop.php )
?>
	<?php if ( is_home() && !is_front_page() ) : ?>

		<?php global $wp_query; ?>

		<div class="loop-meta">
			<h1 class="loop-title"><?php echo get_post_field( 'post_title', $wp_query->get_queried_object_id() ); ?></h1>
		</div><!-- .loop-meta -->

	<?php elseif ( is_tag() || is_tax() || is_category() ) : ?>

		<div class="loop-meta archive-info taxonomy-info">

			<h1 class="loop-title archive-title taxonomy-title"><?php single_term_title(); ?></h1>

			<div class="loop-description archive-description taxonomy-description">
				<?php echo term_description( '', get_query_var( 'taxonomy' ) ); ?>
			</div><!-- .loop-description -->

		</div><!-- .loop-meta -->

	<?php elseif ( is_author() ) : ?>

		<?php $id = get_query_var( 'author' ); ?>

		<div id="hcard-<?php the_author_meta( 'user_nicename', $id ); ?>" class="loop-meta vcard archive-info user-info">

			<h1 class="loop-title fn n archive-title user-title"><?php the_author_meta( 'display_name', $id ); ?></h1>

			<div class="loop-description archive-description user-description">
				<?php echo get_avatar( get_the_author_meta( 'user_email', $id ), '100', '', get_the_author_meta( 'display_name', $id ) ); ?>

				<p class="user-bio">
					<?php the_author_meta( 'description', $id ); ?>
				</p><!-- .user-bio -->
			</div><!-- .loop-description -->

		</div><!-- .loop-meta -->

	<?php elseif ( is_search() ) : ?>

		<div class="loop-meta search-info">

			<h1 class="loop-title search-title"><?php echo esc_attr( get_search_query() ); ?></h1>

			<div class="loop-description search-description">
				<p>
				<?php printf( __( 'You are browsing the search results for &quot;%1$s&quot;', 'hybrid' ), esc_attr( get_search_query() ) ); ?>
				</p>
			</div><!-- .loop-description -->

		</div><!-- .loop-meta -->

	<?php elseif ( is_date() ) : ?>

		<div class="loop-meta archive-info date-info">
			<h1 class="loop-title archive-title date-title"><?php _e( 'Archives by date', 'hybrid' ); ?></h1>

			<div class="loop-description archive-description date-description">
				<p>
				<?php _e( 'You are browsing the site archives by date.', 'hybrid' ); ?>
				</p>
			</div><!-- .loop-description -->

		</div><!-- .loop-meta -->

	<?php elseif ( is_post_type_archive() ) : ?>

		<?php $post_type = get_post_type_object( get_query_var( 'post_type' ) ); ?>

		<div class="loop-meta archive-info">

			<h1 class="loop-title archive-title"><?php post_type_archive_title(); ?></h1>

			<div class="loop-description archive-description">
				<?php if ( !empty( $post_type->description ) ) echo "<p>{$post_type->description}</p>"; ?>
			</div><!-- .loop-description -->

		</div><!-- .loop-meta -->

	<?php elseif ( is_archive() ) : ?>

		<div class="loop-meta archive-info">

			<h1 class="loop-title archive-title"><?php _e( 'Archives', 'hybrid' ); ?></h1>

			<div class="loop-description archive-description">
				<p>
				<?php _e( 'You are browsing the site archives.', 'hybrid' ); ?>
				</p>
			</div><!-- .loop-description -->

		</div><!-- .loop-meta -->

	<?php endif; ?>