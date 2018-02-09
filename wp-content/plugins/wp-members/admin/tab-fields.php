<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the fields tab.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2017
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Load WP_Members_Fields_Table object
 */
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Filters and Actions.
 */
add_action( 'wpmem_admin_do_tab',            'wpmem_a_fields_tab', 99, 1  );
add_action( 'wpmem_after_admin_init',        'wpmem_admin_fields_update'  );
add_action( 'admin_print_styles',            'wpmem_a_fields_tab_scripts' );
add_action( 'wp_ajax_wpmem_a_field_reorder', 'wpmem_a_do_field_reorder'  );

/**
 * Calls the function to reorder fields.
 *
 * @since 2.8.0
 */
function wpmem_a_do_field_reorder() {
	// Reorder registration fields.
	wpmem_a_field_reorder();
}

/**
 * Creates the fields tab.
 *
 * @since 3.0.1
 *
 * @param  string      $tab The admin tab being displayed.
 * @return string|bool      The fields tab, otherwise false.
 */
function wpmem_a_fields_tab( $tab ) {
	if ( $tab == 'fields' ) {
		// Render the fields tab.
		wpmem_a_render_fields_tab();
		return;
	}
}

/**
 * Scripts needed for the fields tab.
 *
 * @since 3.1.8
 */
function wpmem_a_fields_tab_scripts() {
	wp_enqueue_script( 'jquery-ui-sortable' );
}

/**
 * Function to write the field edit link.
 *
 * @since 2.8
 *
 * @param string $field_id The option name of the field to be edited
 */
function wpmem_fields_edit_link( $field_id ) {
	$link_args = array(
		'page'  => 'wpmem-settings',
		'tab'   => 'fields',
		'mode'  => 'edit',
		'edit'  => 'field',
		'field' => $field_id,
	);
	$link = add_query_arg( $link_args, admin_url( 'options-general.php' ) );
	return '<a href="' . $link . '">' . __( 'Edit' ) . '</a>';
}

/**
 * Renders the content of the fields tab.
 *
 * @since 3.1.8
 *
 * @global object $wpmem         The WP_Members Object.
 * @global string $did_update
 * @global string $delete_action
 */
function wpmem_a_render_fields_tab() {

	global $wpmem, $did_update, $delete_action;
	$wpmem_fields  = wpmem_fields();
	$edit_meta     = sanitize_text_field( wpmem_get( 'field', false, 'get' ) );
	$add_meta      = sanitize_text_field( wpmem_get( 'add_field', false ) );
	
	if ( 'delete' == $delete_action ) {
		$delete_fields = wpmem_get( 'delete' ); ?>
		
		<p><?php _e( 'Are you sure you want to delete the following fields?', 'wp-members' ); ?></p>
		
		<?php foreach ( $delete_fields as $meta ) {
			$meta = esc_html( $meta );
			echo esc_html( $wpmem->fields[ $meta ]['label'] ) . ' (meta key: ' . $meta . ')<br />';
		} ?>
		<form name="<?php echo esc_attr( $delete_action ); ?>" id="<?php echo esc_attr( $delete_action ); ?>" method="post" action="<?php echo esc_url( wpmem_admin_form_post_url() ); ?>">
			<?php // wp_nonce_field( 'wpmem-delete-fields' ); ?>
			<input type="hidden" name="delete_fields" value="<?php echo esc_attr( implode( ",", $delete_fields ) ); ?>" />
			<input type="hidden" name="dodelete" value="delete_confirmed" />
			<?php submit_button( 'Delete Fields' ); ?>
		</form><?php

	} else {
		
		if ( 'delete_confirmed' == wpmem_get( 'dodelete' ) ) {
			// validate wpmem-delete-fields nonce

			$delete_fields = sanitize_text_field( wpmem_get( 'delete_fields', array() ) );
			$delete_fields = explode( ",", $delete_fields );
			$wpmem_new_fields = array();
			foreach ( $wpmem_fields as $field ) {
				if ( ! in_array( $field[2], $delete_fields ) ) {
					$wpmem_new_fields[] = $field;
				}
			}
			update_option( 'wpmembers_fields', $wpmem_new_fields );
			$did_update = __( 'Fields deleted', 'wp-members' );
		}
	
		if ( $did_update ) { ?>
			<div id="message" class="updated fade"><p><strong><?php echo $did_update; ?></strong></p></div>
		<?php } 
		if ( $edit_meta || $add_meta ) {
			$mode = ( $edit_meta ) ? sanitize_text_field( wpmem_get( 'mode', false, 'get' ) ) : 'add';
			wpmem_a_render_fields_tab_field_edit( $mode, $wpmem_fields, $edit_meta );
		} else {
			wpmem_a_render_fields_tab_field_table();
		} ?>
		<h3><span><?php _e( 'Need help?', 'wp-members' ); ?></span></h3>
		<div class="inside">
			<strong><i><a href="http://rocketgeek.com/plugins/wp-members/docs/plugin-settings/fields/" target="_blank"><?php _e( 'Field Manager Documentation', 'wp-members' ); ?></a></i></strong>
		</div>
		<?php
	}
}

