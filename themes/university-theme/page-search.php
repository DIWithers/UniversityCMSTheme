<?php get_header(); ?>
<!-- Default Individual Page -->
<?php
    while(have_posts()) {
        the_post();
        pageBanner(array(
            'title' => the_title()
        ));
        ?>
        <div class="container container--narrow page-section">
            <?php $parentID = wp_get_post_parent_id(get_the_ID()) ?>
            <?php if ($parentID) { ?>
                <div class="metabox metabox--position-up metabox--with-home-link">
                    <p>
                        <a class="metabox__blog-home-link" href="<?php echo get_permalink($parentID); ?>">
                            <i class="fa fa-home" aria-hidden="true"></i> 
                            Back to <?php echo get_the_title($parentID); ?>
                        </a> 
                        <span class="metabox__main"><?php the_title(); ?></span>
                    </p>
                </div>
            <?php  }
            ?>
            <?php 
                $isParent = get_pages(array(
                    'child_of' => get_the_ID()
                ));
                if ($parentID or $isParent) { 
            ?>
                <div class="page-links">
                    <h2 class="page-links__title">
                        <a href="<?php echo get_permalink($parentID) ?>"><?php echo get_the_title($parentID) ?></a>
                    </h2>
                    <ul class="min-list">
                        <?php 
                            wp_list_pages(array(
                                'title_li' => NULL,
                                'child_of' => $parentID ? $parentID : get_the_ID(),
                                'sort_column' => 'menu_order'
                            ));
                        ?>
                    </ul>
                </div>
            <?php } ?>
            <div class="generic-content">
                <form
                class="search-form"
                method ="get" 
                action="<?php echo esc_url(site_url('/')); ?>"
                >
                    <label class="headline headline--medium" for="s">Perform a New Search:</label>
                    <div class="search-form-row">
                        <input class="s" id="s" type="search" name="s" placeholder="What are you looking for?"/>
                        <input class="search-submit" type="submit" value="Search" />
                    </div>
                </form>
            </div>
        </div>
    <?php }
?>
<?php get_footer(); ?>