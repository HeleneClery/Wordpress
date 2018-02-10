<?php
/*
 * Plugin Name: Icegram - Popups, Optins, CTAs & lot more...
 * Plugin URI: https://www.icegram.com/
 * Description: All in one solution to inspire, convert and engage your audiences. Action bars, Popup windows, Messengers, Toast notifications and more. Awesome themes and powerful rules.
 * Version: 1.10.16
 * Author: icegram
 * Author URI: https://www.icegram.com/
 * Copyright (c) 2014-16 Icegram
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Text Domain: icegram
 * Domain Path: /lang/
*/

/**
* Main class Icegram
*/
class Icegram {

    var $plugin_url;
    var $plugin_path;
    var $version;
    var $_wpautop_tags;
    var $message_types;
    var $message_type_objs;
    var $shortcode_instances;
    var $available_headlines;
    var $mode;
    var $cache_compatibility;

    public static $current_page_id;
    
    function __construct() {
        $this->version = "1.10.16";
        $this->shortcode_instances = array();
        $this->mode = 'local';
        $this->plugin_url   = untrailingslashit( plugins_url( '/', __FILE__ ) );
        $this->plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) );
        $this->include_classes();
        $this->cache_compatibility = get_option('icegram_cache_compatibility', 'no');

        if( is_admin() && current_user_can( 'edit_posts' ) ) {
            $ig_campaign_admin = Icegram_Campaign_Admin::getInstance();
            $ig_message_admin = Icegram_Message_Admin::getInstance();
            add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_admin_styles_and_scripts' ) );
            add_action( 'admin_print_styles', array( &$this, 'remove_preview_button' ) );        
            add_filter( 'post_row_actions', array( &$this , 'remove_row_actions' ), 10, 2 );

            add_action( 'admin_menu', array( &$this, 'admin_menus') );
            add_action( 'admin_init', array( &$this, 'welcome' ) );
            add_action( 'admin_init', array( &$this, 'dismiss_admin_notice' ) );

            add_action( 'admin_init', array( &$this, 'import_gallery_item' ) );

            add_action( 'icegram_settings_after', array( &$this, 'klawoo_subscribe_form' ) ); 
            add_action( 'icegram_about_changelog', array( &$this, 'klawoo_subscribe_form' ) ); 
            add_action( 'icegram_settings_after', array( &$this, 'icegram_houskeeping' ) ); 
            add_action( 'admin_notices', array( &$this,'add_admin_notices'));
            add_filter( 'plugin_action_links', array($this, 'ig_plugin_settings_link'), 10, 2);
        } else {
            add_action( 'wp_footer', array( &$this, 'icegram_load_data' ));
        }
        if($this->cache_compatibility === 'no'){
            add_action( 'wp_footer', array( &$this, 'display_messages' ) );
        }
        add_shortcode( 'icegram', array( &$this, 'execute_shortcode' ) );
        add_shortcode( 'ig_form', array( &$this, 'execute_form_shortcode' ) );
        // WPML compatibility
        add_filter( 'icegram_identify_current_page',  array( &$this, 'wpml_get_parent_id' ), 10 );

        add_filter( 'icegram_branding_data', array( &$this , 'branding_data_remove' ), 10 );
        add_action( 'wp_enqueue_scripts', array( &$this, 'identify_current_page' ) );
        add_filter( 'icegram_get_valid_campaigns_sql', array( &$this , 'append_to_valid_campaigns_sql' ), 10, 2 );
        add_action( 'icegram_print_js_css_data', array( &$this, 'print_js_css_data' ), 10, 1); 
        // common
        add_action( 'init', array( &$this, 'register_campaign_post_type' ) );
        add_action( 'init', array( &$this, 'register_message_post_type' ) );

        add_action( 'icegram_loaded', array( &$this, 'load_compat_classes') );

        // execute shortcode in sidebar
        add_filter( 'widget_text', array(&$this, 'ig_widget_text_filter') );

        add_filter( 'rainmaker_validate_request',  array(&$this,'form_submission_validate_request'), 10, 2);
        add_filter( 'icegram_data', array( $this, 'two_step_mobile_popup' ), 100, 1);
        
        add_action ( 'wp_ajax_ig_submit_survey',  array(&$this,'ig_submit_survey' ));

        if ( defined( 'DOING_AJAX' ) ) {
            if($this->cache_compatibility === 'yes'){
                add_action( 'wp_ajax_display_messages', array( &$this, 'display_messages' ) );
                add_action( 'wp_ajax_nopriv_display_messages', array( &$this, 'display_messages' ) );
            }
            add_action( 'wp_ajax_icegram_event_track', array( &$this, 'icegram_event_track' ) );
            add_action( 'wp_ajax_nopriv_icegram_event_track', array( &$this, 'icegram_event_track' ) );
            add_action( 'wp_ajax_klawoo_subscribe', array( &$this, 'klawoo_subscribe' ) );
            add_action( 'wp_ajax_icegram_run_housekeeping', array( &$this, 'run_housekeeping' ) );

        }

    }
    function ig_plugin_settings_link($links, $file){
        if ($file == plugin_basename(__FILE__)){
            $settings_link = '<a href="edit.php?post_type=ig_campaign&page=icegram-settings">'.__('Settings', 'icegram').'</a>';
            $support_link  = '<a href="edit.php?post_type=ig_campaign&page=icegram-support">'.__('Support', 'icegram').'</a>';
            array_unshift($links, $support_link);
            array_unshift($links, $settings_link);
        }
        return $links;

    }

    public function load_compat_classes() {

        $compat_classes = (array) glob( $this->plugin_path . '/classes/compat/class-icegram-compat-*.php' );
        if (empty($compat_classes)) {
            return;
        }

        $active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        $active_plugins = array_unique( array_merge( array_values($active_plugins), array_keys($active_plugins)) );
        $active_plugins_with_slugs = array();
        foreach ($active_plugins as $key => $value) {
            $slug = dirname( $value );
            if ($slug == '.') {
                unset($active_plugins[$key]);
            } else {
                $active_plugins[ $key ] = $slug;
            }
        }

        foreach ($compat_classes as $file) {
            if (is_file ( $file )) {
                $slug = str_replace('class-icegram-compat-', '', str_replace(".php", "", basename( $file )) );
                if ( in_array($slug, $active_plugins)) {
                    include_once( $file );
                    $class_name = 'Icegram_Compat_'. str_replace('-', '_', $slug);
                    if ( class_exists( $class_name ) ) {
                        new $class_name();
                    }
                }
            }
        }
    }
    public function add_admin_notices(){
        $screen = get_current_screen(); 
        if ( !in_array( $screen->id, array( 'ig_campaign', 'ig_message','edit-ig_message','edit-ig_campaign' ), true ) ) return;
        $timezone_format = _x('Y-m-d', 'timezone date format');
        $ig_current_date = strtotime(date_i18n($timezone_format));
        $ig_offer_start = strtotime("2017-12-8");
        $ig_offer_end = strtotime("2017-12-26");
        if(($ig_current_date >= $ig_offer_start) && ($ig_current_date <= $ig_offer_end)) {
            include_once('ig-offer.php');
        }
    }
    public function dismiss_admin_notice(){
        if(isset($_GET['ig_dismiss_admin_notice']) && $_GET['ig_dismiss_admin_notice'] == '1' && isset($_GET['ig_option_name'])){
            $option_name = sanitize_text_field($_GET['ig_option_name']);
            update_option($option_name.'_icegram', true);
            header("Location: https://www.icegram.com/?utm_source=in_app&utm_medium=ig_banner&utm_campaign=christmas2017_30");
            exit();
        }
    }
    
    public function klawoo_subscribe_form() {
        ?>
        <div class="wrap">
            <?php 
            if ( stripos(get_current_screen()->base, 'settings') !== false ) {
                echo "<h2>".__( 'Free Add-ons, Proven Marketing Tricks and  Updates', 'icegram' )."</h2>";
            }
            ?>
            <table class="form-table">
                 <tr>
                    <th scope="row"><?php _e( 'Get add-ons and tips...', 'icegram' ) ?></th>
                    <td>
                        <form name="klawoo_subscribe" action="#" method="POST" accept-charset="utf-8">
                            <input class="ltr" type="text" name="name" id="name" placeholder="Name"/>
                            <input class="regular-text ltr" type="text" name="email" id="email" placeholder="Email"/>
                            <input type="hidden" name="list" value="7I763v6Ldrs3YhJeee5EOgFA"/>
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Subscribe">
                            <br/>
                            <div id="klawoo_response"></div>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
        <script type="text/javascript">
            jQuery(function () {
                jQuery("form[name=klawoo_subscribe]").submit(function (e) {
                    e.preventDefault();
                    
                    jQuery('#klawoo_response').html('');
                    params = jQuery("form[name=klawoo_subscribe]").serializeArray();
                    params.push( {name: 'action', value: 'klawoo_subscribe' });
                    
                    jQuery.ajax({
                        method: 'POST',
                        type: 'text',
                        url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                        data: params,
                        success: function(response) {                   
                            if (response != '') {
                                jQuery('#klawoo_response').html(response);
                            } else {
                                jQuery('#klawoo_response').html('error!');
                            }
                        }
                    });
                });
            });
        </script>
        <?php
    }

 
    public function klawoo_subscribe() {
        $url = 'http://app.klawoo.com/subscribe';

        if( !empty( $_POST ) ) {
            $params = $_POST;
        } else {
            exit();
        }
        $method = 'POST';
        $qs = http_build_query( $params );

        $options = array(
            'timeout' => 15,
            'method' => $method
        );

        if ( $method == 'POST' ) {
            $options['body'] = $qs;
        } else {
            if ( strpos( $url, '?' ) !== false ) {
                $url .= '&'.$qs;
            } else {
                $url .= '?'.$qs;
            }
        }

        $response = wp_remote_request( $url, $options );
        if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
            $data = $response['body'];
            if ( $data != 'error' ) {
                             
                $message_start = substr( $data, strpos( $data,'<body>' ) + 6 );
                $remove = substr( $message_start, strpos( $message_start,'</body>' ) );
                $message = trim( str_replace( $remove, '', $message_start ) );
                echo ( $message );
                exit();                
            }
        }
        exit();
    }

    public function icegram_houskeeping(){
        ?>
        <div class="wrap">
            <?php 
            if ( stripos(get_current_screen()->base, 'settings') !== false ) {
            ?>
                <form name="icegram_housekeeping" action="#" method="POST" accept-charset="utf-8">
                        <h2><?php _e( 'Housekeeping', 'icegram' ) ?></h2>
                        <p class="ig_housekeeping">
                            <label for="icegram_remove_shortcodes">
                                <input type="checkbox" name="icegram_remove_shortcodes" value="yes" />
                                <?php _e( 'Remove all Icegram shortcodes', 'icegram' ); ?>                        
                            </label>
                            <br/><br/>
                            <label for="icegram_remove_all_data">
                                <input type="checkbox" name="icegram_remove_all_data" value="yes" />
                                <?php _e( 'Remove all Icegram campaigns and messages', 'icegram' ); ?>                        
                            </label>
                            <br/><br/>
                            <img alt="" src="<?php echo admin_url( 'images/wpspin_light.gif' ) ?>" class="ig_loader" style="vertical-align:middle;display:none" />
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Clean Up', 'icegram' ); ?>">
                            <div id="icegram_housekeeping_response"></div>
                        </p>
                </form>
          
        </div>
        <script type="text/javascript">
            jQuery(function () {
                jQuery("form[name=icegram_housekeeping]").submit(function (e) {
                    if(confirm("<?php _e( 'You won\'t be able to recover this data once you proceed. Do you really want to perform this action?', 'icegram' ); ?>") == true){
                        e.preventDefault();
                        jQuery('.ig_loader').show();
                        jQuery('#icegram_housekeeping_response').text("");                        
                        params = jQuery("form[name=icegram_housekeeping]").serializeArray();
                        params.push( {name: 'action', value: 'icegram_run_housekeeping' });
                        params.push( {name: 'security', value: '<?php echo wp_create_nonce('ig_run_housekeeping'); ?> '});

                        jQuery.ajax({
                            method: 'POST',
                            type: 'text',
                            url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                            data: params,
                            success: function(response) {                   
                                jQuery('.ig_loader').hide();
                                jQuery('#icegram_housekeeping_response').text("<?php _e('Done!', 'icegram'); ?>");
                            }
                        });
                    }
                });
            });
        </script>
    <?php
        }
    }
    public function run_housekeeping() {
        check_ajax_referer('ig_run_housekeeping', 'security');
        global $wpdb, $current_user;
        $params = $_POST; 
        $_POST = array();
        if(current_user_can( 'manage_options' ) && !empty($params['icegram_remove_shortcodes']) && $params['icegram_remove_shortcodes'] == 'yes') {
            // first get all posts with [icegram] shortcode in them
            $sql = "SELECT * FROM `$wpdb->posts` WHERE  `post_content` LIKE  '%[icegram %]%' and `post_type` != 'revision' ";
            $posts = $wpdb->get_results($sql, OBJECT);
            if ( !empty($posts) && is_array($posts) ) {
                foreach ($posts as $post) {
                    $post_content = $post->post_content;
                    // remove shortcode with regexp now
                    $re = "/\\[icegram(.)*\\]/i"; 
                    $post_content = preg_replace($re, '', $post_content);
                    // save post content back
                    if ($post_content && $post_content != $post->post_content) {
                        wp_update_post( array ( 'ID'            => $post->ID,
                                                'post_content'  => $post_content
                                        ) );
                    }
                }
            }
        }

        if(!empty($params['icegram_remove_all_data']) && $params['icegram_remove_all_data'] == 'yes') {
            $posts = get_posts( array( 'post_type' => array( 'ig_campaign', 'ig_message' ) ) );
            if ( !empty($posts) && is_array($posts) ) {
                foreach ($posts as $post) {
                    wp_delete_post( $post->ID, true);
                }
            }
            do_action('icegram_remove_all_data');
        }
        $_POST = $params;
    }

    public function icegram_event_track() { 
        if( !empty($_POST['ig_local_url_cs']) && isset($_SERVER['HTTP_ORIGIN']) ){
            $parts = parse_url($_POST['ig_local_url_cs']);
            $base_url = $parts["scheme"] . "://" . $parts["host"];
            header('Access-Control-Allow-Origin: '.$base_url);
            header('Access-Control-Allow-Credentials: true');
        }

        if( !empty( $_POST['event_data'] ) ) {
            foreach ( $_POST['event_data'] as $event ) {
                switch ($event['type']) {
                    case 'shown':
                        if (is_array($event['params']) && !empty($event['params']['message_id'])) {
                            $messages_shown[] = $event['params']['message_id'];
                            if(!empty($event['params']['expiry_time'])){
                                if($event['params']['expiry_time'] =='today'){
                                    $event['params']['expiry_time'] = strtotime('+1 day', mktime(0, 0, 0));
                                }else if($event['params']['expiry_time'] == 'current_session'){
                                    $event['params']['expiry_time'] = 0;
                                }else{
                                    $event['params']['expiry_time'] = strtotime($event['params']['expiry_time']);
                                }
                                
                                $event['default'] = true;
                                $event = apply_filters('icegram_check_event_track', $event);
                                if($event['default']){
                                    setcookie('icegram_campaign_shown_'.floor($event['params']['campaign_id']),true , $event['params']['expiry_time'] , '/');    
                                }
                            }
                        }
                        break;
                    case 'clicked':
                    if (is_array($event['params']) && !empty($event['params']['message_id'])) {
                        $messages_clicked[] = $event['params']['message_id'];
                        if(!empty($event['params']['expiry_time_clicked'])){
                            if($event['params']['expiry_time_clicked'] =='today'){
                                $event['params']['expiry_time_clicked'] = strtotime('+1 day', mktime(0, 0, 0));
                            }else if($event['params']['expiry_time_clicked'] == 'current_session'){
                                $event['params']['expiry_time_clicked'] = 0;
                            }else{
                                $event['params']['expiry_time_clicked'] = strtotime($event['params']['expiry_time_clicked']);
                            }
                           //setcookie('icegram_messages_clicked_'.$event['params']['message_id'],true , $event['params']['expiry_time_clicked'] , '/' );    
                           setcookie('icegram_campaign_clicked_'.floor($event['params']['campaign_id']),true , $event['params']['expiry_time_clicked'] , '/' );    
                        }
                    }
                    break;
                    
                    default:
                        break;
                }

                // Emit event for other plugins to handle it
                do_action('icegram_event_track', $event);
                do_action('icegram_event_track_'.$event['type'], $event['params']);
            }
        }
        exit();
    }

    static function install() {
        // Redirect to welcome screen 
        delete_option( '_icegram_activation_redirect' );      
        add_option( '_icegram_activation_redirect', 'pending' );
    }

    public function welcome() {

        $this->db_update();
        // Bail if no activation redirect transient is set
        if ( false === get_option( '_icegram_activation_redirect' ) )
            return;               

        // Delete the redirect transient
        delete_option( '_icegram_activation_redirect' );

        wp_redirect( admin_url( 'edit.php?post_type=ig_campaign&page=icegram-support' ) );
        exit;
    }

    function db_update() {
        $current_db_version = get_option( 'icegram_db_version' );
        if ( !$current_db_version || version_compare( $current_db_version, '1.2', '<' ) ) {
            include( 'updates/icegram-update-1.2.php' );
        }
    }

    public function admin_menus() {

        $welcome_page_title   = __( 'Welcome to Icegram', 'icegram' );
        $gallery_page_title   = '<span style="color:#f18500;font-weight:bolder;">' . __( 'Gallery', 'icegram' ).'<span>'; 
        $gallery              = add_submenu_page( 'edit.php?post_type=ig_campaign', $gallery_page_title,  $gallery_page_title, 'manage_options', 'icegram-gallery', array( $this, 'gallery_screen' ) );
        $settings_page_title  = __( 'Settings', 'icegram' ); 
        $upgrade_page_title   = '<span style="color:#f18500;font-weight:bolder;">'.__( 'Upgrade', 'icegram' ) .'</span>'; 

        $menu_title = __( 'Docs & Support', 'icegram' );
        $about      = add_submenu_page( 'edit.php?post_type=ig_campaign', $welcome_page_title,  $menu_title, 'manage_options', 'icegram-support', array( $this, 'about_screen' ) );
        $settings   = add_submenu_page( 'edit.php?post_type=ig_campaign', $settings_page_title,  $settings_page_title, 'manage_options', 'icegram-settings', array( $this, 'settings_screen' ) );
        $upgrade    = add_submenu_page( 'edit.php?post_type=ig_campaign', $upgrade_page_title,  $upgrade_page_title, 'manage_options', 'icegram-upgrade', array( $this, 'upgrade_screen' ) );


        add_action( 'admin_print_styles-'. $about, array( $this, 'admin_css' ) );
        add_action( 'admin_print_styles-'. $settings, array( $this, 'admin_css' ) );
        add_action( 'admin_print_styles-'. $upgrade, array( $this, 'admin_css' ) );

    }

    public function admin_css() {
        wp_enqueue_style( 'icegram-activation', $this->plugin_url . '/assets/css/admin.min.css' );
    }

    public function about_screen() {

        // Import data if not done already
        if( false === get_option( 'icegram_sample_data_imported' ) ) {
            $this->import_sample_data( $this->get_sample_data() );
        }

        include ( 'about-icegram.php' );
    }

    public function settings_screen() {        
        include ( 'settings.php' );
    }

    public function upgrade_screen() {        
        include ( 'addons.php' );
    }

    public static function gallery_screen() {
        global $icegram;
        include ( 'gallery.php' );
        wp_register_script('ig_gallery_js', $icegram->plugin_url . '/assets/js/gallery.min.js', array ( 'jquery', 'backbone', 'wp-backbone', 'wp-a11y', 'wp-util' ), $icegram->version, true);
        if( !wp_script_is( 'ig_gallery_js' ) ) {
            wp_enqueue_script( 'ig_gallery_js' );
            $imported_gallery_items = get_option('ig_imported_gallery_items',true);
            $ig_plan = get_option('ig_engage_plan');
            $ig_plan = (!empty($ig_plan)) ? (($ig_plan == 'plus') ? 1 :(($ig_plan == 'pro') ? 2 : (($ig_plan == 'max') ? 3 : 0))) : 0; 
            wp_localize_script( 'ig_gallery_js', '_wpThemeSettings', array(
                'themes'   => array(),
                'settings' => array(
                    'canInstall'    => ( ! is_multisite() && ( 'install_themes' ) ),
                    'isInstall'     => true,
                    'installURI'    => ( ! is_multisite() && ( 'install_themes' ) ) ? admin_url( 'theme-install.php' ) : null,
                    'confirmDelete' => __( "Are you sure you want to delete this theme?\n\nClick 'Cancel' to go back, 'OK' to confirm the delete." ),
                    'adminUrl'      => parse_url( admin_url(), PHP_URL_PATH ),
                    'ig_plan'       => $ig_plan
                ),
                'l10n' => array(
                    'addNew'            => __( 'Add New Gallery Item' ),
                    'search'            => __( 'Search Gallery Item' ),
                    'searchPlaceholder' => __( 'Search Gallery Item...' ), // placeholder (no ellipsis)
                    'themesFound'       => __( 'Number of Gallery Item found: %d' ),
                    'noThemesFound'     => __( 'No Gallery Item found. Try a different search.' ),
                ),
                'installedThemes' => $imported_gallery_items
            ) );
        }   
    }

    public function branding_data_remove( $icegram_branding_data ) {
        if( !empty( $icegram_branding_data ) && 'yes' != get_option('icegram_share_love', 'no') ) {
            $icegram_branding_data['powered_by_logo'] = '';
            $icegram_branding_data['powered_by_text'] = '';
        }
        return $icegram_branding_data;
    }
    
    //Execute Form shortcode
    function execute_form_shortcode( $atts = array() ) {
        return '<div class="ig_form_container layout_inline"></div>';
    }

    function execute_shortcode( $atts = array() , $content = null ) {
        // When shortcode is called, it will only prepare an array with conditions
        // And add a placeholder div
        // Display will happen in footer via display_messages()
        $i = count($this->shortcode_instances);
        $this->shortcode_instances[ $i ] = shortcode_atts( array(
                'campaigns' => '',
                'messages'  => '',
                'skip_others' => 'no'
            ), $atts );
       
        $class[] = "ig_shortcode_container";
        $html[] = "<div id='icegram_shortcode_{$i}'";
        if(!empty($atts['campaigns']) && !empty($content)){
            $this->shortcode_instances[ $i ]['with_content'] = true; 
            $class[] = "trigger_onclick";
        }
        foreach ($atts as $key => $value) {
            $value = str_replace(",", " ", $value);
            $html[] = " data-{$key}=\"".htmlentities($value)."\" ";
        }
        $class = implode(" ", $class);
        $html[] = "class='".$class."' >".$content."</div>";
        return implode(" ", $html);
    }

    // Do not index Icegram campaigns / messages...
    // Not using currently - made custom post types non public...
    function icegram_load_data() {
        global $post;
        $icegram_pre_data['ajax_url'] = admin_url( 'admin-ajax.php' );
        $icegram_pre_data['post_obj'] = $_GET;
        $icegram_pre_data['post_obj']['is_home'] = (is_home() || is_front_page()) ? true : false;
        $icegram_pre_data['post_obj']['page_id'] = is_object($post) && isset($post->ID) ? $post->ID : 0;
        $icegram_pre_data['post_obj']['action'] = 'display_messages';
        $icegram_pre_data['post_obj']['shortcodes'] = $this->shortcode_instances;
        $icegram_pre_data['post_obj']['cache_compatibility'] = $this->cache_compatibility;
        $icegram_pre_data['post_obj']['device'] = $this->get_platform();
        wp_register_script('icegram_main_js', $this->plugin_url . '/assets/js/main.min.js', array ( 'jquery' ), $this->version, true);
        if( !wp_script_is( 'icegram_main_js' ) ) {
            wp_enqueue_script( 'icegram_main_js' );
            wp_localize_script( 'icegram_main_js', 'icegram_pre_data', $icegram_pre_data);
        }
    }

    function display_messages() {

        $skip_others    = $preview_mode = false;
        $campaign_ids   = $message_ids  = array();
        $this->shortcode_instances = ($this->cache_compatibility == 'yes' && !empty($_REQUEST['shortcodes'])) ? $_REQUEST['shortcodes'] : $this->shortcode_instances;
        // Pull in message and campaign IDs from shortcodes - if set
        if( !empty( $this->shortcode_instances ) ) {
            foreach ($this->shortcode_instances as $i => $value) {
                $cids   = array_map( 'trim', (array) explode( ',', $value['campaigns'] ) );
                $mids   = array_map( 'trim', (array) explode( ',', $value['messages'] ) );
                if (!empty($value['skip_others']) && $value['skip_others'] == 'yes' && (!empty($cids) || !empty($mids))) {
                    $skip_others = true;
                }
                $campaign_ids   = array_merge($campaign_ids, $cids);
                $message_ids    = array_merge($message_ids, $mids);
            }
        }
        if( !empty( $_REQUEST['campaign_preview_id'] ) &&  ( 'edit_posts' ) ) {
            $campaign_ids = array( $_REQUEST['campaign_preview_id'] );
            $preview_mode = true;
        }

        
        $messages = $this->get_valid_messages( $message_ids, $campaign_ids, $preview_mode, $skip_others );

        if( empty( $messages ) ) {
            //wp_die(0);
            return;
        }

        $messages_to_show_ids = array();
        foreach ( $messages as $key => $message_data ) {

            if( !is_array( $message_data ) || empty( $message_data ) ) {
                continue;
            }
            
            // Don't show a seen message again - if needed 
            // change to campaign targetting in v1.9.1
            if( !empty( $message_data['id'] ) &&
                empty( $_GET['campaign_preview_id'] ) &&
                !empty( $message_data['retargeting'] ) &&
                $message_data['retargeting'] == 'yes' 
            ) {
                if(!empty($_COOKIE['icegram_messages_shown_'.$message_data['id']]) || !empty($_COOKIE['icegram_campaign_shown_'.floor($message_data['campaign_id'])])){
                    unset( $messages[$key] );
                    continue;
                }
            }
            if( !empty( $message_data['id'] ) &&
                empty( $_GET['campaign_preview_id'] ) &&
                !empty( $message_data['retargeting_clicked'] ) &&
                $message_data['retargeting_clicked'] == 'yes' 
            ) {
                if(!empty($_COOKIE['icegram_messages_clicked_'.$message_data['id']]) || !empty($_COOKIE['icegram_campaign_clicked_'.floor($message_data['campaign_id'])])){
                    unset( $messages[$key] );
                    continue;
                }
            }

            // Avoid showing the same message twice
            if (in_array($message_data['id'], $messages_to_show_ids)) {
                unset ( $messages[$key] );
                continue;
            } else {
                $messages_to_show_ids[] = $message_data['id'];    
            }
            
            $this->process_message_body($messages[$key]);
        }
        if( empty( $messages ) )
            return;
        $icegram_default = apply_filters( 'icegram_branding_data', 
                                            array ( 'icon'   => $this->plugin_url . '/assets/images/icegram-logo-branding-64-grey.png',
                                                    'powered_by_logo'       => $this->plugin_url . '/assets/images/icegram-logo-branding-64-grey.png',
                                                    'powered_by_text'       => __( 'Powered by Icegram', 'icegram' )
                                                    ) );
        $messages       = apply_filters( 'icegram_messages_to_show', $messages );
        $icegram_data   = apply_filters( 'icegram_data', array ( 'messages' => array_values( $messages ),
                           'ajax_url'       => admin_url( 'admin-ajax.php' ),
                           'preview_id'     => !empty( $_GET['campaign_preview_id'] ) ? $_GET['campaign_preview_id'] : '',
                           'defaults'       => $icegram_default
                        ));
        if (empty($icegram_data['preview_id'])) {
            unset($icegram_data['preview_id']);
        }
            
        do_action('icegram_print_js_css_data', $icegram_data);

        do_action('icegram_data_printed');
    }

    function two_step_mobile_popup( $icegram_data ) {

        $temp = array();
        foreach ($icegram_data['messages'] as $message_id => $message) {
            
            if(!empty($message['ig_mobile_popup']) && $message['ig_mobile_popup'] == true){
                $action_bar = $message;
                $action_bar['type'] = 'action-bar';
                $action_bar['theme'] = 'hello';
                $action_bar['position'] = '21';
                $action_bar['message'] = '';
                $action_bar['label'] = __('Show More', 'icegram');
                $action_bar['id'] = $action_bar['id'].'_00';
                $action_bar['use_custom_code'] = 'yes';
                $action_bar['form_html'] = '';
                $action_bar['form_html_original'] = '';
                $action_bar['rainmaker_form_code'] = '';
                $action_bar['link'] = '';
                $action_bar['redirect_to_link'] = '';
                $action_bar['cta'] = '';
                $action_bar['alt_cta'] = '';
                $action_bar['add_alt_cta'] = '';
                $action_bar['custom_css'] = '#ig_this_message .ig_close{display:none;}';
                $action_bar['custom_js'] = "<script type='text/javascript'>jQuery('#icegram_message_".$action_bar['id']."').on('click', '.ig_button', function(){icegram.get_message_by_id('".$action_bar['id']."').hide(); icegram.get_message_by_id('".$message['id']."').show(); });</script>";
                unset($action_bar['ig_mobile_popup']);
                $temp[] = $action_bar;
            }
        }
        $icegram_data['messages'] = array_merge($icegram_data['messages'], $temp);
        unset($temp);

        return $icegram_data;
    }


    function print_js_css_data( $icegram_data ) {
            
        $this->collect_js_and_css($icegram_data);
        if($this->cache_compatibility === 'yes'){
            echo json_encode($icegram_data);
            wp_die();
        }else{
            wp_localize_script('icegram_main_js', 'icegram_data', $icegram_data);
        }
    }

    function collect_js_and_css(&$icegram_data){
        
        $types_shown    = array(); 
        $scripts = array();
        $css = array();
        foreach ($icegram_data['messages'] as $key => $message_data) {
            $types_shown[] = $message_data['type'];
        }
        
        $types_shown = array_unique($types_shown);
        $ver_psfix = '?var=' .$this->version;

        $scripts[] = $this->plugin_url ."/assets/js/icegram.min.js". $ver_psfix;
        $css[] = $this->plugin_url ."/assets/css/frontend.min.css". $ver_psfix;
        //minify and combine only for default msg type
        $ig_core_message_types = array('popup', 'action-bar', 'toast', 'messenger');
        // Load JS and default CSS
        foreach ($types_shown as $message_type) {
            if(!in_array($message_type, $ig_core_message_types)){
                $scripts[] = $this->message_types[$message_type]['baseurl'] ."main.js". $ver_psfix;
                $css[] = $this->message_types[$message_type]['baseurl'] . "default.css". $ver_psfix;
            }else{
                $css[] = $this->message_types[$message_type]['baseurl'].'themes/'. $message_type. ".min.css". $ver_psfix;
            }
        }
       
        //TODO :: add theme pack theme css files too.
        // Load theme CSS
        foreach ($icegram_data['messages'] as $key => $message) {
            if ( !empty( $this->message_types[ $message['type'] ]['themes'][ $message['theme'] ]) ) {
                $theme = $this->message_types[ $message['type'] ]['themes'][ $message['theme'] ]['baseurl'] . $message['theme'].'.css'. $ver_psfix;
            }else{
                $theme_default = $this->message_types[ $message['type']] ['settings']['theme']['default'];
                $theme = $this->message_types[ $message['type'] ]['themes'][ $theme_default]['baseurl'] . $theme_default.'.css'. $ver_psfix;
                $icegram_data['messages'][$key]['theme'] = $theme_default;
            }
            if(!preg_match('/icegram\/message-types/i', $theme)){
                $css [] = $theme;
            }
        }
        $css = array_unique($css);
        $icegram_data['scripts'] =  apply_filters('add_icegram_script' , $scripts);
        $icegram_data['css'] =   apply_filters('add_icegram_css' , $css);
        return $icegram_data;
    }

    // Process
    function process_message_body(&$message_data){
        global $wp_scripts;
        global $wp_styles;
        
        if($this->cache_compatibility == 'yes'){
            $q_script = !empty($wp_scripts->queue) ? $wp_scripts->queue : array();
            $q_style = !empty($wp_styles->queue) ? $wp_styles->queue : array();
        }
        $content = $message_data['message'];
        $content =  convert_chars(convert_smilies( wptexturize( $content )));
        if(isset($GLOBALS['wp_embed'])) {
            $content = $GLOBALS['wp_embed']->autoembed($content);
        }
        $content = $this->after_wpautop( wpautop( $this->before_wpautop( $content ) ) );
        $content = do_shortcode( shortcode_unautop( $content ) );
        $message_data['message'] = $content;
           
        //do_shortcode in headline
        $message_data['headline'] = do_shortcode( shortcode_unautop( $message_data['headline'] ) );
        //shortcode support for Third party forms and Rainmaker
        $form_html_original = !empty($message_data["rainmaker_form_code"]) 
                                ? ('[rainmaker_form id="'. $message_data["rainmaker_form_code"] .'"]')
                                :(!empty($message_data['form_html_original']) ? $message_data['form_html_original'] : '');
        if(!empty($form_html_original)){
            $message_data['form_html'] = do_shortcode( shortcode_unautop( $form_html_original) );
        }
        //TODO :: Handle case for inline style and script
        if($this->cache_compatibility == 'yes'){
            $handles = !empty($wp_scripts->queue) ? array_diff($wp_scripts->queue, $q_script) : array();
            unset($q_script);
            if(!empty($handles)){
                if(empty($message_data['assets']))
                    $message_data['assets'] = array();

                ob_start();
                $wp_scripts->do_items($handles);
                $message_data['assets']['scripts'] = array_filter(explode('<script', ob_get_clean()));
            }

            //TODO :: do_items if required
            $handles = !empty($wp_styles->queue) ? array_diff($wp_styles->queue, $q_style) : array();
            unset($q_style);
            if(!empty($handles)){
                if(empty($message_data['assets']))
                    $message_data['assets'] = array();

                foreach ($handles as $handle) {
                    ob_start();
                    $wp_styles->do_item($handle);
                    $message_data['assets']['styles'][$handle] = ob_get_clean();
                }
            }
        }
    }

    function enqueue_admin_styles_and_scripts() {
        
        $screen = get_current_screen();   
        if ( !in_array( $screen->id, array( 'ig_campaign', 'ig_message', 'edit-ig_campaign' ), true ) ) return;

        // Register scripts
        wp_register_script( 'icegram_writepanel', $this->plugin_url . '/assets/js/admin.min.js', array ( 'jquery', 'wp-color-picker' ), $this->version );
        
        wp_enqueue_script( 'icegram_writepanel' );
        
        $icegram_writepanel_params  = array ( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'search_message_nonce' => wp_create_nonce( "search-messages" ), 'home_url' => home_url( '/' ) );
        $this->available_headlines  = apply_filters( 'icegram_available_headlines', array() );
        $icegram_writepanel_params  = array_merge( $icegram_writepanel_params, array( 'available_headlines' => $this->available_headlines ) );
        
        wp_localize_script( 'icegram_writepanel', 'icegram_writepanel_params', $icegram_writepanel_params );
        
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'icegram_admin_styles', $this->plugin_url . '/assets/css/admin.min.css', array(), $this->version  );

        if ( !wp_script_is( 'jquery-ui-datepicker' ) ) {
            wp_enqueue_script( 'jquery-ui-datepicker' );
        }

    }
    
    //execute shortcode in text widget
    function ig_widget_text_filter($content){
        if ( ! preg_match( '/\[[\r\n\t ]*icegram?[\r\n\t ].*?\]/', $content ) )
        return $content;

        $content = do_shortcode( $content );

        return $content;
    }

    public static function get_platform() {
        $mobile_detect = new Ig_Mobile_Detect();
        $mobile_detect->setUserAgent();
        if($mobile_detect->isMobile()){
            return ($mobile_detect->isTablet()) ? 'tablet' : 'mobile';
        }else if($mobile_detect->isTablet()){
            return 'tablet';
        }
        return 'laptop';
    }

    function get_message_data( $message_ids = array(), $preview = false ) {
        global $wpdb;
        $message_data = array();
        $original_message_id_map = array();
        $meta_key = $preview ? 'icegram_message_preview_data' : 'icegram_message_data';
        $message_data_query = "SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '$meta_key'";
        if ( !empty( $message_ids ) && is_array( $message_ids ) ) {
            // For WPML compatibility
            if ( function_exists('icl_object_id') ) {
                $wpml_settings = get_option('icl_sitepress_settings');
                $original_if_missing = (is_array($wpml_settings) && array_key_exists('show_untranslated_blog_posts', $wpml_settings) && !empty($wpml_settings['show_untranslated_blog_posts']) ) ? true : false;
                    
                foreach ($message_ids as $i=>$id ) {
                    $translated = icl_object_id( $id, 'ig_message', $original_if_missing );
                    $message_ids[ $i ] = $translated;
                    $original_message_id_map[ $translated ] = $id;
                }              
            }
            $message_ids  = array_filter(array_unique($message_ids));
            if ( !empty( $message_ids ) ) {
                $message_data_query .= " AND post_id IN ( " . implode( ',', $message_ids ) . " )";
                $message_data_results = $wpdb->get_results( $message_data_query, 'ARRAY_A' );
                foreach ( $message_data_results as $message_data_result ) {
                    $data = maybe_unserialize( $message_data_result['meta_value'] );
                    if (!empty($data)) {
                        $message_data[$message_data_result['post_id']] = $data;
                        // For WPML compatibility
                        if (!empty( $original_message_id_map[ $message_data_result['post_id'] ])) {
                               $message_data[$message_data_result['post_id']]['original_message_id'] = $original_message_id_map[ $message_data_result['post_id'] ]; 
                        }
                    }
                }
            } 
        }
        
        return $message_data;
    }

    function get_valid_messages( $message_ids = array(), $campaign_ids = array(), $preview_mode = false, $skip_others = false) {
        list($message_ids, $campaign_ids, $preview_mode, $skip_others) = apply_filters('icegram_get_valid_messages_params', array( $message_ids, $campaign_ids, $preview_mode, $skip_others));

        $valid_messages = $valid_campaigns = $message_campaign_map = array();
        
        $campaign_ids        = array_filter(array_unique( (array) $campaign_ids));
        $message_ids        = array_filter(array_unique( (array) $message_ids));
        if ( !empty( $campaign_ids ) ) {
            $valid_campaigns = $this->get_valid_campaigns( $campaign_ids, true ,$preview_mode);
        }
              
        // When skip_others is true, we won't load campaigns / messages from db
        if (!$skip_others && !$preview_mode) {
            $campaigns = $this->get_valid_campaigns();
            if (!empty($campaigns)) {
                foreach ($campaigns as $id => $campaign) {
                    if (!array_key_exists($id, $valid_campaigns)) {
                        $valid_campaigns[ $id ] = $campaign;
                    }
                }
            }
        }
                
        // Create a map to look up campaign id for a given message
        if( !empty( $valid_campaigns ) ) {
            foreach ($valid_campaigns as $id => $campaign) {
                if ($preview_mode) {
                    $campaign->messages = get_post_meta( $id, 'campaign_preview', true );
                }
                if( !empty( $campaign->messages ) ) {
                    foreach( $campaign->messages as $msg) {
                        $message_ids[] = $msg['id'];
                        if (!array_key_exists( $msg['id'], $message_campaign_map)) {
                            $message_campaign_map[ $msg['id'] ] = $id;
                        }
                    }
                }
            }
        }
        
        // We don't display same message twice...
        $message_ids        = array_unique($message_ids);

        if( empty( $message_ids ) ) {
            return array();
        }
        $valid_messages     = $this->get_message_data( $message_ids, $preview_mode );
        
        foreach ($valid_messages as $id => $message_data) {
            // Remove message if required fields are missing
            if (empty($message_data) || empty($message_data['type'])) {
                unset( $valid_messages[$id] );
                continue;
            }
            // Remove message if message type is uninstalled
            $class_name = 'Icegram_Message_Type_' . str_replace(' ', '_', ucwords(str_replace('-', ' ', $message_data['type'])));
            if( !class_exists( $class_name ) ) {
                unset( $valid_messages[$id] );
                continue;
            }
            $message_data['delay_time']     = 0;
            $message_data['retargeting']    = '';
            $message_data['campaign_id']    = ($preview_mode) ? $_REQUEST['campaign_preview_id'] : '';

            // Pull display time and retargeting rule from campaign if possible
            $message_id = (!empty($message_data['original_message_id'])) ? $message_data['original_message_id'] : $id;
            if (!empty($message_campaign_map[ $message_id ])) {
                //modify campaign id 
                $message_data['campaign_id'] = apply_filters('modify_campaing_id'  , $message_campaign_map[ $message_id ] , $message_id) ;
                $campaign = $valid_campaigns[ floor($message_data['campaign_id']) ];
                if (!empty($campaign) && $campaign instanceof Icegram_Campaign) {
                    $message_meta_from_campaign = $campaign->get_message_meta_by_id( $message_id );
                    if (!empty($message_meta_from_campaign['time'])) {
                       $message_data['delay_time'] = $message_meta_from_campaign['time'];
                    }

                    //check if campaign is targeted to mobile at zero
                    $device_rule = $campaign->get_rule_value('device');
                    if($this->get_platform() !== 'laptop' && !empty($device_rule['mobile']) && $device_rule['mobile'] == 'yes'&& $message_data['delay_time'] == 0 && $message_data['type'] == 'popup'){
                        $message_data['ig_mobile_popup'] =  true;
                        if(!empty($message_data['triggers']) && !empty($message_data['triggers']['when_to_show'])){
                            $message_data['ig_mobile_popup'] =  ($message_data['triggers']['when_to_show'] == 'duration_on_page' && $message_data['triggers']['duration_on_page'] == 0 ) ? true : false;
                        }
                    }
                    //set delay time -1 if shortcode with content
                    foreach ($this->shortcode_instances as $i => $value) {
                        $campaign_ids = explode( ',', $value['campaigns']);
                        if(!empty($value['with_content']) && in_array($message_data['campaign_id'], $campaign_ids)){
                            $message_data['delay_time'] = -1;
                        }
                    }
                    $rule_value = $campaign->get_rule_value('retargeting');
                    $message_data['retargeting']   = !empty( $rule_value['retargeting'] ) ? $rule_value['retargeting'] : '';
                    $message_data['expiry_time']   = !empty( $rule_value['retargeting'] ) ? $rule_value['expiry_time'] : '';
                    $rule_value_retargeting_clicked = $campaign->get_rule_value('retargeting_clicked');
                    $message_data['retargeting_clicked']   = !empty( $rule_value_retargeting_clicked['retargeting_clicked'] ) ? $rule_value_retargeting_clicked['retargeting_clicked'] : '';
                    $message_data['expiry_time_clicked']   = !empty( $rule_value_retargeting_clicked['retargeting_clicked'] ) ? $rule_value_retargeting_clicked['expiry_time_clicked'] : '';
                    
                }
            }
            $valid_messages[$id] = $message_data;
        }
        $valid_messages = apply_filters( 'icegram_valid_messages', $valid_messages ); 
        
        return $valid_messages;
    }

    function get_valid_campaigns( $campaign_ids = array(), $skip_page_check = false ,$preview_mode = false) {
        global $wpdb;
        if ( empty( $campaign_ids ) ) {
            $sql = "SELECT pm.post_id 
                    FROM {$wpdb->prefix}posts AS p 
                    LEFT JOIN {$wpdb->prefix}postmeta AS pm ON ( pm.post_id = p.ID ) 
                    WHERE p.post_status = 'publish' ";
            // Filter handler within this file (and possibly others) will append to this SQL 
            // and provide arguments for wpdb->prepare if needed. 
            // First element in the array is SQL, remaining are values for placeholders in SQL
            $sql_params = apply_filters( 'icegram_get_valid_campaigns_sql', array($sql), array() );
            $campaign_ids = $wpdb->get_col( $wpdb->prepare( array_shift($sql_params), $sql_params ) );
        }
        $valid_campaigns = array();
        foreach ( (array) $campaign_ids as $campaign_id ) {
            $campaign = new Icegram_Campaign( $campaign_id );
            if ( $preview_mode || $campaign->is_valid( array('skip_page_check' =>  $skip_page_check) ) ) {
                $valid_campaigns[$campaign_id] = $campaign;
            } else {
                // Campgain is invalid!
            }

        }
        $valid_campaigns = apply_filters('icegram_valid_campaigns', $valid_campaigns);
        return $valid_campaigns;
    }

    function append_to_valid_campaigns_sql( $sql_params = array(), $options = array() ) {
        // Page check conditions
        //$pid = $_GET['page_id'];
        $pid = Icegram::get_current_page_id();
        $sql = " AND ( 
                pm.meta_key = 'icegram_campaign_target_rules' AND (
                ( pm.meta_value LIKE '%%%s%%' ) 
                OR ( pm.meta_value LIKE '%%%s%%' AND pm.meta_value LIKE '%%%s%%' AND pm.meta_value LIKE '%%%s%%' )
                ";
        $sql_params[] = 's:8:"sitewide";s:3:"yes";';
        $sql_params[] = 's:10:"other_page";s:3:"yes";';
        $sql_params[] = 's:7:"page_id";a:';
        $sql_params[] = serialize( (string) $pid );
        //local url
        $sql .= " OR ( pm.meta_value LIKE '%%%s%%' )";
        $sql_params[] = 's:9:"local_url";s:3:"yes";';
        if(!empty($_REQUEST['cache_compatibility']) && $_REQUEST['cache_compatibility'] == 'yes'){
            $is_home = (!empty($_REQUEST['is_home']) && $_REQUEST['is_home'] === 'true') ?  true : false ;
        }else{
            $is_home = (is_home() || is_front_page()) ? true : false;
        }
        if ($is_home === true )  {
            $sql .= " OR ( pm.meta_value LIKE '%%%s%%' )";
            $sql_params[] = 's:8:"homepage";s:3:"yes";';
        }
        $sql .=" ) )"; 
         
        $sql_params[0] .= $sql;  
        //s:9:"logged_in";s:3:"all";
        
        return $sql_params;
    }

    // Include all classes required for Icegram plugin
    function include_classes() {

        $classes = glob( $this->plugin_path . '/classes/*.php' );
        foreach ( $classes as $file ) {
            // Files with 'admin' in their name are included only for admin section
            if ( is_file( $file ) && ( (strpos($file, '-admin') >= 0 && is_admin()) || (strpos($file, '-admin') === false) ) ) {
                include_once $file;
            } 
        }

        // Load built in message types
        $icegram_message_type_basedirs = glob( $this->plugin_path . '/message-types/*' );
        // Allow other plugins to add new message types
        $icegram_message_type_basedirs = apply_filters( 'icegram_message_type_basedirs',  $icegram_message_type_basedirs );
        // Set up different message type classes
        foreach ( $icegram_message_type_basedirs as $dir ) {
            $type = basename ( $dir );
            $class_file = $dir . "/main.php";
            if( is_file( $class_file ) ) {
                include_once( $class_file );
            }
            $class_name = 'Icegram_Message_Type_' . str_replace(' ', '_', ucwords(str_replace('-', ' ', $type)));
            if (class_exists($class_name)) {
                $this->message_type_objs[ $type ] = new $class_name();
            }
        }
        do_action('ig_file_include');
        $this->message_types    = apply_filters( 'icegram_message_types', array() );
    }
    
    // Register Campaign post type
    function register_campaign_post_type() {
        $labels = array(
            'name'               => __( 'Campaigns', 'icegram' ),
            'singular_name'      => __( 'Campaign', 'icegram' ),
            'add_new'            => __( 'Add New Campaign', 'icegram' ),
            'add_new_item'       => __( 'Add New Campaign', 'icegram' ),
            'edit_item'          => __( 'Edit Campaign', 'icegram' ),
            'new_item'           => __( 'New Campaign', 'icegram' ),
            'all_items'          => __( 'Campaigns', 'icegram' ),
            'view_item'          => __( 'View Campaign', 'icegram' ),
            'search_items'       => __( 'Search Campaigns', 'icegram' ),
            'not_found'          => __( 'No campaigns found', 'icegram' ),
            'not_found_in_trash' => __( 'No campaigns found in Trash', 'icegram' ),
            'parent_item_colon'  => __( '', 'icegram' ),
            'menu_name'          => __( 'Icegram', 'icegram' )
        );

        $args = array(
            'labels'             => $labels,
            // 'menu_icon'          => 'dashicons-info', 
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'ig_campaign' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => $this->plugin_url . '/assets/images/icegram-logo-branding-18-white.png' ,
            'supports'           => array( 'title', 'editor' )
        );

        register_post_type( 'ig_campaign', $args );
    }

    // Register Message post type
    function register_message_post_type() {
        $labels = array(
            'name'               => __( 'Messages', 'icegram' ),
            'singular_name'      => __( 'Message', 'icegram' ),
            'add_new'            => __( 'Create New', 'icegram' ),
            'add_new_item'       => __( 'Create New Message', 'icegram' ),
            'edit_item'          => __( 'Edit Message', 'icegram' ),
            'new_item'           => __( 'New Message', 'icegram' ),
            'all_items'          => __( 'Messages', 'icegram' ),
            'view_item'          => __( 'View Message', 'icegram' ),
            'search_items'       => __( 'Search Messages', 'icegram' ),
            'not_found'          => __( 'No messages found', 'icegram' ),
            'not_found_in_trash' => __( 'No messages found in Trash', 'icegram' ),
            'parent_item_colon'  => __( '', 'icegram' ),
            'menu_name'          => __( 'Messages', 'icegram' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=ig_campaign',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'ig_message' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title' )
        );

        register_post_type( 'ig_message', $args );
    }

    
    function import( $data = array() ) {
        if ( empty( $data['campaigns'] ) && empty( $data['messages'] ) ) return;
              
        $default_theme = $default_type = '';
        $first_message_type = current( $this->message_types );

        if( is_array( $first_message_type ) ) {
            $default_type  = $first_message_type['type'];
            if( !empty( $first_message_type['themes'] ) ) {
                $default_theme = key( $first_message_type['themes'] );
            }
        }

        $new_campaign_ids = array();
        foreach ( (array) $data['campaigns'] as $campaign ) {

            $args = array( 
                        'post_content'   =>  ( !empty( $campaign['post_content'] ) ) ? esc_attr( $campaign['post_content'] ) : '',
                        'post_name'      =>  ( !empty( $campaign['post_title'] ) ) ? sanitize_title( $campaign['post_title'] ) : '',
                        'post_title'     =>  ( !empty( $campaign['post_title'] ) ) ? $campaign['post_title'] : '',
                        'post_status'    =>  ( !empty( $campaign['post_status'] ) ) ? $campaign['post_status'] : 'draft',
                        'post_type'      =>  'ig_campaign'
                     );

            $new_campaign_id    = wp_insert_post( $args );
            $new_campaign_ids[] = $new_campaign_id;

            if ( !empty( $campaign['target_rules'] ) ) {

                $defaults = array (
                                'homepage'   => 'yes',
                                'when'       => 'always',
                                'from'       => '',
                                'to'         => '',
                                'mobile'     => 'yes',
                                'tablet'     => 'yes',
                                'laptop'     => 'yes',
                                'logged_in'  => 'all'
                            );

                $target_rules = wp_parse_args( $campaign['target_rules'], $defaults );
                update_post_meta( $new_campaign_id, 'icegram_campaign_target_rules', $target_rules );
            }
            
            if ( !empty( $campaign['messages'] ) ) {

                $messages = array();

                foreach ( $campaign['messages'] as $message ) {

                    if ( !is_array( $message ) ) continue;

                    $args = array( 
                                'post_content'   =>  ( !empty( $message['message'] ) ) ? esc_attr( $message['message'] ) : '',
                                'post_name'      =>  ( !empty( $message['post_title'] ) ) ? sanitize_title( $message['post_title'] ) : '',
                                'post_title'     =>  ( !empty( $message['post_title'] ) ) ? $message['post_title'] : '',
                                'post_status'    =>  ( !empty( $message['post_status'] ) ) ? $message['post_status'] : 'publish',
                                'post_type'      =>  'ig_message'
                             );

                    $new_message_id = wp_insert_post( $args );
                    $new_message    = array(
                                        'id'    => $new_message_id,
                                        'time'  => ( !empty( $message['time'] ) ) ? $message['time'] : 0
                                    );
                    //for gallery + CTA another message
                    if(!empty($message['cta']) && $message['cta'] == 'cta_another_message' && !empty($message['cta_linked_message_id']) && $message['cta_linked_message_id'] == 'auto'){
                        $prev_message = end($messages);
                        $message['cta_linked_message_id'] = $prev_message['id'];
                        array_pop($messages);
                    }
                    $messages[]     = $new_message;

                    unset( $message['post_content'] );
                    unset( $message['time'] );

                    $message['id']  = $new_message_id;

                    $defaults = array (
                                    'post_title'    => '',
                                    'type'          => $default_type,
                                    'theme'         => $default_theme,
                                    'animation'     => '',
                                    'headline'      => '',
                                    'label'         => '',
                                    'link'          => '',
                                    'icon'          => '',
                                    'message'       => '',
                                    'position'      => '',
                                    'text_color'    => '#000000',
                                    'bg_color'      => '#ffffff',
                                    'custom_code'    => '',
                                    'id'            => ''
                                );
                    $icegram_message_data = wp_parse_args( $message, $defaults );
                    if ( !empty( $icegram_message_data ) ) {
                        update_post_meta( $new_message_id, 'icegram_message_data', $icegram_message_data );
                        update_post_meta( $new_message_id, 'icegram_message_preview_data', $icegram_message_data );
                    }
                }//foreach
                    
                if ( !empty( $campaign['messages'] ) ) {
                    update_post_meta( $new_campaign_id, 'messages', $messages );
                    update_post_meta( $new_campaign_id, 'campaign_preview', $messages );
                }
            }//if
        }
        return $new_campaign_ids;

    }

    function import_gallery_item(){
        if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'fetch_messages' && !empty($_REQUEST['campaign_id']) && !empty($_REQUEST['gallery_item'])){    
            $params = $_REQUEST;
            $imported_gallery_items = array();
            $url = 'https://www.icegram.com/gallery/wp-admin/admin-ajax.php?utm_source=ig_inapp&utm_campaign=ig_gallery&utm_medium='.$_REQUEST['campaign_id']; 
            $options = array(
            'timeout' => 15,
            'method' => 'POST',
            'body' => http_build_query( $params )
            ); 
            $response = wp_remote_request( $url, $options );
            $response_code = wp_remote_retrieve_response_code( $response );
            if ( $response_code == 200 ) {
                $new_campaign_ids = $this->import(json_decode($response['body'] ,true));
                if(!empty($new_campaign_ids)){
                    $imported_gallery_items = get_option('ig_imported_gallery_items');
                    $imported_gallery_items[] = $_REQUEST['campaign_id'];
                    update_option( 'ig_imported_gallery_items', $imported_gallery_items);
                    $location = admin_url( 'post.php?post='.$new_campaign_ids[0].'&action=edit');
                    header('Location:'.$location);
                    exit;      
                }else{
                    wp_safe_redirect($_SERVER['HTTP_REFERER']);
                }
            }   
        }
    }

    function import_sample_data ($data = array() ){
        $new_campaign_ids = $this->import($data);
        if( !empty( $new_campaign_ids ) ) {
            update_option( 'icegram_sample_data_imported', $new_campaign_ids );
        }
    }

    


    function get_sample_data() {

        return array(
                'campaigns' => array(
                        array(
                                'post_name'     => '',
                                'post_title'    => 'My First Icegram Campaign',
                                'target_rules'  => array (
                                                        'homepage'   => 'yes',
                                                        'when'       => 'always',
                                                        'from'       => '',
                                                        'to'         => '',
                                                        'mobile'     => 'yes',
                                                        'tablet'     => 'yes',
                                                        'laptop'     => 'yes',
                                                        'logged_in'  => 'all'
                                                    ),
                                'messages'      => array(
                                                        array (
                                                                'post_title'            => 'Get 2x more Contacts with Your Website',
                                                                'post_status'           => 'publish',
                                                                'time'                  => '0',
                                                                'type'                  => 'action-bar',
                                                                'theme'                 => 'hello',
                                                                'headline'              => 'Get 2x more Contacts with Your Website',
                                                                'label'                 => 'Show Me How',
                                                                'link'                  => '',
                                                                'icon'                  => '',
                                                                'message'               => 'Instant Results Guaranteed',
                                                                'position'              => '01',
                                                                'text_color'            => '#000000',
                                                                'bg_color'              => '#eb593c'
                                                            ),
                                                        array (
                                                                'post_title'            => '20% Off Coupon',
                                                                'post_status'           => 'publish',
                                                                'time'                  => '4',
                                                                'type'                  => 'messenger',
                                                                'theme'                 => 'social',
                                                                'animation'             => 'slide',
                                                                'headline'              => '20% Off - for you',
                                                                'label'                 => '',
                                                                'link'                  => '',
                                                                'icon'                  => '',
                                                                'message'               => "Hey there! We are running a <strong>special 20% off this week</strong> for registered users - like you. 

                                                                Use coupon <code>LOYALTY20</code> during checkout.",
                                                                'position'              => '22',
                                                                'text_color'            => '#000000',
                                                                'bg_color'              => '#ffffff'                                                        
                                                            ),
                                                        array (
                                                                'post_title'            => 'How this blog makes over $34,800 / month for FREE.',
                                                                'post_status'           => 'publish',
                                                                'time'                  => '10',
                                                                'type'                  => 'popup',
                                                                'theme'                 => 'air-mail',
                                                                'headline'              => 'How this blog makes over $34,800 / month for FREE.',
                                                                'label'                 => 'FREE INSTANT ACCESS',
                                                                'link'                  => '',
                                                                'icon'                  => '',
                                                                'message'               => "This website earns over $30,000 every month, every single month, almost on autopilot. I have 4 other sites with similar results. All I do is publish new regular content every week.

        <strong>Download my free kit to learn how I do this.</strong>

        <ul>
            <li>How to choose blog topics that create long term value</li>
            <li>The type of blog post that will make your site go viral</li>
            <li>How to free yourself from the routine tasks</li>
            <li>Resources and tips to get started quickly</li>
            <li>Private members club to connect with fellow owners</li>
        </ul>",
                                                                'text_color'            => '#000000',
                                                                'bg_color'              => '#ffffff'
                                                                                                                        
                                                            ),
                                                        array (
                                                                'post_title'            => 'Exclusive Marketing Report',
                                                                'post_status'           => 'publish',
                                                                'time'                  => '6',
                                                                'type'                  => 'toast',
                                                                'theme'                 => 'stand-out',
                                                                'animation'             => 'pop',
                                                                'headline'              => 'Exclusive Marketing Report',
                                                                'label'                 => '',
                                                                'link'                  => '',
                                                                'icon'                  => '',
                                                                'message'               => 'FREE for every subscriber. Click here to download it.',
                                                                'position'              => '02',
                                                                'text_color'            => '#000000',
                                                                'bg_color'              => '#ffffff'
                                                            )
                            
                                                    )
                            )
                    )
            );
    }

    function remove_preview_button() {
        global $post_type;
        if( $post_type == 'ig_message' || $post_type == 'ig_campaign' ) {
            ?>
                <style type="text/css">
                    #message.updated.below-h2{ display: none; }
                    #preview-action { display:none; }
                </style>
            <?php
        }
    }


    function remove_row_actions( $actions, $post ) {

        if ( empty( $post->post_type ) || ( $post->post_type != 'ig_campaign' && $post->post_type != 'ig_message' ) ) return $actions;
        
        unset( $actions['inline hide-if-no-js'] );
        unset( $actions['view'] );
        
        return $actions;

    }

    function identify_current_page() {
        global $post, $wpdb;
        
        $obj = get_queried_object();
        $id = 0;
        if( !empty( $obj->has_archive ) ) {
            $id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='page'", $obj->has_archive ) );
        } elseif( is_object( $post ) && isset( $post->ID ) ) {            
            $id = $post->ID;
        }        
        $id = apply_filters('icegram_identify_current_page', $id );
        self::$current_page_id = $id;
    }

    static function get_current_page_id() {
        global $post;
        if (!empty($_REQUEST['page_id']) && is_numeric($_REQUEST['page_id'])) {
            $post = get_post($_REQUEST['page_id']);
            setup_postdata( $post ); 
            // WPML check
            $id = apply_filters('icegram_identify_current_page', $post->ID );
            self::$current_page_id = $id;
        }
        return self::$current_page_id;
    }
    static function get_current_page_url() {
        if(!empty($_REQUEST['cache_compatibility']) && $_REQUEST['cache_compatibility'] == 'yes'){
            $pageURL = (!empty($_REQUEST['referral_url'])) ? $_REQUEST['referral_url'] : '';
        }else{
            $pageURL = 'http';
            if( isset($_SERVER["HTTPS"]) ) {
                if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
            }
            $pageURL .= "://";
            if ($_SERVER["SERVER_PORT"] != "80") {
                $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            } else {
                $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
            }
        }
        return $pageURL;
    }

    function wpml_get_parent_id( $id ) {
        global $post;
        if (function_exists('icl_object_id') && function_exists('icl_get_default_language') ) {
            $id = icl_object_id($id, $post->post_type, true, icl_get_default_language() );
        }
        return $id;
    }


    /**
     * Our implementation of wpautop to preserve script and style tags
     */
    function before_wpautop($pee) {
        if ( trim($pee) === '' ) {
            $this->_wpautop_tags = array();
            return '';
        }

        $tags = array();
        // Pull out tags and add placeholders
        list( $pee, $tags['pre'] ) = $this->_wpautop_add_tag_placeholders( $pee, 'pre' );
        list( $pee, $tags['script'] ) = $this->_wpautop_add_tag_placeholders( $pee, 'script' );
        list( $pee, $tags['style'] ) = $this->_wpautop_add_tag_placeholders( $pee, 'style' );
        $this->_wpautop_tags = $tags;

        if( !empty( $pre_tags ) )
            $pee = $this->_wpautop_replace_tag_placeholders( $pee, $pre_tags );
        if( !empty( $script_tags ) )
            $pee = $this->_wpautop_replace_tag_placeholders( $pee, $script_tags );
        if( !empty( $style_tags ) )
            $pee = $this->_wpautop_replace_tag_placeholders( $pee, $style_tags );

        return $pee;
    }

    function after_wpautop($pee) {
        if ( trim($pee) === '' || empty($this->_wpautop_tags) )
            return '';

        // Replace placeholders with original content
        if (!empty($this->_wpautop_tags['pre'])) {
            $pee = $this->_wpautop_replace_tag_placeholders( $pee, $this->_wpautop_tags['pre'] );
        }
        if (!empty($this->_wpautop_tags['script'])) {
            $pee = $this->_wpautop_replace_tag_placeholders( $pee, $this->_wpautop_tags['script'] );
        }
        if (!empty($this->_wpautop_tags['style'])) {
            $pee = $this->_wpautop_replace_tag_placeholders( $pee, $this->_wpautop_tags['style'] );
        }

        $this->_wpautop_tags = array();

        return $pee;
    }

    function _wpautop_add_tag_placeholders( $pee, $tag ) {
            $tags = array();

            if ( false !== strpos( $pee, "<{$tag}" ) ) {
                    $pee_parts = explode( "</{$tag}>", $pee );
                    $last_pee = array_pop( $pee_parts );
                    $pee = '';
                    $i = 0;

                    foreach ( $pee_parts as $pee_part ) {
                            $start = strpos( $pee_part, "<{$tag}" );

                            // Malformed html?
                            if ( false === $start ) {
                                    $pee .= $pee_part;
                                    continue;
                            }

                            $name = "<{$tag} wp-{$tag}-tag-$i></{$tag}>";
                            $tags[ $name ] = substr( $pee_part, $start ) . "</{$tag}>";

                            $pee .= substr( $pee_part, 0, $start ) . $name;
                            $i++;
                    }

                    $pee .= $last_pee;
            }

            return array( $pee, $tags );
    }

    function _wpautop_replace_tag_placeholders( $pee, $tags ) {
        if ( ! empty( $tags ) ) {
            $pee = str_replace( array_keys( $tags ), array_values( $tags ), $pee );
        }

        return $pee;
    }

    static function duplicate_in_db( $original_id){
        // Get access to the database
        global $wpdb;
        // Get the post as an array
        $duplicate = get_post( $original_id, 'ARRAY_A' );
        // Modify some of the elements
        $duplicate['post_title'] = $duplicate['post_title'].' '.__('Copy', 'icegram');
        $duplicate['post_status'] = 'draft';
        // Set the post date
        $timestamp = current_time('timestamp', 0);
        
        $duplicate['post_date'] = date('Y-m-d H:i:s', $timestamp);

        // Remove some of the keys
        unset( $duplicate['ID'] );
        unset( $duplicate['guid'] );
        unset( $duplicate['comment_count'] );

        // Insert the post into the database
        $duplicate_id = wp_insert_post( $duplicate );
        
        // Duplicate all taxonomies/terms
        $taxonomies = get_object_taxonomies( $duplicate['post_type'] );
            
        foreach( $taxonomies as $taxonomy ) {
            $terms = wp_get_post_terms( $original_id, $taxonomy, array('fields' => 'names') );
            wp_set_object_terms( $duplicate_id, $terms, $taxonomy );
        }

        // Duplicate all custom fields
        $custom_fields = get_post_custom( $original_id );
        foreach ( $custom_fields as $key => $value ) {
            if($key === 'messages'){
                $messages = unserialize($value[0]);
                foreach ($messages as &$message) {
                    $clone_msg_id = Icegram::duplicate_in_db($message['id']);
                    $message['id'] = $clone_msg_id;
                }
                $value[0] = serialize($messages);
            }
            add_post_meta( $duplicate_id, $key, maybe_unserialize($value[0]) );
        }
        return $duplicate_id;
    }

    static function duplicate( $original_id){
        $duplicate_id = Icegram::duplicate_in_db($original_id);
        $location = admin_url( 'post.php?post='.$duplicate_id.'&action=edit');
        header('Location:'.$location);
        exit;
    }

    public static function form_submission_validate_request($request_data){
        if(!empty($request_data)){
            // Check for Remote Rainmaker form submission request
            $request_data['ig_is_remote'] = false;
            $request_data['is_remote'] = false;
            if(!empty($request_data['ig_mode']) && $request_data['ig_mode'] === 'remote'){
                $ig_remote_url = $request_data['ig_remote_url'];
                if(!empty($request_data['ig_campaign_id'])){
                    $rules = get_post_meta( $request_data['ig_campaign_id'], 'icegram_campaign_target_rules', true );
                    if( !empty($rules['remote_urls']) && is_array($rules['remote_urls']) ){
                        foreach ($rules['remote_urls'] as $remote_url_pattern) {    
                            $valid = Icegram_Campaign::is_valid_url($remote_url_pattern , $ig_remote_url);
                            if( $valid ){
                                $request_data['ig_is_remote'] = true;
                                $request_data['is_remote'] = true;
                                break;
                            }
                        }
                        //TODO :: discard the the remote request and data
                        // if($request_data['ig_is_remote'] == false){
                        //  return array();
                        // }
                    }

                }
            }
        }
        return $request_data;
    }

}


function initialize_icegram() {
    global $icegram;

    // i18n / l10n - load translations
    load_plugin_textdomain( 'icegram', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' ); 

    $icegram = new Icegram();
    do_action('icegram_loaded');
}

add_action( 'plugins_loaded', 'initialize_icegram' );
register_activation_hook( __FILE__, array( 'Icegram', 'install' ) );