/**
 * Function to dispay the add/edit field form.
 *
 * @since 2.8
 * @since 3.1.8 Changed name from wpmem_a_field_edit().
 *
 * @global object      $wpmem        The WP_Members Object.
 * @param  string      $mode         The mode for the function (edit|add)
 * @param  array|null  $wpmem_fields The array of fields
 * @param  string|null $field        The field being edited
 */
function wpmem_a_render_fields_tab_field_edit( $mode, $wpmem_fields, $meta_key ) {
	global $wpmem;
	$fields = wpmem_fields();
	if ( $mode == 'edit' ) {
		$field = $fields[ $meta_key ];	
	}
	$form_action = ( $mode == 'edit' ) ? 'editfieldform' : 'addfieldform'; 
	$span_optional = '<span class="description">' . __( '(optional)', 'wp-members' ) . '</span>';
	$span_required = '<span class="req">' . __( '(required)', 'wp-members' ) . '</span>'; 
	$form_submit = array( 'mode' => $mode ); 
	if ( isset( $_GET['field'] ) ) {
		$form_submit['field'] = $meta_key; 
	} ?>
    <h3 class="title"><?php ( $mode == 'edit' ) ? _e( 'Edit Field', 'wp-members' ) : _e( 'Add a Field', 'wp-members' ); ?></h3>
    <form name="<?php echo $form_action; ?>" id="<?php echo $form_action; ?>" method="post" action="<?php echo wpmem_admin_form_post_url( $form_submit ); ?>">
		<?php wp_nonce_field( 'wpmem-add-fields' ); ?>
		<ul>
			<li>
				<label><?php _e( 'Field Label', 'wp-members' ); ?> <?php echo $span_required; ?></label>
				<input type="text" name="add_name" value="<?php echo ( $mode == 'edit' ) ? $field['label'] : false; ?>" />
				<?php _e( 'The name of the field as it will be displayed to the user.', 'wp-members' ); ?>
			</li>
			<li>
				<label><?php _e( 'Meta Key', 'wp-members' ); ?> <?php echo $span_required; ?></label>
				<?php if ( $mode == 'edit' ) { 
					echo "<span>$meta_key</span>"; ?>
					<input type="hidden" name="add_option" value="<?php echo $meta_key; ?>" /> 
				<?php } else { ?>
					<input type="text" name="add_option" value="" />
					<?php _e( 'The database meta value for the field. It must be unique and contain no spaces (underscores are ok).', 'wp-members' ); ?>
				<?php } ?>
			</li>
			<li>
				<label><?php _e( 'Field Type', 'wp-members' ); ?></label>
				<?php if ( $mode == 'edit' ) {
					echo '<span>' . $field['type'] . '</span>'; ?>
					<input type="hidden" name="add_type" value="<?php echo $field['type']; ?>" /> 							
				<?php } else { ?>
					<select name="add_type" id="wpmem_field_type_select">
						<option value="text"><?php          _e( 'text',              'wp-members' ); ?></option>
						<option value="email"><?php         _e( 'email',             'wp-members' ); ?></option>
						<option value="textarea"><?php      _e( 'textarea',          'wp-members' ); ?></option>
						<option value="checkbox"><?php      _e( 'checkbox',          'wp-members' ); ?></option>
						<option value="multicheckbox"><?php _e( 'multiple checkbox', 'wp-members' ); ?></option>
						<option value="select"><?php        _e( 'select (dropdown)', 'wp-members' ); ?></option>
						<option value="multiselect"><?php   _e( 'multiple select',   'wp-members' ); ?></option>
						<option value="radio"><?php         _e( 'radio group',       'wp-members' ); ?></option>
						<option value="password"><?php      _e( 'password',          'wp-members' ); ?></option>
						<option value="image"><?php         _e( 'image',             'wp-members' ); ?></option>
						<option value="file"><?php          _e( 'file',              'wp-members' ); ?></option>
						<option value="url"><?php           _e( 'url',               'wp-members' ); ?></option>
						<option value="number"><?php        _e( 'number',            'wp-members' ); ?></option>
						<option value="date"><?php          _e( 'date',              'wp-members' ); ?></option>
						<option value="hidden"><?php        _e( 'hidden',            'wp-members' ); ?></option>
					</select>
				<?php } ?>
			</li>
			<li>
				<label><?php _e( 'Display?', 'wp-members' ); ?></label>
				<input type="checkbox" name="add_display" value="y" <?php echo ( $mode == 'edit' ) ? checked( true, $field['register'] ) : false; ?> />
			</li>
			<li>
				<label><?php _e( 'Required?', 'wp-members' ); ?></label>
				<input type="checkbox" name="add_required" value="y" <?php echo ( $mode == 'edit' ) ? checked( true, $field['required'] ) : false; ?> />
			</li>
			<!--<div id="wpmem_allowhtml">
			<li>
				<label><?php _e( 'Allow HTML?', 'wp-members' ); ?></label>
				<input type="checkbox" name="add_html" value="y" <?php echo ( $mode == 'edit' ) ? checked( true, $field['html'] ) : false; ?> />
			</li>
			</div>-->
		<?php if ( $mode == 'add' || ( $mode == 'edit' && ( in_array( $field['type'], array( 'text', 'password', 'email', 'url', 'number', 'date', 'textarea' ) ) ) ) ) { ?>
		<?php echo ( $mode == 'add' ) ? '<div id="wpmem_placeholder">' : ''; ?>
			<li>
				<label><?php _e( 'Placeholder', 'wp-members' ); ?></label>
				<input type="text" name="add_placeholder" value="<?php echo ( $mode == 'edit' ) ? ( isset( $field['placeholder'] ) ? $field['placeholder'] : false ) : false; ?>" /> <?php echo $span_optional; ?>
			</li>
		<?php echo ( $mode == 'add' ) ? '</div>' : ''; ?>
		<?php } ?>
		<?php if ( $mode == 'add' || ( $mode == 'edit' && ( in_array( $field['type'], array( 'text', 'password', 'email', 'url', 'number', 'date' ) ) ) ) ) { ?>
		<?php echo ( $mode == 'add' ) ? '<div id="wpmem_pattern">' : ''; ?>
			<li>
				<label><?php _e( 'Pattern', 'wp-members' ); ?></label>
				<input type="text" name="add_pattern" value="<?php echo ( $mode == 'edit' ) ? ( isset( $field['pattern'] ) ? $field['pattern'] : false ) : false; ?>" /> <?php echo $span_optional; ?>
			</li>
		<?php echo ( $mode == 'add' ) ? '</div>' : ''; ?>
		<?php echo ( $mode == 'add' ) ? '<div id="wpmem_title">' : ''; ?>
			<li>
				<label><?php _e( 'Title', 'wp-members' ); ?></label>
				<input type="text" name="add_title" value="<?php echo ( $mode == 'edit' ) ? ( isset( $field['title'] ) ? $field['title'] : false ) : false; ?>" /> <?php echo $span_optional; ?>
			</li>
		<?php echo ( $mode == 'add' ) ? '</div>' : ''; ?>
		<?php } ?>

		<?php if ( $mode == 'add' || ( $mode == 'edit' && ( in_array( $field['type'], array( 'number', 'date' ) ) ) ) ) { ?>
		<?php echo ( $mode == 'add' ) ? '<div id="wpmem_min_max">' : ''; ?>
			<li>
				<label><?php _e( 'Minimum Value', 'wp-members' ); ?></label>
				<input type="text" name="add_min" value="<?php echo ( $mode == 'edit' ) ? ( isset( $field['min'] ) ? $field['min'] : false ) : false; ?>" /> <?php echo $span_optional; ?>
			</li>
			<li>
				<label><?php _e( 'Maximum Value', 'wp-members' ); ?></label>
				<input type="text" name="add_max" value="<?php echo ( $mode == 'edit' ) ? ( isset( $field['max'] ) ? $field['max'] : false ) : false; ?>" /> <?php echo $span_optional; ?>
			</li>
		<?php echo ( $mode == 'add' ) ? '</div>' : ''; ?>
		<?php } ?>
		<?php if ( $mode == 'add' || ( $mode == 'edit' && ( $field['type'] == 'file' || $field['type'] == 'image' ) ) ) { ?>
		<?php echo ( $mode == 'add' ) ? '<div id="wpmem_file_info">' : ''; ?>
			<li>
				<label><?php _e( 'Accepted file types:', 'wp-members' ); ?></label>
				<input type="text" name="add_file_value" value="<?php echo ( $mode == 'edit' && ( $field['type'] == 'file' || $field['type'] == 'image' ) ) ? $field['file_types'] : false; ?>" />
			</li>
			<li>
				<label>&nbsp;</label>
				<span class="description"><?php _e( 'Accepted file types should be set like this: jpg|jpeg|png|gif', 'wp-members' ); ?></span>
			</li>
		<?php echo ( $mode == 'add' ) ? '</div>' : ''; ?>
		<?php } ?>
		<?php if ( $mode == 'add' || ( $mode == 'edit' && $field['type'] == 'checkbox' ) ) { ?>
		<?php echo ( $mode == 'add' ) ? '<div id="wpmem_checkbox_info">' : ''; ?>
			<li>
				<label><?php _e( 'Checked by default?', 'wp-members' ); ?></label>
				<input type="checkbox" name="add_checked_default" value="y" <?php echo ( $mode == 'edit' && $field['type'] == 'checkbox' ) ? checked( true, $field['checked_default'] ) : false; ?> />
			</li>
			<li>
				<label><?php _e( 'Stored value if checked:', 'wp-members' ); ?> <span class="req"><?php _e( '(required)', 'wp-members' ); ?></span></label>
				<input type="text" name="add_checked_value" value="<?php echo ( $mode == 'edit' && $field['type'] == 'checkbox' ) ? $field['checked_value'] : false; ?>" />
			</li>
		<?php echo ( $mode == 'add' ) ? '</div>' : ''; ?>
		<?php } 

		if ( isset( $field['type'] ) ) {
			$additional_settings = ( $field['type'] == 'select' || $field['type'] == 'multiselect' || $field['type'] == 'multicheckbox' || $field['type'] == 'radio' ) ? true : false;
			$delimiter_settings  = ( $field['type'] == 'multiselect' || $field['type'] == 'multicheckbox' ) ? true : false;
		}
		if ( $mode == 'add' || ( $mode == 'edit' && $additional_settings ) ) { ?>
		<?php echo ( $mode == 'add' ) ? '<div id="wpmem_dropdown_info">' : ''; ?>
			<?php if ( $mode == 'add' || ( $mode == 'edit' && $delimiter_settings ) ) {
			echo ( $mode == 'add' ) ? '<div id="wpmem_delimiter_info">' : ''; 
			if ( isset( $field['delimiter'] ) && ( "|" == $field['delimiter'] || "," == $field['delimiter'] ) ) {
				$delimiter = $field['delimiter'];
			} else {
				$delimiter = "|";
			}
			?>
			<li>
				<label><?php _e( 'Stored values delimiter:', 'wp-members' ); ?></label>
				<select name = "add_delimiter_value">
					<option value="|" <?php selected( '|', $delimiter ); ?>>pipe "|"</option>
					<option value="," <?php selected( ',', $delimiter ); ?>>comma ","</option>
				</select>
			</li>
			<?php echo ( $mode == 'add' ) ? '</div>' : '';
			} ?>
			<li>
				<label style="vertical-align:top"><?php _e( 'Values (Displayed|Stored):', 'wp-members' ); ?> <?php echo $span_required; ?></label>
				<textarea name="add_dropdown_value" rows="5" cols="40"><?php
// Accomodate editing the current dropdown values or create dropdown value example.
if ( $mode == 'edit' ) {
for ( $row = 0; $row < count( $field['values'] ); $row++ ) {
// If the row contains commas (i.e. 1,000-10,000), wrap in double quotes.
if ( strstr( $field['values'][ $row ], ',' ) ) {
echo '"' . $field['values'][ $row ]; echo ( $row == count( $field['values'] )- 1  ) ? '"' : "\",\n";
} else {
echo $field['values'][ $row ]; echo ( $row == count( $field['values'] )- 1  ) ? "" : ",\n";
} }
				} else {
					if (version_compare(PHP_VERSION, '5.3.0') >= 0) { ?>
<---- Select One ---->|,
Choice One|choice_one,
"1,000|one_thousand",
"1,000-10,000|1,000-10,000",
Last Row|last_row<?php } else { ?>
<---- Select One ---->|,
Choice One|choice_one,
Choice 2|choice_two,
Last Row|last_row<?php } } ?></textarea>
			</li>
			<li>
				<label>&nbsp;</label>
				<span class="description"><?php _e( 'Options should be Option Name|option_value,', 'wp-members' ); ?></span>
			</li>
			<li>
				<label>&nbsp;</label>
				<span class="description"><a href="http://rocketgeek.com/plugins/wp-members/users-guide/registration/choosing-fields/" target="_blank"><?php _e( 'Visit plugin site for more information', 'wp-members' ); ?></a></span>
			</li>
		<?php echo ( $mode == 'add' ) ? '</div>' : ''; ?>
		<?php } ?>
		<?php if ( $mode == 'add' || ( $mode == 'edit' && $field['type'] == 'hidden' ) ) { ?>
		<?php echo ( $mode == 'add' ) ? '<div id="wpmem_hidden_info">' : ''; ?>
			<li>
				<label><?php _e( 'Value', 'wp-members' ); ?> <?php echo $span_required; ?></label>
				<input type="text" name="add_hidden_value" value="<?php echo ( $mode == 'edit' && $field['type'] == 'hidden' ) ? $field['value'] : ''; ?>" />
			</li>
		<?php echo ( $mode == 'add' ) ? '</div>' : ''; ?>
		<?php } ?>
		</ul><br />
		<?php if ( $mode == 'edit' ) { ?><input type="hidden" name="field_arr" value="<?php echo $meta_key; ?>" /><?php } ?>
		<?php if ( 'add' == $mode ) {
				$ids = array();
				foreach ( $fields as $f ) {
					$ids[] = $f[0];
				}
				sort( $ids );
				$field_order_id = end( $ids ) + 1;
			} else {
				$field_order_id = $field[0];
			} ?>
		<input type="hidden" name="add_order_id" value="<?php echo $field_order_id; ?>" />
		<input type="hidden" name="wpmem_admin_a" value="<?php echo ( $mode == 'edit' ) ? 'edit_field' : 'add_field'; ?>" />
		<?php $text = ( $mode == 'edit' ) ? __( 'Edit Field', 'wp-members' ) : __( 'Add Field', 'wp-members' ); ?>
		<?php submit_button( $text ); ?>
		<p><a href="<?php echo add_query_arg( array( 'page' => 'wpmem-settings', 'tab' => 'fields' ), get_admin_url() . 'options-general.php' ); ?>">&laquo; <?php _e( 'Return to Fields Table', 'wp-members' ); ?></a></p>
	</form><?php
}

