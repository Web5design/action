<?php 
namespace theme;
?>

        <!-- 
            @link github.com/ryanve/action/issues/1
            @link microformats.org/wiki/hatom
            @link microformats.org/wiki/hcard
            @link microformats.org/wiki/hentry
            @link stackoverflow.com/a/7295013/770127
        -->

        <div class="loop hfeed" itemscope>
            
            <?php do_action( '@loop' ); ?>

        </div><!-- .loop -->
        
