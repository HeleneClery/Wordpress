<?php if (!defined('ABSPATH')) { exit; } // Exit if accessed directly
/**
 * Simple_Job_Board_Meta_box_Job_Features Class
 * 
 * This meta box is designed to create job features and list the user defined job features.
 *
 * @link        https://wordpress.org/plugins/simple-job-board
 * @since       2.2.3
 * @since       2.3.2   Added Job Features Labels' Editing Feature
 * @since       2.4.0   Improved Sanitization & Escaping of Job Features' Inputs & Outputs
 * @since       2.4.5   Fixed the job application form builder issue with multilingual characters.
 *
 * @package     Simple_Job_Board
 * @subpackage  Simple_Job_Board/admin/partials/meta-boxes
 * @author      PressTigers <support@presstigers.com>
 */

class Simple_Job_Board_Meta_Box_Job_Features {

    /**
     * Meta box for Job Features.
     * 
     * @since   2.2.3
     */
    public static function sjb_meta_box_output($post) {
        global $jobfields;

        // Add a nonce field so we can check for it later.
        wp_nonce_field('sjb_jobpost_meta_box', 'jobpost_meta_box_nonce');

        /*
         * Use get_post_meta() to retrieve an existing value
         * from the database and use the value for the form.
         */
        ?>

        <div class="job_features meta_option_panel jobpost_fields">
            <ul id="job_features">

                <?php
                $keys = get_post_custom_keys($post->ID);

                //getting setting page saved options
                $settings_options = unserialize(get_option('jobfeature_settings_options'));

                //check Array differnce when $keys is not NULL
                if (NULL == $keys) {

                    //"Add New" job check
                    $removed_options = $settings_options;
                } elseif (NULL == $settings_options) {
                    $removed_options = '';
                } else {

                    //Remove the same option from post meta and options
                    $removed_options = array_diff_key($settings_options, get_post_meta($post->ID));
                }

                if (NULL != $keys):
                    foreach ($keys as $key):
                        if (substr($key, 0, 11) == 'jobfeature_') {
                            $val = get_post_meta($post->ID, $key, TRUE);
                            
                            if (is_serialized($val)) {
                                $val = unserialize( $val );
                                $val = ( is_array( $val ) ) ? array_map('esc_attr', $val ) : esc_attr( $val );
                            }

                            /**
                             * New Label Index Insertion:
                             * 
                             * - Addition of new index "label"
                             * - Data Legacy Checking  
                             */
                            $label = isset($val['label']) ? $val['label'] : __(ucwords(str_replace('_', ' ', substr($key, 11))), 'simple-job-board');
                            $value = isset($val['value']) ? $val['value'] : $val;

                            echo '<li><label class="sjb-editable-label">'
                            . $label
                            . '</label>'
                            . '<input type="hidden" name="' . $key . '[label]" value="' . $label . '" />';

                            // Setting options meta Fileds button to Empty
                            $button = '<div class="button removeField">' . esc_html__('Delete', 'simple-job-board') . '</div>';
                            echo '<input type="text" id="' . $key . '" name="' . $key . '[value]" value="' . $value . '" /> &nbsp; ' . $button . '</li>';
                        }
                    endforeach;
                endif;

                // Adding setting page features to jobpost
                if (NULL != $removed_options):
                    if ( !isset( $_GET['action'] ) ):
                        foreach ($removed_options as $key => $val):
                            if (substr($key, 0, 11) == 'jobfeature_') {
                                $val = ( is_array( $val ) )? array_map('esc_attr', $val ) : esc_attr( $val );
                                
                                /**
                                 * New Label Index Insertion:
                                 * 
                                 * - Addition of new index "label"
                                 * - Data Legacy Checking  
                                 */
                                $label = isset($val['label']) ? $val['label'] : __(ucwords(str_replace('_', ' ', substr($key, 11))), 'simple-job-board');
                                $value = isset($val['value']) ? $val['value'] : $val;

                                // Convert Empty Value Parameter to NULL
                                if ('empty' === $value) {
                                    $value = '';
                                }

                                echo '<li><label class="sjb-editable-label">'
                                . $label
                                . '</label>'
                                . '<input type="hidden" name="' . $key . '[label]" value="' . $label . '" />';
                                echo '<input type="text" id="' . $key . '" name="' . $key . '[value]" value="' . $value . '" /> &nbsp; <div class="button removeField">' . esc_html__('Delete', 'simple-job-board') . '</div></li>';
                            }
                        endforeach;
                    endif;
                endif;
                ?>
            </ul>
        </div>
        <div class="clearfix clear"></div>
        
        <table id="jobfeatures_form" class="alignleft">
            <thead>
                <tr>
                    <th><label for="jobFeature"><?php echo esc_html__('Feature', 'simple-job-board'); ?></label></th>
                    <th><label for="jobFeatureVal"><?php echo esc_html__('Value', 'simple-job-board'); ?></label></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="jobFeature"><input type="text" id="jobfeature_name" /></td>
                    <td><input type="text" id="jobfeature_value" /></td>
                    <td><div class="button" id="addFeature"><?php echo esc_html__('Add Field', 'simple-job-board'); ?></div></td>
                </tr>
            </tbody>
        </table>
        <div class="clearfix clear"></div>

        <?php
    }

    /**
     * Save job features meta box.
     * 
     * @since   2.2.3
     * 
     * @param   int     $post_id    Post id
     * @return  void
     */
    public static function sjb_save_jobpost_meta($post_id) {

        // Delete previous stored fields.
        $old_keys = get_post_custom_keys($post_id);
        foreach ($old_keys as $key => $val):
            if ( substr($val, 0, 11) == 'jobfeature_' ) {
                delete_post_meta($post_id, $val); //Remove meta from the db.
            }
        endforeach;
        
        $POST_data = filter_input_array( INPUT_POST );
        
        // Add new value.
        foreach( $POST_data as $key => $val ):
            if ( substr($key, 0, 11 ) == 'jobfeature_' ) { // Make sure that it is set.
                $key = preg_replace('/[^\p{L} 0-9]/u', '_', $key);
                $data = serialize( array_map('sanitize_text_field', filter_input( INPUT_POST, $key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ) ); 
                update_post_meta( $post_id, $key, $data ); // Add new value.
            }
        endforeach;
    }

}