/**
 * Function to display the table of fields in the field manager tab.
 * 
 * @since 2.8.0
 * @since 3.1.8 Changed name from wpmem_a_field_table().
 *
 * @global object $wpmem
 */
function wpmem_a_render_fields_tab_field_table() {
	global $wpmem; 

	$wpmem_ut_fields_skip = array( 'user_email', 'confirm_email', 'password', 'confirm_password' );	
	$wpmem_ut_fields = get_option( 'wpmembers_utfields' );
	$wpmem_us_fields_skip = array( 'user_email', 'confirm_email', 'password', 'confirm_password' );	
	$wpmem_us_fields = get_option( 'wpmembers_usfields' );

	$wpmem_fields = get_option( 'wpmembers_fields', array() );
	foreach ( $wpmem_fields as $key => $field ) {
		
		// @todo - transitional until new array keys
		if ( is_numeric( $key ) ) {
			
			$meta = $field[2];
			$ut_checked = ( ( $wpmem_ut_fields ) && ( in_array( $field[1], $wpmem_ut_fields ) ) ) ? $field[1] : '';
			$us_checked = ( ( $wpmem_us_fields ) && ( in_array( $field[1], $wpmem_us_fields ) ) ) ? $field[1] : '';
			$field_items[] = array(
				'order'    => $field[0],
				'label'    => $field[1],
				'meta'     => $meta,
				'type'     => $field[3],
				'display'  => ( $meta != 'user_email' ) ? wpmem_create_formfield( $meta . "_display",  'checkbox', 'y', $field[4] ) : '',
				'req'      => ( $meta != 'user_email' ) ? wpmem_create_formfield( $meta . "_required", 'checkbox', 'y', $field[5] ) : '',
				//'profile'  => ( $meta != 'user_email' ) ? wpmem_create_formfield( $meta . "_profile",  'checkbox', true, $field[6] ) : '',
				'edit'     => wpmem_fields_edit_link( $meta ),
				'userscrn' => ( ! in_array( $meta, $wpmem_ut_fields_skip ) ) ? wpmem_create_formfield( 'ut_fields[' . $meta . ']', 'checkbox', $field[1], $ut_checked ) : '',
				'usearch'  => ( ! in_array( $meta, $wpmem_us_fields_skip ) ) ? wpmem_create_formfield( 'us_fields[' . $meta . ']', 'checkbox', $field[1], $us_checked ) : '',
				'sort'     => '<span class="ui-icon ui-icon-grip-dotted-horizontal" title="' . __( 'Drag and drop to reorder fields', 'wp-members' ) . '"></span>',
			);
		}
	}
	
	$extra_user_screen_items = array(
		'user_registered' => __( 'Registration Date', 'wp-members' ),
		'active'          => __( 'Active',            'wp-members' ),
		'wpmem_reg_ip'    => __( 'Registration IP',   'wp-members' ),
		'exp_type'        => __( 'Subscription Type', 'wp-members' ),
		'expires'         => __( 'Expires',           'wp-members' ),
		'user_id'         => __( 'User ID',           'wp-members' ),
	);
	
	foreach ( $extra_user_screen_items as $key => $item ) {
		$ut_checked = ( ( $wpmem_ut_fields ) && ( in_array( $item, $wpmem_ut_fields ) ) ) ? $item : '';
		if ( 'user_id' == $key
			|| 'user_registered' == $key 
			|| 'wpmem_reg_ip' == $key 
			|| ( 'active' == $key && 1 == $wpmem->mod_reg ) 
			|| defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 && ( 'exp_type' == $key || 'expires' == $key ) ) {
			$user_screen_items[ $key ] = array( 'label' => __( $item, 'wp-members' ), 'meta' => $key,
				'userscrn' => wpmem_create_formfield( "ut_fields[{$key}]", 'checkbox', $item, $ut_checked ),
			);
		}
	}

	foreach ( $user_screen_items as $screen_item ) {
		$field_items[] = array(
			'label'    => $screen_item['label'],
			'meta'     => $screen_item['meta'],
			'type'     => '',
			'display'  => '',
			'req'      => '',
			'profile'  => '',
			'edit'     => '',
			'userscrn' => $screen_item['userscrn'],
			'usearch'  => '',
			'sort'     => '',
		);
	}

	$table = new WP_Members_Fields_Table();

	$heading     = __( 'Manage Fields', 'wp-members' );
	//$description = __( 'Displaying fields for:', 'wp-members' );
	//$which_form  = $wpmem->form_tags[ $wpmem->admin->current_form ];

	echo '<div class="wrap">';
	printf( '<h3 class="title">%s</h3>', $heading );
	//printf( '<p>%s <strong>%s</strong></p>', $description, $which_form );
	printf( '<form name="updatefieldform" id="updatefieldform" method="post" action="%s">', wpmem_admin_form_post_url() );

	$table->items = $field_items;
	$table->prepare_items(); 
	$table->display(); 
	echo '</form>';
	echo '</div>'; 
}

