<?php if (!defined('ABSPATH')) { exit; } // Exit if accessed directly
/**
 * Simple_Job_Board_Settings_Init Class
 * 
 * This is used to define job settings. This file contains following settings
 * 
 * - General
 * - Appearance
 * - Job Features
 * - Application Form Fields
 * - Filters
 * - Email Notifications
 * - Upload File Extensions
 * 
 * @link        https://wordpress.org/plugins/simple-job-board
 * @since       2.2.3
 * 
 * @package     Simple_Job_Board
 * @subpackage  Simple_Job_Board/admin
 * @author      PressTigers <support@presstigers.com> 
 */

class Simple_Job_Board_Settings_Init {

    /**
     * Initialize the class and set its properties.
     *
     * @since   2.2.3
     */
    public function __construct() {
        
        /**
         * The class responsible for defining all the plugin general settings that occur in the frontend area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/settings/class-simple-job-board-settings-general.php';
        
        // Check if General Settings Class Exists
        if ( class_exists ( 'Simple_Job_Board_Settings_General' ) ) {
            
            // Initialize General Settings class           
            new Simple_Job_Board_Settings_General();
        }
        
        /**
         * The class responsible for defining all the plugin appearance settings that occur in the frontend area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/settings/class-simple-job-board-settings-appearance.php';
        
        // Check if  Appearance Settings Class Exists
        if ( class_exists ( 'Simple_Job_Board_Settings_Appearance' ) ) {
            
            // Initialize Appearance Settings Class           
            new Simple_Job_Board_Settings_Appearance();
        }
        
        /**
         * The class responsible for defining all the plugin job features settings that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/settings/class-simple-job-board-settings-job-features.php';
        
        // Check if  Job Features Settings Class Exists
        if ( class_exists ( 'Simple_Job_Board_Settings_Job_Features' ) ) {
            
            // Initialize Job Features Class           
            new Simple_Job_Board_Settings_Job_Features();
        }
        
        /**
         * The class responsible for defining all the plugin job application form settings that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/settings/class-simple-job-board-settings-application-form-fields.php';
        
        // Check if Job Application Form Settings Class Exists
        if ( class_exists ( 'Simple_Job_Board_Settings_Application_Form_Fields' ) ) {
            
            // Initialize Job Application Form Settings Class           
            new Simple_Job_Board_Settings_Application_Form_Fields();
        }
        
        /**
         * The class responsible for defining all the plugin job filters settings that occur in the frontend area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/settings/class-simple-job-board-settings-filters.php';
        
        // Check if Job Filters Settings Class Exists
        if ( class_exists ( 'Simple_Job_Board_Settings_Filters' ) ) {
            
            // Initialize Job Filters Settings Class           
            new Simple_Job_Board_Settings_Filters();
        }
        
        /**
         * The class responsible for defining all the plugin email notifications settings that occur in the frontend area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/settings/class-simple-job-board-settings-email-notifications.php';
        
        // Check if Email Notifications Settings Class Exists
        if ( class_exists ( 'Simple_Job_Board_Settings_Email_Notifications' ) ) {
            
            // Initialize Email Notifications Settings Class           
            new Simple_Job_Board_Settings_Email_Notifications();
        }        
        
        /**
         * The class responsible for defining all the plugin uplaod file extensions settings that occur in the frontend area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/settings/class-simple-job-board-settings-upload-file-extensions.php';
        
        // Check if Upload File Extension Settings Class Exists
        if (class_exists ( 'Simple_Job_Board_Settings_Upload_File_Extensions' )) {
            
            // Initialize Upload File Extension Settings Class           
            new Simple_Job_Board_Settings_Upload_File_Extensions();
        }  
        
        // Action - Add Settings Menu
        add_action( 'admin_menu', array($this, 'sjb_admin_menu'), 12 );

        // Action - Save Settings
        add_action( 'admin_notices', array($this, 'sjb_save_settings' ) );
    }

    /**
     * Add Settings Page Under Job Board Menu.
     * 
     * @since   2.0.0
     */
    public function sjb_admin_menu() {
        
        add_submenu_page('edit.php?post_type=jobpost', esc_html__('Settings', 'simple-job-board'), esc_html__('Settings', 'simple-job-board'), 'manage_options', 'job-board-settings', array($this, 'sjb_settings_tab_menu'));
    }

    /**
     * Add Settings Tab Menu.
     * 
     * @Since   2.0.0
     */
    public function sjb_settings_tab_menu() {
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Settings', 'simple-job-board'); ?></h1>        
            <h2 class="nav-tab-wrapper">
                
                <?php
                /**
                 * Filter the Settings Tab Menus. 
                 * 
                 * @since 2.2.0 
                 * 
                 * @param array (){
                 *     @type array Tab Id => Settings Tab Name
                 * }
                 */
                $settings_tabs = apply_filters('sjb_settings_tab_menus', array());

                $count = 1;
                foreach ( $settings_tabs as $key => $tab_name ) {
                    $active_tab = ( 1 === $count ) ? 'nav-tab-active' : '';
                    echo '<a href="#settings-' . sanitize_key($key) . '" class="nav-tab ' . sanitize_html_class( $active_tab ) . ' ">' . esc_attr( $tab_name ) . '</a>';
                    $count++;
                }
                ?>
                
            </h2>
            
            <?php
            /**
             * Action -> Display Settings Sections.  
             * 
             * @since 2.2.3 
             */
            do_action('sjb_settings_tab_section');
            ?>
            
        </div>

        <?php
    }    

    /**
     * Save Settings.
     * 
     * @since   2.2.3
     */
    public function sjb_save_settings() {
        
        /**
         * Action -> Save Setting Sections.  
         * 
         * @since   2.2.3 
         */
        do_action('sjb_save_setting_sections');

        // Admin Notices
        if ( ( NULL != filter_input( INPUT_POST, 'admin_notices') ) ) {
?>
        <div class="updated">
            <p><?php echo apply_filters('sjb_saved_settings_notification', esc_html__('Settings have been saved.', 'simple-job-board')); ?></p>
        </div>
<?php
        }
    }
}
new Simple_Job_Board_Settings_Init();