<?php
/**
 * @link     actiontheme.com
 * @author   Ryan Van Etten
 * @license  MIT
 */

# Loads via functions.php
# All functions defined in this file (and theme) are anonymous.
# Use the generic namespace "theme" as redundant protection
# against name conflicts with WP core, plugins, or native PHP.
namespace theme;

# Hooks created by the theme are prefixed with the '@' symbol 
# as to not conflict with hooks created by the WordPress core.
# @link  codex.wordpress.org/Plugin_API/Action_Reference
# @link  codex.wordpress.org/Function_Reference/add_action
# @link  codex.wordpress.org/Function_Reference/add_filter

# $content_width is required per codex.wordpress.org/Theme_Review
# codex.wordpress.org/Content_Width
# bit.ly/content-width-zero
# Use WP -> Settings -> Media
isset( $content_width ) or $content_width = get_option( 'large_size_w' );
\is_numeric( $content_width ) or $content_width = 1024;

# Use the generic 'theme' as the textdomain such that it is easier to 
# repurpose code in other themes.
# @link  codex.wordpress.org/I18n_for_WordPress_Developers
# @link  ottopress.com/2012/internationalization-youre-probably-doing-it-wrong/
# @link  markjaquith.wordpress.com/2011/10/06/translating-wordpress-plugins-and-themes-dont-get-clever/
# todo: load textdomain


# Basic contextual support.
add_filter('body_class', function($array) {
    return \array_unique( \array_merge($array, array(
        is_child_theme() ? 'child-theme' : 'parent-theme'
      , is_singular() ? 'singular' : 'plural'
    )));
});

# Actions to be run on the 'after_setup_theme' hook:
add_action('after_setup_theme', function() {
    \defined('WP_DEBUG') && WP_DEBUG or remove_action( 'wp_head', 'wp_generator' ); # tighten security
    add_theme_support( 'automatic-feed-links' ); # required
    add_theme_support( 'post-thumbnails' ); # "featured image"
    add_editor_style(); # codex.wordpress.org/Function_Reference/add_editor_style
}, 0);

add_filter('@html_tag', function() {
    # Emulate language_attributes() b/c it has no "get" version.
    # Include its 'language_attributes' filter for plugin usage.
    $attrs = 'dir="' . (is_rtl() ? 'rtl' : 'ltr') . '" lang="' . get_bloginfo('language') . '"';
    $attrs = apply_filters( 'language_attributes', $attrs );
    $attrs = apply_filters( '@html_attrs', $attrs );
    return "<html $attrs>";
}, 0);

add_action('@body', apply_filters('@body_actions', function() {

    static $ran; # prevent from running more than once
    if ($ran = null !== $ran) return;

    add_action('@body', 'get_header' , 5);
    add_action('@body', function() {
        include locate_template('main.php', false, false);
    }, 10);
    add_action('@body', 'get_sidebar', 20);
    add_action('@body', 'get_footer' , 30);
}), 0);

add_action('@header', function() {
    locate_template( 'branding.php', true, false );
}, apply_filters('@branding_priority', 10));

add_action('@header', function() {

    $skip = '<a href="#main" accesskey="5">' . __('Skip', 'theme') . '</a>';
    $skip = apply_filters( '@menu_skip_anchor', $skip );
    $skip and $skip = \trim( \strip_tags( $skip, '<a>' ) );
    $skip = $skip ? '<li class="assistive focusable">' . $skip . '</li>' : '';
    
    $attrs = 'id="menu" role="navigation" class="site-nav invert-anchors"';
    $attrs = apply_filters( '@menu_attrs', $attrs );

    echo apply_filters('@menu', \str_repeat( ' ', 8 )
      . "<nav $attrs><h2 class='assistive menu-toggle'>Menu</h2>"
      . wp_nav_menu(array(
            'theme_location' => 'menu'
          , 'container'      => false
          , 'echo'           => false
          , 'menu_class'     => 'nav'
          , 'items_wrap'     => '<ul>' . $skip . '%3$s</ul>'
    )) . '</nav>' . "\n\n");

}, apply_filters( '@menu_priority', 10));

