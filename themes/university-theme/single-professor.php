<!-- Whenever a custom post type is registered, WP is automatically on the lookout for a file in your theme folder named "single-[postType]" to use for display -->
<?php get_header(); ?>
<!-- Individual Post -->
<?php
    while(have_posts()) {
        the_post(); 
        pageBanner();
        ?>
        <div class="container container--narrow page-section">
            <div class="generic-content">
                <div class="row group">
                    <div class="one-third"><?php the_post_thumbnail('professorPortrait'); ?></div>
                    <div class="two-thirds">
                        <?php
                            $likeCount = new WP_Query(array(
                                'post_type' => 'like',
                                'meta_query' => array(
                                    array(
                                        'key' => 'liked_professor_id',
                                        'compare' => '=',
                                        'value' => get_the_ID()
                                    )
                                )
                            ));
                            $likeExists = "no";
                            if (is_user_logged_in()) {
                                $likeExistsQuery = new WP_Query(array(
                                    'author' => get_current_user_id(),
                                    'post_type' => 'like',
                                    'meta_query' => array(
                                        array(
                                            'key' => 'liked_professor_id',
                                            'compare' => '=',
                                            'value' => get_the_ID()
                                        )
                                    )
                                ));
                                if ($likeExistsQuery->found_posts) {
                                    $likeExists = "yes";
                                }                                
                            }
                        ?>
                        <span class="like-box-message">You must be logged in to create a Like.</span>
                        <span class="like-box" data-like="<?php echo $likeExistsQuery->posts[0]->ID; ?>" data-exists="<?php echo $likeExists; ?>" data-professor="<?php the_ID(); ?>">
                            <i class="fa fa-heart-o" aria-hidden="true"></i>
                            <i class="fa fa-heart" aria-hidden="true"></i>
                            <span class="like-count"><?php echo $likeCount->found_posts; ?></span>
                        </span>
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
            <?php
                $relatedPrograms = get_field('related_programs');
                if ($relatedPrograms) {
                    echo '<hr class="section-break">';
                    echo '<h2 class="headline headline--medium">Department(s)</h2>';
                    echo '<ul class="link-list min-list">';
                    foreach($relatedPrograms as $program) {
                ?>
                    <li>
                        <a href="<?php echo get_the_permalink($program) ?>"><?php echo get_the_title($program); ?></a>
                    </li>
                <?php }
                    echo '</ul>';
                }
            ?>
        </div>
    <?php }
?>
<?php get_footer(); ?>