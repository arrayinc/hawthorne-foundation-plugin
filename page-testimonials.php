<?php

materialis_get_header();
?>
<div class="page-content">
    <div class="gridContainer">
        <div class="row">
            <div class="col-xs-12 col-sm-4 col-md-3 page-sidebar-column">
                <div class="sidebar page-sidebar">
                    <?php dynamic_sidebar('testimonials_sidebar'); ?>
                </div>
            </div>

            <div class="col-xs-12 col-sm-8 col-md-9">
                <?php
                while (have_posts()) : the_post();
                    the_content();
                endwhile;
                ?>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>