add_action('@header', function() {
    is_active_sidebar('header') and get_sidebar('header');
});

add_action('@footer', function() {
    is_active_sidebar('footer') and get_sidebar('footer');
});

add_action( 'widgets_init', function() {
    $areas = (array) apply_filters( '@widget_areas', array(
        array( 'id' => 'sidebar', 'name' => '.sidebar-widget-area' )
      , array( 'id' => 'header' , 'name' => '.header-widget-area' )
      , array( 'id' => 'footer' , 'name' => '.footer-widget-area' )
    ));
    # codex.wordpress.org/Function_Reference/register_sidebar
    # Merge in sensible defaults:
    foreach ($areas as $a) {
        $a and register_sidebar( \array_merge(array(
            'before_widget' => '<li class="widget %2$s">'
        ), $a));
    }
});

add_action('init', function() {
    register_nav_menus(array('menu' => 'Menu'));
});

add_action('@main', function() {
    # insert the loop into [role="main"]
    # codex.wordpress.org/Function_Reference/get_template_part
    get_template_part( 'loop', is_singular() ? 'singular' : 'plural' ); #wp
}, apply_filters('@loop_priority', 10));

add_filter('previous_posts_link_attributes', function( $attrs = '' ) {
    $attrs or $attrs = '';
    if ( ! \is_string($attrs) )
        return $attrs;
    $attrs = (array) $attrs;
    $attrs[] = 'rel="prev" class="prev"';
    return \implode( ' ', $attrs );
});

add_filter('next_posts_link_attributes', function( $attrs = '' ) {
    $attrs or $attrs = '';
    if ( ! \is_string($attrs) )
        return $attrs;
    $attrs = (array) $attrs;
    $attrs[] = 'rel="next" class="next"';
    return \implode( ' ', $attrs );
});

add_action('@loop', function() {

    static $ran; # prevent from running more than once
    if ($ran = null !== $ran) return;
    
    add_action('@loop', function() {
        # codex.wordpress.org/Function_Reference/locate_template
        is_singular() or locate_template( 'loop-header.php', true, false );
    }, 5);

    add_action('@loop', function() {
        # the actual loop
        if ( ! have_posts() )
            locate_template( 'loop-empty.php', true, false );
        else for ( $path = locate_template( 'entry.php', false, false ); have_posts(); ) {
            the_post();
            include $path;
        }
    }, 10);

    add_action('@loop', function() {
        # codex.wordpress.org/Function_Reference/locate_template
        locate_template( 'loop-nav.php', true, false );
    }, 20);
    
    add_action('@loop_nav', apply_filters('@loop_nav_actions', is_singular() ? function() {
        previous_post_link( '%link' );
        next_post_link( '%link' );
    } : function() {
        $prev = '<span>' . __('Prev', 'theme') . '</span>';
        $next = '<span>' . __('Next', 'theme') . '</span>';
        posts_nav_link( ' ', $prev, $next );
    }));
}, 0);

add_action('@entry', apply_filters('@entry_actions', function() {

    static $ran; # prevent from running more than once
    if ( $ran = null !== $ran ) return;
    
    # allow the '@content_mode' to be changed between iterations
    # truthy => content | falsey => excerpt
    $content_mode = apply_filters( '@content_mode', is_singular() );
    
    $content_mode or current_theme_supports( 'post-thumbnails' ) && add_filter('@thumbnail', function() {
        if ( $size = apply_filters( '@thumbnail_size', 'thumbnail' ) )
            if ( $img = get_the_post_thumbnail( null, $size, array( 'itemprop' => 'image' ) ) )
                return ( $url = get_permalink() ) && \strip_tags( $img, '<img>' ) === $img
                    ? "<a itemprop='url' rel='bookmark' href='$url'>$img</a>" : $img;
    }, 0);

    add_action('@entry', function() {# insert entry-header.php
        include locate_template( 'entry-header.php', false, false );
    }, 5);

    add_action('@entry', $content_mode ? function() {
        include locate_template( 'entry-content.php', false, false );
    } : function() {
        static $summ; # cache path b/c repeated calls are likely here
        include $summ = $summ ?: locate_template( 'entry-summary.php', false, false );
    }, 10);

    $content_mode and add_action('@entry', function() {
        include locate_template( 'entry-footer.php', false, false );
    }, 15);
    
    is_singular() and add_action('@entry', function() {
        # codex.wordpress.org/Function_Reference/comments_template
        comments_template( '/comments.php', true );
    }, 20);
}), 0);

