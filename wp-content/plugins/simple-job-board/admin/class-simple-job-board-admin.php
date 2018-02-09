<?php
/**
 * Simple_Job_Board_Admin Class
 * 
 * The admin-specific functionality of the plugin. Defines the plugin name,
 * version and two examples hooks for how to enqueue the admin-specific 
 * stylesheet and JavaScript.
 *
 * @link       https://wordpress.org/plugins/simple-job-board
 * @since      1.0.0
 * @since      2.3.2    Admin Footer Text Branding
 * @since      2.4.0    Updated Outdated Scripts & Styles
 * @since      2.4.4    Added User Capability for Resume Download
 * 
 * @package    Simple_Job_Board
 * @subpackage Simple_Job_Board/admin
 * @author     PressTigers <support@presstigers.com>
 */
class Simple_Job_Board_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $simple_job_board    The ID of this plugin.
     */
    private $simple_job_board;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $simple_job_board       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($simple_job_board, $version) {
        $this->simple_job_board = $simple_job_board;
        $this->version = $version;

        /**
         * The class responsible for defining all the meta options under custom post type in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-simple-job-board-admin-meta-boxes-init.php';

        /**
         * The class responsible for writing rules in htaccess file and to protect the file from direct link.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-simple-job-board-rewrite.php';

        /**
         * The class responsible for defining all the plugin settings that occur in the front end area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-simple-job-board-admin-settings-init.php';

        /**
         * The class responsible for Applicant's detail in the back end area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-simple-job-board-applicants.php';

        /**
         * The class responsible for creating the job board shortcode generator functionality in TinyMCE through its button.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-simple-job-board-admin-shortcode-generator.php';

        /**
         * The class responsible for creating the add-ons page in admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-simple-job-board-admin-add-ons.php';
        
        /**
         * The class responsible for handling resume download.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-simple-job-board-resume-download-handler.php';
        
        /**
         * The class responsible to add the SJB widgets in admin Widgets area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-simple-job-board-widgets-init.php';
        
        // Filter -> Checks for User Certain apability.
        add_filter('user_has_cap', array($this, 'sjb_user_has_capability'), 10, 3);

        // Filter -> Footer Branding - with PressTigers Logo
        add_filter('admin_footer_text', array($this, 'sjb_powered_by'));
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        
        // Enqueue Core Admin Styles
        wp_enqueue_style($this->simple_job_board, plugin_dir_url(__FILE__) . 'css/simple-job-board-admin.css', array('wp-color-picker'), '1.1.0', 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        if (is_admin()) {
            
            // Simple Job Board Admin Core JS File
            wp_enqueue_script( $this->simple_job_board . '-admin', plugin_dir_url(__FILE__) . 'js/simple-job-board-admin.js', array('jquery', 'jquery-ui-sortable', 'wp-color-picker'), '1.3.0', TRUE );
                       
            // Localize Script for Making jQuery Stings Translation Ready
            wp_localize_script( $this->simple_job_board . '-admin', 'application_form', array(
                    'settings_jquery_alerts' => array(
                    'delete' => esc_html__('Delete', 'simple-job-board'),
                    'required' => esc_html__('Required', 'simple-job-board'),
                    'field_name' => esc_html__('Field Name', 'simple-job-board'),
                    'empty_feature_name' => esc_html__('Please fill out job feature.', 'simple-job-board'),
                    'empty_field_name' => esc_html__('Please fill out application form field name.', 'simple-job-board'),
                    'applicant_listing_col' => esc_html__('Expose in Applicant Listing', 'simple-job-board'),
                    ),
                )
            );
            
            // Register Alpha Color Picker Script
            wp_register_script( 'wp-color-picker-alpha', plugin_dir_url(__FILE__) . 'js/wp-color-picker-alpha.js', array(), '1.2.2', TRUE );
        }
    }
    
    /**
     * Checks if a user has a certain capability.
     *
     * @since   2.4.4
     * 
     * @param   array   $allcaps    User Capabilities 
     * @param   array   $caps       Actual capabilities for meta capability.
     * @param   array   $args       Parameters passed to has_cap(),
     * @return  array   $allcaps    Modified Capabilities along with Resume Download
     */
    public function sjb_user_has_capability( $allcaps, $caps, $args) {      
        
        if ( isset( $caps[0] ) && 'download_resume' == $caps[0]) {            
            if ( array_key_exists( 'edit_others_posts', $allcaps ) ) {        
                $allcaps['download_resume'] = TRUE;
            } 
        }
        
        return $allcaps;
    }

    /**
     * Replace admin footer text with PressTigers branding.
     *
     * @since    2.3.2
     */
    public function sjb_powered_by($text) {
        $screen = get_current_screen();
        
        // SJB Admin Pages Ids
        $sjb_pages = array(
            'jobpost_page_job-board-settings',
            'jobpost_page_sjb-add-ons',
            'edit-jobpost_applicants',
            'jobpost_applicants',
            'edit-jobpost',
            'jobpost',
            'edit-jobpost_category',
            'edit-jobpost_job_type',
            'edit-jobpost_location',
        );

        if (is_admin() && ( in_array($screen->id, apply_filters('sjb_pages', $sjb_pages)) )) {
            $text = '<a href="'. esc_url('http://www.presstigers.com/') .'" target="_blank"><img src="' . untrailingslashit( plugins_url( basename( plugin_dir_path( __DIR__ ) ), basename( __DIR__ ) ) ) . '/admin/images/powerByIcon.png" alt="Powered by PressTigers"></a>';
        }
        return $text;
    }
}