/**
 * Extends the WP_List_Table to create a table of form fields.
 *
 * @since 3.1.8
 */
class WP_Members_Fields_Table extends WP_List_Table {
	
	private $excludes = array( 'user_registered', 'active', 'wpmem_reg_ip', 'exp_type', 'expires', 'user_id' );
	
	private $no_delete = array( 'user_email', 'first_name', 'last_name', 'user_url' );
	
	/**
	 * Checkbox at start of row.
	 *
	 * @since 3.1.8
	 *
	 * @param $item
	 * @return string The checkbox.
	 */
	function column_cb( $item ) {
		if ( in_array( $item['meta'], $this->no_delete ) || in_array( $item['meta'], $this->excludes ) ) {
			return;
		} else {
			return sprintf( '<input type="checkbox" name="delete[]" value="%s" title="%s" />', $item['meta'], __( 'delete', 'wp-members' ) );
		}
	}

	/**
	 * Returns table columns.
	 *
	 * @since 3.1.8
	 *
	 * @return array
	 */
	function get_columns() {
		return array(
			'cb'   =>  '<input type="checkbox" />',
			'label'    => __( 'Display Label', 'wp-members' ),
			'meta'     => __( 'Meta Key',      'wp-members' ),
			'type'     => __( 'Field Type',    'wp-members' ),
			'display'  => __( 'Display?',      'wp-members' ),
			'req'      => __( 'Required?',     'wp-members' ),
			//'profile'  => __( 'Profile Only',  'wp-members' ),
			'edit'     => __( 'Edit',          'wp-members' ),
			'userscrn' => __( 'Users Screen',  'wp-members' ),
			'usearch'  => __( 'Users Search',  'wp-members' ),
			'sort'     => '',
		);
	}

