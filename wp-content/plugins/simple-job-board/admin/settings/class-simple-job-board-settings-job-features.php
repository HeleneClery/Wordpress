<?php if (!defined('ABSPATH')) { exit; } // Exit if accessed directly
/**
 * Simple_Job_Board_Settings_Job_Features Class
 * 
 * This file used to define the settings for the job features. User can create 
 * generic job features that will add to the newly created job.
 *
 * @link        https://wordpress.org/plugins/simple-job-board
 * @since       2.2.3
 * @since       2.3.2   Added Application Form Labels' Editing Feature
 * @since       2.4.0   Revised Inputs & Outputs Sanitization & Escaping
 *
 * @package     Simple_Job_Board
 * @subpackage  Simple_Job_Board/admin/settings
 * @author      PressTigers <support@presstigers.com>
 */

class Simple_Job_Board_Settings_Job_Features {

    /**
     * Initialize the class and set its properties.
     *
     * @since   2.2.3
     */
    public function __construct() {

        // Filter -> Add Settings Job Features Tab
        add_filter('sjb_settings_tab_menus', array($this, 'sjb_add_settings_tab'), 40);

        // Action -> Add Settings Job Features Section 
        add_action('sjb_settings_tab_section', array($this, 'sjb_add_settings_section'), 40);

        // Action -> Save Settings Job Features Section 
        add_action('sjb_save_setting_sections', array($this, 'sjb_save_settings_section'));
    }

    /**
     * Add Settings Job Features Tab.
     *
     * @since    2.2.3
     * 
     * @param    array  $tabs  Settings Tab
     * @return   array  $tabs  Merge array of Settings Tab with Job Features Tab.
     */
    public function sjb_add_settings_tab($tabs) {

        $tabs['job_features'] = esc_html__('Job Features', 'simple-job-board');
        return $tabs;
    }

    /**
     * Add Settings Job Features section.
     *
     * @since    2.2.3
     */
    public function sjb_add_settings_section() {
        ?>
        <!-- Job Features -->
        <div id="settings-job_features" class="sjb-admin-settings" style="display: none;">                
            <h4 class="first"><?php esc_html_e('Default Feature List', 'simple-job-board'); ?></h4>
            <div class="sjb-section settings-fields">
                <form method="post" action="" id="job_feature_form">
                    <ul id="settings_job_features">
                        <?php
                        // Get Job Features From DB
                        $job_features = get_option('jobfeature_settings_options');
                        $fields = unserialize($job_features);

                        // Display Job Features
                        if (NULL != $fields):
                            foreach ( $fields as $field => $val ) {
                                if ( 'jobfeature_' == substr( $field, 0, 11) ) {
                                    
                                    // Escaping all array value
                                    $val = ( is_array( $val ) )? array_map('esc_attr', $val ) : esc_attr( $val );
                                    $field = preg_replace('/[^\p{L} 0-9]/u', '_', $field);
                                                                       
                                    /**
                                     * New Label Index Insertion:
                                     * 
                                     * - Addition of new index "label"
                                     * - Data Legacy Checking  
                                     */
                                    $label = isset($val['label']) ? $val['label'] : esc_html__(ucwords(str_replace('_', ' ', substr($field, 11))), 'simple-job-board');
                                    $value = isset($val['value']) ? $val['value'] : $val;
                                    $feature_value = ( 'empty' === $value ) ? '<input type="text" value=" "  name="' . $field . '[value]" />' : '<input type="text" value="' . $value . '"  name="' . $field . '[value]" />';
                                    echo '<li class="' . $field . '"><strong>' . esc_html__('Field Name', 'simple-job-board') . ': </strong><label class="sjb-editable-label" for="' . $field . '">' . $label . '</label><input type="hidden" name="' . $field . '[label]" value="' . $label . '"  >' . $feature_value . ' &nbsp; <div class="button removeField">' . esc_html__('Delete', 'simple-job-board') . '</div></li>';
                                }
                            }
                        endif;
                        ?>
                    </ul>
                    <input type="hidden" name="job_features" value="job_features" />
                    <input type="hidden" value="1" name="admin_notices" />
                </form>
            </div> 

            <!-- Add Job Features -->
            <h4><?php _e('Add New Feature', 'simple-job-board'); ?></h4>
            <div class="sjb-section">
                <div class="sjb-content-featured">
                    <div class="sjb-form-group">
                        <label class="sjb-featured-label"><?php esc_html_e('Feature', 'simple-job-board'); ?></label>
                        <input type="text" id="settings_jobfeature_name" />
                    </div>
                    <div class="sjb-form-group">
                        <label class="sjb-featured-label"><?php esc_html_e('Value', 'simple-job-board'); ?></label>
                        <input type="text" id="settings_jobfeature_value" />
                    </div>
                    <input type="submit" class="button" id="settings_addFeature" value="<?php echo esc_html__('Add Field', 'simple-job-board'); ?>" />
                </div>
            </div>
            <input type="submit" name="jobfeature_submit" id="jobfeature_form" class="button button-primary" value="<?php echo esc_html__('Save Changes', 'simple-job-board'); ?>" />
        </div>

        <?php
    }

    /**
     * Save Settings Job Features Section.
     * 
     * This function is used to save the generic job features. All these 
     * features are displayed on creation of new job.
     *
     * @since    2.2.3
     */
    public function sjb_save_settings_section() {
        $POST_data = filter_input_array( INPUT_POST );
        $features = filter_input( INPUT_POST, 'job_features' );
        
        // Save Form Data to WP Option
        if ( !empty( $POST_data ) && ( $features ) ) {
            $job_features = serialize( $POST_data );

            // Save Job Features in WP Options || Add Option if not exist.
            (FALSE !== get_option('jobfeature_settings_options')) ?
                update_option('jobfeature_settings_options', $job_features) :
                add_option('jobfeature_settings_options', $job_features, '', 'no');
        }
    }

}