add_filter('@entry_attrs', function( $attrs = '' ) {
    $class = \implode( ' ', get_post_class() );
    return "class='$class' itemscope itemtype='http://schema.org/Article'";
}, 0);

add_filter('post_class', function( $arr = array() ) {
    $arr = (array) $arr;
    # This compares using the format from: Settings > General > Date Format
    # Maybe we should use Y-m-d or provide a filter like:
    # $format = apply_filters( '@time_compare_format', '' );
    $arr[] = get_the_date() === get_the_modified_date() ? 'unrevised' : 'revised';
    return \array_unique( $arr );
});

add_action('@entry_header', function() {
    echo apply_filters( '@thumbnail', null );
    $markup  = '<h1 class="entry-title">';
    $markup .= '<a itemprop="url" rel="bookmark" href="' . get_permalink() . '">';
    $markup .= '<span class="headline name">' . get_the_title() . '</span></a></h1>';
    echo apply_filters( '@headline', $markup );
}, 5);

add_action('@entry_header', function() {

    global $authordata;        
    $markup = "<dl class='byline meta-list'>";
    
    \is_object( $authordata )
        and ( $link = get_author_posts_url( $authordata->ID, $authordata->user_nicename ) ) # href
        and ( $link = "<a href='$link' class='url fn n' itemprop='author' rel='author'>" . get_the_author() .'</a>' )
        and ( $link = apply_filters( 'the_author_posts_link', $link ) ) #wp: the_author_posts_link()
        and $markup .= '<dt class="author-label">' . __('By', 'theme') . '</dt><dd class="author-value vcard">' . $link . '</dd>';

    $time_item = function( $arr ) {
        \extract( $arr );
        $date = \call_user_func( $fn ); # Settings > General > Date Format
        $ymd = \call_user_func( $fn, 'Y-m-d' );
        $idx = get_year_link( \strtok( $ymd, '-' ) );
        $rel and $rel = ' rel="index"';
        $date = "<a$rel href='$idx'>$date</a>";
        $tag = "<time itemprop='$itemprop' class='$class' datetime='$ymd'>$date</time>";
        $tag = apply_filters( '@' . $hook . '_tag', $tag, $date );
        $item = '<dt class="' . $hook . '-label time-label">' . $label . '</dt>';
        return $item . '<dd class="' . $hook . '-value time-value">' . $tag . '</dd>';
    };
    
    # microformats.org/wiki/hentry
    # schema.org/Article
    # github.com/ryanve/action/issues/1
        
    $markup .= $time_item(array( 
           'fn' => 'get_the_date'
         , 'itemprop' => 'datePublished'
         , 'label' => 'Posted'
         , 'class' => 'published'
         , 'hook' => 'published'
         , 'rel' => 'index' 
    ));
    
    $markup .= $time_item(array(
        'fn' => 'get_the_modified_date'
      , 'itemprop' => 'dateModified'
      , 'label' => 'Updated'
      , 'class' => 'updated'
      , 'hook' => 'modified'
      , 'rel' => '' 
    ));
        
    $markup .= '</dl>';
    echo apply_filters( '@byline', $markup );
}, 7);