	/**
	 * Set up table columns.
	 *
	 * @since 3.1.8
	 */
	function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
	}

	/**
	 * Iterates through the columns
	 *
	 * @since 3.1.8
	 *
	 * @param  array  $item
	 * @param  string $column_name
	 * @return string $item[ $column_name ]
	 */
	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			default:
	  			return $item[ $column_name ];
		}
	}

	/**
	 * Sets actions in the bulk menu.
	 *
	 * @since 3.1.8
	 *
	 * @return array $actions
	 */
	function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete Selected', 'wp-members' ),
			'save'   => __( 'Save Settings', 'wp-members' ),
		);
		return $actions;
	}

	/**
	 * Handles "delete" column - checkbox
	 *
	 * @since 3.1.8
	 *
	 * @param  array  $item
	 * @return string 
	 */
	function column_delete( $item ) {
		$can_delete = ( $item['meta_key'] == 'user_nicename' || $item['meta_key'] == 'display_name' || $item['meta_key'] == 'nickname' ) ? true : false;
		return ( ( $can_delete ) || $item['native'] != true ) ? sprintf( '<input type="checkbox" name="field[%s]" value="delete" />', $item['meta'] ) : '';
	}
	
	/**
	 * Sets rows so that they have field IDs in the id.
	 *
	 * @since 3.1.8
	 *
	 * @global wpmem
	 * @param  array $columns
	 */
	function single_row( $columns ) {
		if ( in_array( $columns['meta'], $this->excludes ) ) {
			echo '<tr id="' . esc_attr( $columns['meta'] ) . '" class="nodrag nodrop">';
			echo $this->single_row_columns( $columns );
			echo "</tr>\n";
		} else {
			echo '<tr id="list_items_' . esc_attr( $columns['order'] ) . '" class="list_item" list_item="' . esc_attr( $columns['order'] ) . '">';
			echo $this->single_row_columns( $columns );
			echo "</tr>\n";
		}
	}
	
	public function process_bulk_action() {

	//nonce validations,etc
	
		$action = $this->current_action();
	
		switch ( $action ) {
	
			case 'delete':
	
				// Do whatever you want
				wp_redirect( esc_url( add_query_arg() ) );
				break;
	
			default:
				// do nothing or something else
				return;
				break;
		}
		return;
	}
	
}

