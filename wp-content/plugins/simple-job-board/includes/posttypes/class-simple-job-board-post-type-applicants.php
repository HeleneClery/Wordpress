<?php

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
/**
 * Simple_Job_Board_Post_Type_Applicants Class
 *
 * This class is used to define the "jobpost_applicants" custom post type.
 * 
 * @link        https://wordpress.org/plugins/simple-job-board
 * @since       2.2.0
 * @since       2.4.0   Added for "Selected Information" Column in Applicant Listing & Revised Input/Output Sanitization & Escaping
 *
 * @package     Simple_Job_Board
 * @subpackage  Simple_Job_Board/includes/posttypes
 * @author      PressTigers <support@presstigers.com>
 */
if (!class_exists('Simple_Job_Board_Post_Type_Applicants')) {

    class Simple_Job_Board_Post_Type_Applicants {

        /**
         * Initialize the class and set its properties.
         *
         * @since   2.2.0
         */
        public function __construct() {

            // Add Hook into the 'init()' action
            add_action('init', array($this, 'simple_job_board_init'));

            // Add Hook into the 'admin_init()' action
            add_action('admin_init', array($this, 'simple_job_board_admin_init'));
        }

        /**
         * A function hook that the WordPress core launches at 'init' points
         *
         * @since   2.2.0
         */
        public function simple_job_board_init() {

            $this->createPostType();
        }

        /**
         * A function hook that the WordPress core launches at 'admin_init' points
         *
         * @since   2.2.0
         */
        public function simple_job_board_admin_init() {

            // Hook - Delete Uploads on Applicant Deletion
            add_action('before_delete_post', array($this, 'job_board_delete_uploads'));

            // Hook - Post Type -> Applicants ->  Add New Column
            add_filter('manage_edit-jobpost_applicants_columns', array($this, 'job_board_applicant_list_columns'));

            // Hook - Post Type -> Applicants ->  Add Value to New Column
            add_filter('manage_jobpost_applicants_posts_custom_column', array($this, 'job_board_applicant_list_columns_value'), 10, 2);
        }

        /**
         * Create Applicants Post Type.
         *
         * @since   2.2.0
         */
        public function createPostType() {
            if (post_type_exists("jobpost_applicants"))
                return;

            /**
             * Post Type -> Applicants
             */
            $plural = esc_html__('Applicants', 'simple-job-board');

            $labels_applicants = array(
                'edit_item' => sprintf(esc_html__('Edit %s', 'simple-job-board'), $plural),
            );

            $args_applicants = array(
                'label' => $plural,
                'labels' => $labels_applicants,
                'description' => sprintf(esc_html__('List of %s with their resume.', 'simple-job-board'), $plural),
                'public' => FALSE,
                'exclude_from_search' => FALSE,
                'publicly_queryable' => FALSE,
                'show_ui' => TRUE,
                'show_in_menu' => 'edit.php?post_type=jobpost',
                'show_in_nav_menus' => FALSE,
                'menu_icon' => 'dashicons-clipboard',
                'can_export' => TRUE,
                'capabilities' => array(
                    'create_posts' => FALSE,
                ),
                'map_meta_cap' => TRUE,
                'hierarchical' => FALSE,
                'supports' => array('editor')
            );

            // Register Applicant Post Type.
            register_post_type("jobpost_applicants", apply_filters("register_post_type_jobpost_applicants", $args_applicants));
        }

        /**
         * Delete Uploads on Applicant Deletion.
         *
         * @since   2.0.0
         * 
         * @param   int     $postId
         * @return  void
         */
        public function job_board_delete_uploads($postId) {

            global $post_type;
            if ($post_type == 'jobpost_applicants' && '' != get_post_meta($postId, 'resume_path', TRUE))
                unlink(get_post_meta($postId, 'resume_path', TRUE));
        }

        /**
         * Applicants -> Add New Column.
         *
         * @since   1.0.0
         * @since   2.4.0   Modified Applicant Name with Selected Information Column
         * 
         * @param   array   $columns    Applicant's listing Columns.
         * @return  array   $columns    Applicant's listing Columns.
         */
        public function job_board_applicant_list_columns($columns) {

            $columns = array(
                'cb' => '<input type="checkbox" />',
                'title' => esc_html__('Job Applied for', 'simple-job-board'),
                'taxonomy' => esc_html__('Categories', 'simple-job-board'),
                'date' => esc_html__('Date', 'simple-job-board'),
                'selected_information' => '&nbsp;' . __('Selected Information', 'simple-job-board') . '<br>( ' . __('Default Applicant Name', 'simple-job-board') . ' )',
            );
            return $columns;
        }

        /**
         * Applicants ->  Add Value to New Column.
         *
         * @since   1.0.0
         * @since   2.4.0   Modified Applicant Name with Selected Information Column
         * 
         * @param   array   $column    
         * @param   int     $post_id
         * @return  void
         */
        public function job_board_applicant_list_columns_value($column, $post_id) {

            // Applicant Keys
            $keys = get_post_custom_keys($post_id);
            $parent_id = wp_get_post_parent_id($post_id);
            $parent_keys = get_post_custom_keys($post_id);
            $selected_info = '';

            switch ($column) {
                case 'selected_information':
                    $is_checked = 0;
                    $column_key = '';

                    // Get selected information according to user selected field
                    if (NULL != $parent_keys):
                        foreach ($parent_keys as $key) :
                            if (substr($key, 0, 7) == 'jobapp_'):
                                $val = get_post_meta($parent_id, $key, TRUE);
                                $val = unserialize($val);
                                if (isset($val['applicant_column']) && 'checked' === $val['applicant_column']) {
                                    $is_checked = 1;
                                    $column_key = $key;
                                }
                            endif;
                        endforeach;
                    endif;

                    // Display values for selected information
                    if (NULL != $keys):
                        foreach ($keys as $key) {
                            if ('jobapp_' === substr($key, 0, 7)) {

                                // For backward compaitability
                                if (1 !== $is_checked && NULL == $column_key) {

                                    $place = strpos($key, 'name');
                                    if (!empty($place)) {
                                        $selected_info = get_post_meta($post_id, $key, TRUE);
                                        break;
                                    }
                                } else {

                                    // Display value from user selected field
                                    $selected_info = get_post_meta($post_id, $column_key, TRUE);
                                }
                            }
                        }
                    endif;
                    
                    if ( is_serialized( $selected_info ) ) {
                        $selected_info = unserialize($selected_info);
                        if ( NULL != $selected_info ) {
                            $count = sizeof( $selected_info );
                            foreach ( $selected_info as $val ) {
                                $selected_info = sprintf('<a href="%s">%s</a>', esc_url(add_query_arg(array('post' => $post_id, 'action' => 'edit'), 'post.php')), esc_html( $val ) );
                                echo $selected_info;
                                if ($count > 1) {
                                    echo ',&nbsp;';
                                }                                
                                $count--;
                            }
                        }
                    } else {
                        $selected_info = sprintf('<a href="%s">%s</a>', esc_url(add_query_arg(array('post' => $post_id, 'action' => 'edit'), 'post.php')), esc_html($selected_info));
                        echo $selected_info;
                    }

                    break;
                case 'taxonomy' :
                    $parent_id = wp_get_post_parent_id($post_id);
                    $terms = get_the_terms($parent_id, 'jobpost_category');

                    if (!empty($terms)) {
                        $out = array();
                        foreach ($terms as $term) {
                            $out[] = sprintf('<a href="%s">%s</a>', esc_url(add_query_arg(array('post_type' => get_post_type($parent_id), 'jobpost_category' => $term->slug), 'edit.php')), esc_html(sanitize_term_field('name', $term->name, $term->term_id, 'jobpost_category', 'display'))
                            );
                        }
                        echo join(', ', $out);
                    } else {
                        /* If no terms were found, output a default message. */
                        esc_html_e('No Categories', 'simple-job-board');
                    }
                    break;
            }
        }

    }

}