add_action ('@entry_footer', function() {

    global $wp_taxonomies;
    static $taxos;

    echo apply_filters('@entry_pages', wp_link_pages(array(
        'echo'   => 0
      , 'before' => '<nav class="entry-pages meta-list"><h4 class="meta-label pages-label">' 
                    . __('Pages', 'theme') . '</h4><div class="meta-value pages-value">'
      , 'after'  => '</div></nav>'
    )));

    isset( $taxos ) or $taxos = \wp_list_pluck( $wp_taxonomies, 'label' );
    $id    = get_the_ID();
    $type  = get_post_type( $id );   
    $markup = '';

    foreach ( $taxos as $name => $label ) {
        if ( is_object_in_taxonomy($type, $name) ) {
            if ( $class = sanitize_html_class( \mb_strtolower($label) ) ) {
                $terms = get_the_term_list( $id, $name, '<li>', '</li><li>', '</li>' );
                $void = $terms ? '' : ' void';
                $markup .= "<dt class='meta-label taxo-label $class-label$void'>$label</dt>";
                $markup .= "<dd class='meta-value taxo-value $class-value$void'>";
                $markup .= '<ul class="term-list">' . $terms . '</ul></dd>';
            }
        }
    }

    $markup = '<dl class="meta-list entry-taxos">' . $markup . '</dl>';
    $markup = apply_filters( '@entry_terms', $markup, $taxos );
    echo $markup;
}, 20);

# Actions to be run on the 'init' hook
# CPTs and taxonomies should register on init.
# Scripts/styles should register/enqueue on init.
add_action( 'init', function() {
    
    # Register Modernizr
    $modernizr_uri = apply_filters( '@modernizr_uri', 'http://airve.github.com/js/modernizr/modernizr_shiv.min.js' );
    $modernizr_uri and wp_register_script( 'modernizr', $modernizr_uri, array(), null, false );

    # Get URI in child theme or else parent theme.
    $locate_uri = function($file) {
        $file = '/' . ltrim($file, '/');
        foreach( array('get_stylesheet_directory', 'get_template_directory') as $fn ) {
            if ( \file_exists( \rtrim( \call_user_func($fn), '/' ) . $file ) )
                return \rtrim( \call_user_func($fn . '_uri'), '/' ) . $file;
        }
    };

    # Frontend-specific actions:
    if ( !is_admin() ) {
        # Enqueue CSS
        $css = array( 'base' => null );
        $css['style'] = is_child_theme() ? null : 'screen,projection,tty,tv';
        foreach ( $css as $handle => $media ) {
            $file = $locate_uri( "$handle.css" );
            $file and wp_enqueue_style( $handle, $file, array(), null, $media );
        }
        
        # Enqueue Modernizr
        $modernizr_uri and wp_enqueue_script( 'modernizr' );
        
        # required for theme approval
        # codex.wordpress.org/Migrating_Plugins_and_Themes_to_2.7/Enhanced_Comment_Display
        is_singular() and wp_enqueue_script( 'comment-reply' );
    
        # Google Analytics
        if ( $gaq = apply_filters( '@gaq', array() ) ) {
            # WP runs json_encode on data provided to wp_localize_script
            # so decode it if it looks like it's already encoded.
            \is_scalar($gaq) and $gaq = \json_decode( $gaq );
            $ga_uri = apply_filters( '@ga_uri', 'http://www.google-analytics.com/ga.js' );
            $ga_uri and wp_enqueue_script( 'ga', $ga_uri, array(), null, true );
            wp_localize_script( 'ga', '_gaq', $gaq );
        }
    }
});

add_filter('@output', function($html) {
    # The '@output' filter enables filtering via DOMDocument
    # Remove excessive whitespace for better readability:
    return \preg_replace( '/\n+\s*\n+/', "\n\n", $html );
});

# early priority <head> actions
# debating whether to use filters (like below) and/or to make
# them named functions so child themes can use remove_action
add_action('wp_head', function() {
     $tag = '<meta charset="utf-8">';
     echo ltrim( apply_filters( '@meta_charset', $tag ) . "\n" );
}, -5); 

