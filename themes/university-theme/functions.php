<?php 

    require get_theme_file_path('/includes/search-route.php');
    require get_theme_file_path('/includes/like-route.php');

    function load_scripts_and_styles() {
        $googleMapsUrl = '//maps.googleapis.com/maps/api/js?key=' . GOOGLE_MAPS_API_KEY;
        wp_enqueue_script('google-map', $googleMapsUrl, NULL, microtime(), true);
        wp_enqueue_script('main-js', get_theme_file_uri('/js/scripts-bundled.js'), NULL, microtime(), true); //slideshow behavior
        wp_enqueue_style('custom-google-font', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
        wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_style('bootstrap-css', '//stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css');
        // wp_enqueue_script('bootstrap-js', get_theme_file_uri('/js/bootstrap.min.js'));
        wp_enqueue_style('main_styles', get_stylesheet_uri(), NULL, microtime());
        wp_localize_script('main-js', 'mainData', array(
          'root_url' => get_site_url(),
          'nonce' => wp_create_nonce('wp_rest')
        ));
    }
    function manage_display_features() {
        add_theme_support('title-tag'); //wp handles header title
        add_theme_support('post-thumbnails'); // add featured image widget on admin screen
        add_image_size('professorLandscape', 400, 260, true); 
        add_image_size('professorPortrait', 480, 650, true);
        add_image_size('pageBanner', 1500, 350, true);
        add_WP_admin_menu_display_locations();
    }
    function add_WP_admin_menu_display_locations() {
        register_nav_menu('headerMenuLocation', 'Header Menu Location');
        register_nav_menu('footerLocationOne', 'Footer Location One'); 
        register_nav_menu('footerLocationTwo', 'Footer Location Two');
    }
    function adjust_queries($query) {
      if (!is_admin() AND is_post_type_archive('campus') AND $query->is_main_query()) {
        $query->set('posts_per_page', '-1');
      } 
      if (!is_admin() AND is_post_type_archive('program') AND $query->is_main_query()) {
          $query->set('posts_per_page', '-1');
          $query->set('orderby', 'title');
          $query->set('order', 'ASC');
      }
      // not admin screen, only for event archives, do not manipulate custom queries
      if (!is_admin() AND is_post_type_archive('event') AND $query->is_main_query()) {
        $today = date('Ymd');
        $query->set('posts_per_page', '10');
        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');
        $query->set('meta_query', array(
          array(
            'key' => 'event_date',
            'compare' => '>=',
            'value' => $today, 
            'type' => 'numeric'
          )
        ));
      }
      if (!is_admin() AND $query->is_main_query()) {
        $query->set('posts_per_page', '10');
      }
    }

    function custom_REST() { //access WP PHP methods for JSON use
      register_rest_field('post', 'authorName', array(
        'get_callback' => function() {return get_the_author();}
      ));
      register_rest_field('note', 'userNoteCount', array(
        'get_callback' => function() {return count_user_posts(get_current_user_id(), 'note');}
      ));
    }

    function automaticallyRedirectSubscribersToHomePage() {
      $currentUser = wp_get_current_user();
      $userHasSingleRole = (count($currentUser->roles) == 1);
      $hasSubscriberRole = $currentUser->roles[0] == 'subscriber';
      if($userHasSingleRole AND $hasSubscriberRole) {
        wp_redirect(site_url('/'));
        exit;
      }
    }

    function doNotShowAdminBarForSubscribers() {
      $currentUser = wp_get_current_user();
      $userHasSingleRole = (count($currentUser->roles) == 1);
      $hasSubscriberRole = $currentUser->roles[0] == 'subscriber';
      if($userHasSingleRole AND $hasSubscriberRole) {
        show_admin_bar(false);
      }
    }

    function customizeLoginCSS() {
      wp_enqueue_style('main_styles', get_stylesheet_uri(), NULL, microtime());
      wp_enqueue_style('custom-google-font', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    }

    add_action('wp_enqueue_scripts','load_scripts_and_styles');
    add_action('after_setup_theme','manage_display_features');
    add_action('pre_get_posts', 'adjust_queries');
    add_action('rest_api_init','custom_REST');
    add_action('admin_init', 'automaticallyRedirectSubscribersToHomePage');
    add_action('wp_loaded', 'doNotShowAdminBarForSubscribers');
    add_action('login_enqueue_scripts','customizeLoginCSS');

    function universityMapKey($api) {
      $api['key'] = GOOGLE_MAPS_API_KEY;
      return $api;
    }

    function customizeLoginScreenUrl() {
      return esc_url(site_url('/'));
    }

    function customizeLoginTitle() {
      return get_bloginfo('name');
    }

    function makeNotePrivateAndLimited($data, $postarr) {
      if ($data['post_type'] == 'note') {
        $data = sanitize($data);
        if (count_user_posts(get_current_user_id(), 'note') >= USER_NOTE_LIMIT AND !$postarr['ID']) {
          die("You have reached your note limit.");
        }
      }

      if ($data['post_type'] == 'note' AND $data['post_status'] != "trash") {
        $data['post_status'] = "private";
      }
      return $data;
    }
    function sanitize($data) {
      $data['post_content'] = sanitize_textarea_field($data['post_content']);
      $data['post_title'] = sanitize_text_field($data['post_title']);
      return $data;
    }

    function generate_title_field( $value, $post_id, $field ) {
      if ( get_post_type( $post_id ) == 'student' ) {
    
        $new_title = get_field('first_name', $post_id) . ' ' . $value;
        $new_slug = sanitize_title( $new_title );
    
        // update post
        wp_update_post( array(
          'ID'         => $post_id,
          'post_title' => $new_title,
          'post_name'  => $new_slug,
          ) );
      }
      return $value;
    }

    function should_show_admin_bar() { 
      if (!current_user_can( 'manage_options')) 
      return false; 
    }
    
    add_filter('acf/fields/google_map/api', 'universityMapKey');
    add_filter('login_headerurl', 'customizeLoginScreenUrl');
    add_filter('login_headertitle', 'customizeLoginTitle');
    add_filter('wp_insert_post_data', 'makeNotePrivateAndLimited', 10, 2); //last two args are 'priority' and 'param amount'
    add_filter( 'acf/update_value/name=last_name', 'generate_title_field', 10, 3 );
    add_filter( 'show_admin_bar', 'should_show_admin_bar' );

    function pageBanner($args = NULL) {
      if (!$args['title']) {
          $args['title'] = get_the_title(); //WP Page Title as default
      }
      if (!$args['subtitle']) {
        $args['subtitle'] = get_field('page_banner_subtitle'); //WP Page Subtitle as default
      }
      if (!$args['photo']) {
        if (get_field('page_banner_background_image')) { //Get WP Admin uploaded photo if available
          $args['photo'] = get_field('page_banner_background_image')['sizes']['pageBanner']; 
        }
        else {
          $args['photo'] = get_theme_file_uri('/images/ocean.jpg');
        }
      }
      ?>
      <div class="page-banner">
        <div class="page-banner__bg-image" 
          style="background-image: url(
          <?php echo $args['photo']; ?>
          );">
        </div>
        <div class="page-banner__content container container--narrow">
          <h1 class="page-banner__title"><?php echo $args['title']; ?></h1>
          <div class="page-banner__intro">
          <p><?php echo $args['subtitle']; ?></p>
          </div>
        </div>  
      </div>
      <?php 
    }
?>