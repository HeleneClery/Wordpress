<?php
/**
 * Display the job application form.
 *
 * Override this template by copying it to yourtheme/simple_job_board/single-jobpost/job-application.php
 *
 * @author 	PressTigers
 * @package     Simple_Job_Board
 * @subpackage  Simple_Job_Board/Templates
 * @version     1.0.0
 * @since       2.1.0
 * @since       2.2.2   Added more @hooks in application form.
 * @since       2.3.0   Added "sjb_job_application_template" filter & "sjb_job_application_form_fields" filter.
 */
ob_start();
global $post;

/**
 * Fires on job detail page before displaying job application section.
 *                  
 * @since   2.1.0                   
 */
do_action('sjb_job_application_before');
?>

<!-- Start Job Application Form
================================================== -->
<form class="jobpost-form" id="sjb-application-form" name="c-assignments-form"  enctype="multipart/form-data">
    <h3><?php echo apply_filters('sjb_job_application_form_title', esc_html__('Apply Online', 'simple-job-board')); ?></h3>    
    <div class="row">
        <div class="col-md-12">
            <?php
            /**
             * Fires on job detail page at start of job application form. 
             *                 
             * @since   2.3.0                   
             */
            do_action('sjb_job_application_form_fields_start');

            $keys = get_post_custom_keys(get_the_ID());
            if (NULL != $keys):
                foreach ($keys as $key):
                    if (substr($key, 0, 7) == 'jobapp_'):
                        $val = get_post_meta(get_the_ID(), $key, TRUE);
                        $val = unserialize($val);
                        $is_required = isset($val['optional']) ? "checked" === $val['optional'] ? 'required="required"' : "" : 'required="required"';
                        $required_class = isset($val['optional']) ? "checked" === $val['optional'] ? "sjb-required" : "sjb-not-required" : "sjb-required";
                        $required_field_asterisk = isset($val['optional']) ? "checked" === $val['optional'] ? '<span class="required">*</span>' : "" : '<span id="sjb-required">*</span>';
                        $id = preg_replace('/[^\p{L}\p{N}\_]/u', '_', $key);
                        $name = preg_replace('/[^\p{L}\p{N}\_]/u', '_', $key);
                        $label = isset($val['label']) ? $val['label'] : ucwords(str_replace('_', ' ', substr($key, 7)));

                        // Field Type Meta
                        $field_type_meta = array(
                            'id' => $id,
                            'name' => $name,
                            'label' => $label,
                            'type' => $val['type'],
                            'is_required' => $is_required,
                            'required_class' => $required_class,
                            'required_field_asterisk' => $required_field_asterisk,
                        );

                        /**
                         * Fires on job detail page at start of job application form. 
                         *                 
                         * @since   2.3.0                   
                         */
                        do_action('sjb_job_application_form_fields', $field_type_meta);

                        switch ($val['type']) {
                            case 'text':
                                echo '<div class="form-group">'
                                . '<label for="' . $key . '">' . $label . $required_field_asterisk . '</label>'
                                . '<input type="text" name="' . $name . '" class="form-control ' . $required_class . '" id="' . $id . '" ' . $is_required . ' >'
                                . '</div>';
                                break;
                            case 'text_area':
                                echo '<div class="form-group">'
                                . '<label for="' . $key . '">' . $label . $required_field_asterisk . '</label>'
                                . '<textarea name="' . $name . '" class="form-control ' . $required_class . '" id="' . $id . '" ' . $is_required . '></textarea>'
                                . '</div>';
                                break;
                            case 'email':
                                echo '<div class="form-group">'
                                . '<label for="' . $key . '">' . $label . $required_field_asterisk . '</label>'
                                . '<input type="email" name="' . $name . '" class="form-control sjb-email-address ' . $required_class . '" id="' . $id . '" ' . $is_required . '><span class="sjb-invalid-email validity-note">' . esc_html__('A valid email address is required.', 'simple-job-board') . '</span>'
                                . '</div>';
                                break;
                            case 'phone':
                                echo '<div class="form-group">'
                                . '<label for="' . $key . '">' . $label . $required_field_asterisk . '</label>'
                                . '<input type="tel" name="' . $name . '" class="form-control sjb-phone-number ' . $required_class . '" id="' . $id . '" ' . $is_required . '><span class="sjb-invalid-phone validity-note" id="' . $id . '-invalid-phone">' . esc_html__('A valid phone number is required.', 'simple-job-board') . ' </span>'
                                . '</div>';
                                break;
                            case 'date':
                                echo '<div class="form-group">'
                                . '<label for="' . $key . '">' . $label . $required_field_asterisk . '</label>'
                                . '<input type="text" name="' . $name . '" class="form-control sjb-datepicker ' . $required_class . '" id="' . $id . '" ' . $is_required . '>'
                                . '</div>';
                                break;
                            case 'radio':
                                if ($val['options'] != '') {
                                    echo '<div class="form-group">'
                                    . '<label class="sjb-label-control" for="' . $key . '">' . $label . $required_field_asterisk . '</label>'
                                    . '<div id="' . $key . '" >';
                                    $options = explode(',', $val['options']);
                                    $i = 0;
                                    foreach ($options as $option) {
                                        echo '<label class="small"><input type="radio" name="' . $name . '" class=" ' . $required_class . '" id="' . $id . '" value="' . $option . '"  ' . sjb_is_checked( $i ) . ' ' . $is_required . '>' . $option . ' </label> ';
                                        $i++;
                                    }
                                    echo '</div></div>';
                                }
                                break;
                            case 'dropdown':
                                if ($val['options'] != '') {
                                    echo '<div class="form-group">'
                                    . '<label for="' . $key . '">' . $label . $required_field_asterisk . '</label>'
                                    . '<div id="' . $key . '" >'
                                    . '<select class="form-control" name="' . $name . '" id="' . $id . '" ' . $is_required . '>';
                                    $options = explode(',', $val['options']);
                                    foreach ($options as $option) {
                                        echo '<option class="' . $required_class . '" value="' . $option . '" >' . $option . ' </option>';
                                    }
                                    echo '</select></div></div>';
                                }
                                break;
                            case 'checkbox' :
                                if ($val['options'] != '') {
                                    echo '<div class="form-group ">'
                                    . '<label for="' . $key . '">' . $label . $required_field_asterisk . '</label>'
                                    . '<div id="' . $key . '">';
                                    $options = explode(',', $val['options']);
                                    $i = 0;

                                    foreach ($options as $option) {
                                        echo '<label class="small"><input type="checkbox" name="' . $name . '[]" class="' . $required_class . '" id="' . $id . '" value="' . $option . '"  ' . $i . ' ' . $is_required . '>' . $option . ' </label>';
                                        $i++;
                                    }
                                    echo '</div></div>';
                                }
                                break;
                        }
                    endif;
                endforeach;
            endif;

            /**
             * Modify the output of file upload button. 
             * 
             * @since   2.2.0 
             * 
             * @param   string  $sjb_attach_resume  Attach resume button.
             */
            $sjb_attach_resume = '<div class="form-group">'
                    . '<label for="applicant_resume">' . apply_filters('sjb_resume_label', __('Attach Resume', 'simple-job-board')) . '<span class="sjb-required required">*</span></label>'
                    . '<input type="file" name="applicant_resume" id="applicant-resume" class="sjb-attachment form-control "' . apply_filters('sjb_resume_required', 'required="required"') . '>'
                    . '<span class="sjb-invalid-attachment validity-note" id="file-error-message"></span>'
                    . '</div>';
            echo apply_filters('sjb_attach_resume', $sjb_attach_resume);

            /**
             * Fires on job detail page before job submit button. 
             *                 
             * @since   2.2.0                   
             */
            do_action('sjb_job_application_form_fields_end');
            ?>

            <input type="hidden" name="job_id" value="<?php the_ID(); ?>" >
            <input type="hidden" name="action" value="process_applicant_form" >
            <input type="hidden" name="wp_nonce" value="<?php echo wp_create_nonce('jobpost_security_nonce') ?>" >
            <div class="form-group" id="sjb-form-padding-button">
                <button class="btn btn-primary app-submit"><?php esc_html_e('Submit', 'simple-job-board'); ?></button>
            </div>
        </div>    
    </div>
</form>
<div class="clearfix"></div>
<?php
/**
 * Fires on job detail page after displaying job application form.
 *                  
 * @since 2.1.0                   
 */
do_action('sjb_job_application_end');
?>

<div id="jobpost_form_status"></div>
<!-- ==================================================
End Job Application Form -->

<?php
/**
 * Fires on job detail page after displaying job application section.
 *                  
 * @since   2.1.0                   
 */
do_action('sjb_job_application_after');

$html_job_application = ob_get_clean();

/**
 * Modify the Job Applicatin Form Template. 
 *                                       
 * @since   2.3.0
 * 
 * @param   html    $html_job_application   Job Application Form HTML.                   
 */
echo apply_filters('sjb_job_application_template', $html_job_application);