add_action('wp_head', function() {
    $tag = '<title>' . get_the_title() . '</title>';
    echo ltrim( apply_filters( '@title_tag', $tag ) . "\n\n" );
}, -3); 

add_action('wp_head', function() {
    $tag = '<meta name="viewport" content="width=device-width,initial-scale=1.0">';
    echo ltrim( apply_filters( '@meta_viewport', $tag ) . "\n" );
}, -1); 

# comments callback ( see comments.php )
# codex.wordpress.org/Function_Reference/wp_list_comments
# wp-includes/comment-template.php
add_filter('@list_comments', function( $arr ) {
    empty( $arr['callback'] ) and $arr['callback'] = function( $comment, $arr, $depth ) {
        $GLOBALS['comment'] = $comment;
        $GLOBALS['comment_depth'] = $depth;
        $attrs;
        $attrs = apply_filters( '@comment_attrs', $attrs );
        echo "<li><article $attrs>"; 
        do_action( '@comment' );
        echo '</article>'; 
    };
    return $arr;
});

add_filter('@comment_attrs', \function_exists( '\\phat\\attrs' ) ? function() {
    # core.trac.wordpress.org/ticket/23236
    $attrs = array(); 
    $id = get_comment_ID();
    is_singular() and $attrs['id'] = 'comment-' . $id;
    $attrs['class'] = \implode(' ', get_comment_class( '', $id ));
    if ( 'comment' === get_comment_type($id) ) {
        $attrs['itemprop'] = 'comment';
        $attrs['itemscope'] = '';
        $attrs['itemtype'] = 'http://schema.org/UserComments';
    }
    return  \phat\attrs( $attrs );
} : function() {
    $attrs = array(); 
    $id = get_comment_ID();
    is_singular() and $attrs[] = "id='comment-$id'";
    $class = \implode(' ', get_comment_class( '', $id ));
    $class and $attrs[] = "class='$class'";
    if ( 'comment' === get_comment_type( $id ) ) {
        $attrs[] = 'itemprop="comment"';
        $attrs[] = 'itemscope';
        $attrs[] = 'itemtype="http://schema.org/UserComments"';
    }
    return \implode(' ', $attrs);
}, 1);

add_action('@comment', apply_filters('@comment_actions', function() {

    static $ran; # prevent from running more than once
    if ( $ran = null !== $ran ) return;

    add_action('@comment', function() {
        global $comment;
        $markup = '<header class="comment-header">';
        $markup .= apply_filters( '@comment_avatar', get_avatar( $comment, 60 ) );
        $markup .= '<dl class="meta-list">'; 
        $markup .= '<dt>' . __('By', 'theme') . '</dt>'; 
        $markup .= '<dd itemprop="creator">' . get_comment_author_link() . '</dd>';
        $markup .= '<dt class="published-label">' . __('Published', 'theme') . '</dt>';
        $markup .= '<dd class="published-value" itemprop="commentTime">' . get_comment_date() . '</dd>';
        $markup .= '</dl></header>';
        echo apply_filters( '@comment_header', $markup );
    }, 5);
    
    add_action('@comment', function() {
        $markup = '<div class="comment-content" itemprop="commentText">';
        $markup .= get_comment_text( get_comment_ID() );
        $markup .= '</div>';
        echo $markup;
    }, 10);
    
    add_action('@comment', function() {
        global $comment;
        if ( ! $comment->comment_approved ) {
            $markup = __('Your comment is awaiting moderation.', 'theme');
            $markup = "<p class='alert' role='alert'>$markup</p>";
            echo apply_filters( '@comment_moderation', $markup );
        }
    }, 10);
}), 0);

# @link github.com/ryanve/action/issues/1
add_filter('the_author_posts_link', function( $tag ) {
    # add hcard classes to the link if there's not already any classes
    return \strpos($tag, 'class=') ? $tag : str_replace(' href=', ' class="url fn n" href=', $tag);
});