/** 
 * Javascript to ID the fields table and add curser style to rows.
 *
 * @since 3.1.8
 */ 
add_action( 'admin_footer', 'wpmem_bulk_fields_action'   );
function wpmem_bulk_fields_action() { 
	if ( 'wpmem-settings' == wpmem_get( 'page', false, 'get' ) && 'fields' == wpmem_get( 'tab', false, 'get' ) ) {
	?><script type="text/javascript">
		(function($) {
			$(document).ready(function() {
				$("table").attr("id", "wpmem-fields");
				/**$("tr").attr('style', 'cursor:move;');**/
			});
		})(jQuery);
		jQuery('<input id="add_field" name="add_field" class="button action" type="submit" value="<?php _e( 'Add Field', 'wp-members' ); ?>" />').appendTo(".top .bulkactions");
		jQuery('<input id="add_field" name="add_field" class="button action" type="submit" value="<?php _e( 'Add Field', 'wp-members' ); ?>" />').appendTo(".bottom .bulkactions");
	</script><?php
	}
}

/**
 * Updates fields.
 *
 * Derived from wpmem_update_fields()
 *
 * @since 3.1.8
 *
 * @global object $wpmem
 * @global string $did_update
 * @global string $add_field_err_msg  The add field error message
 */
function wpmem_admin_fields_update() {
	
	global $wpmem, $did_update, $delete_action;

	if ( 'wpmem-settings' == wpmem_get( 'page', false, 'get' ) && 'fields' == wpmem_get( 'tab', false, 'get' ) ) {
		// Get the current fields.
		$wpmem_fields    = get_option( 'wpmembers_fields' );

		$action = sanitize_text_field( wpmem_get( 'action', false ) );
		$action = ( -1 == $action ) ? sanitize_text_field( wpmem_get( 'action2' ) ) : $action;
		
		$delete_action = false;

		if ( 'save' == $action ) {

			// Check nonce.
			//check_admin_referer( 'wpmem-update-fields' );
			
			// Update user table fields.
			$arr = ( isset( $_POST['ut_fields'] ) ) ? $_POST['ut_fields'] : array();
			$ut_fields_arr = array();
			foreach ( $arr as $key => $item ) {
				$ut_fields_arr[ sanitize_text_field( $key ) ] = sanitize_text_field( $item );
			}
			update_option( 'wpmembers_utfields', $ut_fields_arr );
			
			// Update user search fields.
			$arr = ( isset( $_POST['us_fields'] ) ) ? $_POST['us_fields'] : array();
			$us_fields_arr = array();
			foreach ( $arr as $key => $item ) {
				$us_fields_arr[ sanitize_text_field( $key ) ] = sanitize_text_field( $item );
			}
			update_option( 'wpmembers_usfields', $us_fields_arr );

			// Update display/required settings
			foreach ( $wpmem_fields as $key => $field ) {
				$meta_key = $field[2];
				if ( 'user_email' == $meta_key ) {
					$wpmem_fields[ $key ][4] = 'y';
					$wpmem_fields[ $key ][5] = 'y';
				} else {
					$wpmem_fields[ $key ][4] = ( wpmem_get( $meta_key . "_display"  ) ) ? 'y' : '';
					$wpmem_fields[ $key ][5] = ( wpmem_get( $meta_key . "_required" ) ) ? 'y' : '';
				}
			}
			update_option( 'wpmembers_fields', $wpmem_fields );
			$wpmem->load_fields();
			$did_update = __( 'WP-Members fields were updated', 'wp-members' );
			return $did_update;
			
		} elseif ( 'delete' == $action ) {
			
			$delete_action = 'delete';

		} elseif ( 'add_field' == wpmem_get( 'wpmem_admin_a' ) || 'edit_field' == wpmem_get( 'wpmem_admin_a' ) ) {
			
			// Set action.
			$action = sanitize_text_field( wpmem_get( 'wpmem_admin_a' ) );

			// Check nonce.
			//check_admin_referer( 'wpmem-add-fields' );

			global $add_field_err_msg;

			$add_field_err_msg = false;
			$add_name = sanitize_text_field( wpmem_get( 'add_name' ) );
			$add_option = sanitize_text_field( wpmem_get( 'add_option' ) );

			// Error check that field label and option name are included and unique.
			$add_field_err_msg = ( ! $add_name   ) ? __( 'Field Label is required. Nothing was updated.', 'wp-members' ) : $add_field_err_msg;
			$add_field_err_msg = ( ! $add_option ) ? __( 'Meta Key is required. Nothing was updated.',    'wp-members' ) : $add_field_err_msg;

			$add_field_err_msg = ( ! preg_match("/^[A-Za-z0-9_]*$/", $add_option ) ) ? __( 'Meta Key must contain only letters, numbers, and underscores', 'wp-members' ) : $add_field_err_msg;

			// Check for duplicate field names.
			$chk_fields = array();
			foreach ( $wpmem_fields as $field ) {
				$chk_fields[] = $field[2];
			}
			$add_field_err_msg = ( in_array( $add_option, $chk_fields ) ) ? __( 'A field with that meta key already exists', 'wp-members' ) : $add_field_err_msg;

			// Error check for reserved terms.
			$reserved_terms = wpmem_wp_reserved_terms();
			if ( in_array( strtolower( $add_option ), $reserved_terms ) ) {
				$add_field_err_msg = sprintf( __( 'Sorry, "%s" is a <a href="https://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms" target="_blank">reserved term</a>. Field was not added.', 'wp-members' ), $submitted_term );
			}

			// Error check option name for spaces and replace with underscores.
			$us_option = preg_replace( "/ /", '_', $add_option );

			$arr = array();

			$type = sanitize_text_field( wpmem_get( 'add_type' ) );

			$arr[0] = filter_var( wpmem_get( 'add_order_id' ), FILTER_SANITIZE_NUMBER_INT );
			$arr[1] = sanitize_text_field( stripslashes( wpmem_get( 'add_name' ) ) );
			$arr[2] = $us_option;
			$arr[3] = $type;
			$arr[4] = ( 'y' == wpmem_get( 'add_display', 'n'  ) ) ? 'y' : 'n';
			$arr[5] = ( 'y' == wpmem_get( 'add_required', 'n' ) ) ? 'y' : 'n';
			$arr[6] = ( $us_option == 'user_nicename' || $us_option == 'display_name' || $us_option == 'nickname' ) ? 'y' : 'n';

			if ( 'text' == $type || 'email' == $type || 'textarea' == $type || 'password' == $type || 'url' == $type || 'number' == $type || 'date' == $type ) {
				$arr['placeholder'] = sanitize_text_field( stripslashes( wpmem_get( 'add_placeholder' ) ) );
			}

			if ( 'text' == $type || 'email' == $type || 'password' == $type || 'url' == $type || 'number' == $type || 'date' == $type ) {
				$arr['pattern'] = sanitize_text_field( stripslashes( wpmem_get( 'add_pattern' ) ) );
				$arr['title']   = sanitize_text_field( stripslashes( wpmem_get( 'add_title' ) ) );
			}

			if ( 'number' == $type || 'date' == $type ) {
				$arr['min'] = filter_var( wpmem_get( 'add_min' ), FILTER_SANITIZE_NUMBER_INT );
				$arr['max'] = filter_var( wpmem_get( 'add_max' ), FILTER_SANITIZE_NUMBER_INT );
			}

			if ( $type == 'checkbox' ) {
				$add_field_err_msg = ( ! $_POST['add_checked_value'] ) ? __( 'Checked value is required for checkboxes. Nothing was updated.', 'wp-members' ) : $add_field_err_msg;
				$arr[7] = sanitize_text_field( wpmem_get( 'add_checked_value', false ) );
				$arr[8] = ( 'y' == wpmem_get( 'add_checked_default', 'n'  ) ) ? 'y' : 'n';
			}

			if (   $type == 'select' 
				|| $type == 'multiselect' 
				|| $type == 'radio'
				|| $type == 'multicheckbox' 
			) {
				// Get the values.
				$str = stripslashes( $_POST['add_dropdown_value'] );
				// Remove linebreaks.
				$str = trim( str_replace( array("\r", "\r\n", "\n"), '', $str ) );
				// Create array.
				if ( ! function_exists( 'str_getcsv' ) ) {
					$arr[7] = explode( ',', $str );
				} else {
					$arr[7] = str_getcsv( $str, ',', '"' );
				}
				// If multiselect or multicheckbox, set delimiter.
				if ( 'multiselect' == $type || 'multicheckbox' == $type ) {
					$arr[8] = ( ',' === wpmem_get( 'add_delimiter_value', '|' ) ) ? ',' : '|';
				}
			}

			if ( $type == 'file' || $type == 'image' ) {
				$arr[7] = sanitize_text_field( stripslashes( $_POST['add_file_value'] ) );
			}

			if ( wpmem_get( 'add_type' ) == 'hidden' ) { 
				$add_field_err_msg = ( ! $_POST['add_hidden_value'] ) ? __( 'A value is required for hidden fields. Nothing was updated.', 'wp-members' ) : $add_field_err_msg;
				$arr[7] = ( isset( $_POST['add_hidden_value'] ) ) ? sanitize_text_field( stripslashes( $_POST['add_hidden_value'] ) ) : '';
			}

			if ( $action == 'add_field' ) {
				if ( ! $add_field_err_msg ) {
					array_push( $wpmem_fields, $arr );
					$did_update = sprintf( __( '%s was added', 'wp-members' ), esc_html( $_POST['add_name'] ) );
				} else {
					$did_update = $add_field_err_msg;
				}
			} else {
				for ( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
					if ( $wpmem_fields[ $row ][2] == wpmem_get( 'field', false, 'get' ) ) {
						$arr[0] = $wpmem_fields[ $row ][0];
						foreach ( $arr as $key => $value ) {
							$wpmem_fields[ $row ][ $key ] = $arr[ $key ];
						}
					}
				}
				$did_update =  sprintf( __( '%s was updated', 'wp-members' ), esc_html( stripslashes( $add_name ) ) );
				$did_update.= '<p><a href="' . esc_url( add_query_arg( array( 'page' => 'wpmem-settings', 'tab' => 'fields' ), get_admin_url() . 'options-general.php' ) ) . '">&laquo; ' . __( 'Return to Fields Table', 'wp-members' ) . '</a></p>';
			}

			$wpmem_newfields = $wpmem_fields;

			update_option( 'wpmembers_fields', $wpmem_newfields );
			$wpmem->load_fields();
			return $did_update;		
		}
	}
}

/**
 * Reorders form fields.
 *
 * @since 2.5.1
 * @since 3.1.8 Rebuilt for new List Table.
 */
function wpmem_a_field_reorder() {

	// Start fresh.
	$new_order = $wpmem_fields = $field = $key = $wpmem_new_fields = $id = $k = '';
	$wpmem_fields = get_option( 'wpmembers_fields' );
	
	// Get the list items
	$new_order = $_POST;
	
	// Put fields in the proper order for the current form.
	$wpmem_new_fields = array();
	foreach ( $new_order['list_items'] as $id ) {
		foreach( $wpmem_fields as $val ) {
			if ( $val[0] == $id ) {
				$wpmem_new_fields[] = $val;
			}
		}
	}

	// Save fields array with new current form field order.
	update_option( 'wpmembers_fields', $wpmem_new_fields ); 

	// Indicate successful transaction.
	_e( 'Form field order updated.', 'wp-members' );
	
	die(); // This is required to return a proper result.

}

// End of file.