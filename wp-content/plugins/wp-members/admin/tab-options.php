<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the plugin options tab.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2017
 *
 * Functions included:
 * - wpmem_a_build_options
 * - wpmem_update_cpts
 * - wpmem_update_options
 * - wpmem_admin_new_settings
 * - wpmem_admin_style_list
 * - wpmem_admin_page_list
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Builds the settings panel.
 *
 * @since 2.2.2
 *
 * @global object $wpmem The WP_Members Object.
 */
function wpmem_a_build_options() {

	global $wpmem;

	/** This filter is documented in wp-members/inc/email.php */
	$admin_email = apply_filters( 'wpmem_notify_addr', get_option( 'admin_email' ) );
	$chg_email   = __( sprintf( '%sChange%s or %sFilter%s this address', '<a href="' . site_url( 'wp-admin/options-general.php', 'admin' ) . '">', '</a>', '<a href="http://rocketgeek.com/plugins/wp-members/users-guide/filter-hooks/wpmem_notify_addr/">', '</a>' ), 'wp-members' );
	$help_link   = __( sprintf( 'See the %sUsers Guide on plugin options%s.', '<a href="http://rocketgeek.com/plugins/wp-members/users-guide/plugin-settings/options/" target="_blank">', '</a>' ), 'wp-members' );	

	// Build an array of post types
	$post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names', 'and' );
	$post_arr = array(
		'post' => 'Posts',
		'page' => 'Pages',
	);
	if ( $post_types ) {
		foreach ( $post_types  as $post_type ) { 
			$cpt_obj = get_post_type_object( $post_type );
			$post_arr[ $cpt_obj->name ] = $cpt_obj->labels->name;
		}
	} ?>
	
	<div class="metabox-holder has-right-sidebar">

		<div class="inner-sidebar">
			<?php wpmem_a_meta_box(); ?>
			<div class="postbox">
				<h3><span><?php _e( 'Need help?', 'wp-members' ); ?></span></h3>
				<div class="inside">
					<strong><i><?php echo $help_link; ?></i></strong>
				</div>
			</div>
			<?php wpmem_a_rss_box(); ?>
		</div> <!-- .inner-sidebar -->

		<div id="post-body">
			<div id="post-body-content">
				<div class="postbox">
					<h3><span><?php _e( 'Manage Options', 'wp-members' ); ?></span></h3>
					<div class="inside">
						<form name="updatesettings" id="updatesettings" method="post" action="<?php echo wpmem_admin_form_post_url(); ?>">
						<?php wp_nonce_field( 'wpmem-update-settings' ); ?>
							<h3><?php _e( 'Content', 'wp-members' ); ?></h3>
							<ul>
							<?php

							// Content Blocking option group.
							$i = 0;
							$len = count( $post_arr );
							foreach ( $post_arr as $key => $val ) {  
								if ( $key == 'post' || $key == 'page' || ( isset( $wpmem->post_types ) && array_key_exists( $key, $wpmem->post_types ) ) ) {
								?>
								<li<?php echo ( $i == $len - 1 ) ? ' style="border-bottom:1px solid #eee;"' : ''; ?>>
									<label><?php echo ( $i == 0 ) ? __( 'Content Blocking', 'wp-members' ) : '&nbsp;'; ?></label>
									 <?php
									$block  = ( isset( $wpmem->block[ $key ] ) ) ? $wpmem->block[ $key ] : '';
									$values = array(
										__( 'Do not block', 'wp-members' ) . '|0',
										__( 'Block', 'wp-members' ) . '|1',
										// @todo Future development. __( 'Hide', 'wp-members' ) . '|2',
									);
									echo wpmem_create_formfield( 'wpmem_block_' . $key, 'select', $values, $block ); ?>
									<span><?php echo $val; ?></span><?php // @todo - this needs to be translatable. ?>
								</li>
								<?php $i++;
								}
							}

							// Show Excerpts, Login Form, and Registration Form option groups.

							$option_group_array = array( 
								'show_excerpt' => __( 'Show Excerpts', 'wp-members' ), 
								'show_login'   => __( 'Show Login Form', 'wp-members' ), 
								'show_reg'     => __( 'Show Registration Form', 'wp-members' ),
								'autoex'       => __( 'Auto Excerpt:', 'wp-members' ),
							);

							foreach ( $option_group_array as $item_key => $item_val ) {
								$i = 0;
								$len = count( $post_arr );
								foreach ( $post_arr as $key => $val ) {
									if ( $key == 'post' || $key == 'page' || ( isset( $wpmem->post_types ) && array_key_exists( $key, $wpmem->post_types ) ) ) {
									?>
									<li<?php echo ( $i == $len - 1 ) ? ' style="border-bottom:1px solid #eee;"' : ''; ?>>
										<label><?php echo ( $i == 0 ) ? $item_val : '&nbsp;'; ?></label>
									<?php if ( 'autoex' == $item_key ) { 
										if ( isset( $wpmem->{$item_key}[ $key ] ) && $wpmem->{$item_key}[ $key ]['enabled'] == 1 ) {
											$setting = 1; 
											$ex_len  = $wpmem->{$item_key}[ $key ]['length'];
											$ex_text = ( isset( $wpmem->{$item_key}[ $key ]['text'] ) ) ? $wpmem->{$item_key}[ $key ]['text'] : '';
										} else {
											$setting = 0;
											$ex_len  = '';
											$ex_text = ''; 
										}
										echo wpmem_create_formfield( 'wpmem_' . $item_key . '_' . $key, 'checkbox', '1', $setting ); ?> <span><?php echo $val; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;
										<span><?php _e( 'Number of words in excerpt:', 'wp-members' ); ?> </span><input name="wpmem_autoex_<?php echo $key; ?>_len" type="text" size="5" value="<?php echo $ex_len; ?>" />&nbsp;&nbsp;&nbsp;&nbsp;
										<span><?php _e( 'Custom read more link (optional):', 'wp-members' ); ?> </span><input name="wpmem_autoex_<?php echo $key; ?>_text" type="text" size="5" value="<?php echo $ex_text; ?>" />
									<?php } else {
										$setting = ( isset( $wpmem->{$item_key}[ $key ] ) ) ? $wpmem->{$item_key}[ $key ] : 0; 
										echo wpmem_create_formfield( 'wpmem_' . $item_key . '_' . $key, 'checkbox', '1', $setting ); ?> <span><?php echo $val; ?></span>
									<?php } ?>
									</li>
									<?php $i++;
									}
								}
							} ?>
							</ul>
							<?php
							if ( WPMEM_EXP_MODULE == true ) {
								$arr = array( 
									array(__('Time-based expiration','wp-members'),'wpmem_settings_time_exp',__('Allows for access to expire','wp-members'),'use_exp'),
									array(__('Trial period','wp-members'),'wpmem_settings_trial',__('Allows for a trial period','wp-members'),'use_trial'),
								); ?>
							<h3><?php _e( 'Subscription Settings', 'wp-members' ); ?></h3>	
							<ul><?php
							for ( $row = 0; $row < count( $arr ); $row++ ) { ?>
							  <li>
								<label><?php echo $arr[ $row ][0]; ?></label>
								<?php echo wpmem_create_formfield( $arr[ $row ][1], 'checkbox', '1', $wpmem->{$arr[ $row ][3]} ); ?>&nbsp;&nbsp;
								<?php if ( $arr[ $row ][2] ) { ?><span class="description"><?php echo $arr[ $row ][2]; ?></span><?php } ?>
							  </li>
							<?php } 
							}?></ul>
							<h3><?php _e( 'Other Settings', 'wp-members' ); ?></h3>
							<ul>
							<?php 
							/** This filter is defined in class-wp-members.php */
							$dropin_folder = apply_filters( 'wpmem_dropin_folder', WPMEM_DROPIN_DIR );
							$arr = array(
								array(__('Notify admin','wp-members'),'wpmem_settings_notify',sprintf(__('Notify %s for each new registration? %s','wp-members'),$admin_email,$chg_email),'notify'),
								array(__('Moderate registration','wp-members'),'wpmem_settings_moderate',__('Holds new registrations for admin approval','wp-members'),'mod_reg'),
								array(__('Ignore warning messages','wp-members'),'wpmem_settings_ignore_warnings',__('Ignores WP-Members warning messages in the admin panel','wp-members'),'warnings'),
								//array(__('Enable dropins', 'wp-members'),'wpmem_settings_enable_dropins',sprintf(__('Enables dropins in %s', 'wp-members'), $dropin_folder),'dropins'),
							);
							for ( $row = 0; $row < count( $arr ); $row++ ) { ?>
							  <li>
								<label><?php echo $arr[ $row ][0]; ?></label>
								<?php echo wpmem_create_formfield( $arr[ $row ][1], 'checkbox', '1', $wpmem->{$arr[$row][3]} ); ?>&nbsp;&nbsp;
								<?php if ( $arr[$row][2] ) { ?><span class="description"><?php echo $arr[ $row ][2]; ?></span><?php } ?>
							  </li>
							<?php } ?>
							  <li>
								<label><?php _e( 'Attribution', 'wp-members' ); ?></label>
								<?php echo wpmem_create_formfield( 'attribution', 'checkbox', '1', $wpmem->attrib ); ?>&nbsp;&nbsp;
								<span class="description"><?php _e( 'Attribution is appreciated!  Display "powered by" link on register form?', 'wp-members' ); ?></span>
							  </li>
							  <li>
								<label><?php _e( 'Enable CAPTCHA', 'wp-members' ); ?></label>
								<?php $captcha = array( __( 'None', 'wp-members' ) . '|0' );
								if ( 1 == $wpmem->captcha ) {
									$wpmem->captcha = 3; // @todo reCAPTCHA v1 is fully obsolete. Change it to v2.
								}
								$captcha[] = __( 'reCAPTCHA', 'wp-members' ) . '|3';
								$captcha[] = __( 'Really Simple CAPTCHA', 'wp-members' ) . '|2';
								echo wpmem_create_formfield( 'wpmem_settings_captcha', 'select', $captcha, $wpmem->captcha ); ?>
							  </li>
							<h3><?php _e( 'Pages' ); ?></h3>
							  <?php $wpmem_logurl = $wpmem->user_pages['login'];
							  if ( ! $wpmem_logurl ) { $wpmem_logurl = wpmem_use_ssl(); } ?>
							  <li>
								<label><?php _e( 'Login Page:', 'wp-members' ); ?></label>
								<select name="wpmem_settings_logpage" id="wpmem_logpage_select">
								<?php wpmem_admin_page_list( $wpmem_logurl ); ?>
								</select>&nbsp;<span class="description"><?php _e( 'Specify a login page (optional)', 'wp-members' ); ?></span><br />
								<div id="wpmem_logpage_custom">
									<label>&nbsp;</label>
									<input class="regular-text code" type="text" name="wpmem_settings_logurl" value="<?php echo $wpmem_logurl; ?>" size="50" />
								</div>
							  </li>
							  <?php $wpmem_regurl = $wpmem->user_pages['register'];
							  if ( ! $wpmem_regurl ) { $wpmem_regurl = wpmem_use_ssl(); } ?>
							  <li>
								<label><?php _e( 'Register Page:', 'wp-members' ); ?></label>
								<select name="wpmem_settings_regpage" id="wpmem_regpage_select">
									<?php wpmem_admin_page_list( $wpmem_regurl ); ?>
								</select>&nbsp;<span class="description"><?php _e( 'For creating a register link in the login form', 'wp-members' ); ?></span><br />
								<div id="wpmem_regpage_custom">
									<label>&nbsp;</label>
									<input class="regular-text code" type="text" name="wpmem_settings_regurl" value="<?php echo $wpmem_regurl; ?>" size="50" />
								</div>
							  </li>
							  <?php $wpmem_msurl = $wpmem->user_pages['profile'];
							  if ( ! $wpmem_msurl ) { $wpmem_msurl = wpmem_use_ssl(); } ?>
							  <li>
								<label><?php _e( 'User Profile Page:', 'wp-members' ); ?></label>
								<select name="wpmem_settings_mspage" id="wpmem_mspage_select">
								<?php wpmem_admin_page_list( $wpmem_msurl ); ?>
								</select>&nbsp;<span class="description"><?php _e( 'For creating a forgot password link in the login form', 'wp-members' ); ?></span><br />
								<div id="wpmem_mspage_custom">
									<label>&nbsp;</label>
									<input class="regular-text code" type="text" name="wpmem_settings_msurl" value="<?php echo $wpmem_msurl; ?>" size="50" />
								</div>
							  </li>
							<h3><?php _e( 'Stylesheet' ); ?></h3>
							  <li>
								<label><?php _e( 'Stylesheet' ); ?>:</label>
								<select name="wpmem_settings_style" id="wpmem_stylesheet_select">
								<?php wpmem_admin_style_list( $wpmem->style ); ?>
								</select>
							  </li>
							  <?php $wpmem_cssurl = $wpmem->cssurl;
							  if ( ! $wpmem_cssurl ) { $wpmem_cssurl = wpmem_use_ssl(); } ?>
							  <div id="wpmem_stylesheet_custom">
								  <li>
									<label><?php _e( 'Custom Stylesheet:', 'wp-members' ); ?></label>
									<input class="regular-text code" type="text" name="wpmem_settings_cssurl" value="<?php echo $wpmem_cssurl; ?>" size="50" />
								  </li>
							  </div>
								<br /></br />
								<input type="hidden" name="wpmem_admin_a" value="update_settings">
								<?php submit_button( __( 'Update Settings', 'wp-members' ) ); ?>
							</ul>
						</form>
					</div><!-- .inside -->
				</div>
				<?php if ( $post_types ) { ?>
				<div class="postbox">
					<h3><span><?php _e( 'Custom Post Types', 'wp-members' ); ?></span></h3>
					<div class="inside">
						<form name="updatecpts" id="updatecpts" method="post" action="<?php echo wpmem_admin_form_post_url(); ?>">
						<?php wp_nonce_field( 'wpmem-update-cpts' ); ?>
							<table class="form-table">
								<tr>
									<th scope="row"><?php _e( 'Add to WP-Members Settings', 'wp-members' ); ?></th>
									<td><fieldset><?php
									foreach ( $post_arr as $key => $val ) {
										if ( 'post' != $key && 'page' != $key ) {
											$checked = ( isset( $wpmem->post_types ) && array_key_exists( $key, $wpmem->post_types ) ) ? ' checked' : '';
											echo '<label for="' . $key . '"><input type="checkbox" name="wpmembers_handle_cpts[]" value="' . $key . '"' . $checked . ' />' . $val . '</label><br />';
										}
									}
									?></fieldset>
									</td>
								</tr>
								<tr>
									<input type="hidden" name="wpmem_admin_a" value="update_cpts" />
									<td colspan="2"><?php submit_button( __( 'Update Settings', 'wp-members' ) ); ?></td>
								</tr>
							</table>
						</form>
					</div>
				</div>
				<?php } ?>
			</div><!-- #post-body-content -->
		</div><!-- #post-body -->
	</div><!-- .metabox-holder -->
	<?php
}


/**
 * Updates the plugin settings on Custom Post Types to manage.
 *
 * @since 3.0.9
 *
 * @return string The updated message.
 */
function wpmem_update_cpts() {

	// Check nonce.
	check_admin_referer( 'wpmem-update-cpts' );

	// Get the main settings array as it stands.
	$wpmem_newsettings = get_option( 'wpmembers_settings' );

	// Assemble CPT settings.
	$cpts = array();

	$post_arr = array();
	$post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names', 'and' );
	if ( $post_types ) {
		foreach ( $post_types as $post_type ) { 
			$cpt_obj = get_post_type_object( $post_type );
			$post_arr[ $cpt_obj->name ] = $cpt_obj->labels->name;
		}
	}

	$post_vals = ( isset( $_POST['wpmembers_handle_cpts'] ) ) ? $_POST['wpmembers_handle_cpts'] : false;
	if ( $post_vals ) {
		foreach ( $post_vals as $val ) {
			$cpts[ $val ] = sanitize_text_field( $post_arr[ $val ] );
		}
	} else {
		$cpts = array();
	}
	$wpmem_newsettings['post_types'] = $cpts;

	// Update settings, remove or add CPTs.
	$chk_settings = array( 'block', 'show_excerpt', 'show_login', 'show_reg', 'autoex' );
	foreach ( $chk_settings as $chk ) {
		// Handle removing unmanaged CPTs.
		foreach ( $wpmem_newsettings[ $chk ] as $key => $val ) {
			if ( 'post' != $key && 'page' != $key ) {
				// If the $key is not in managed CPTs, remove it.
				if ( ! array_key_exists( $key, $cpts ) ) {
					unset( $wpmem_newsettings[ $chk ][ $key ] );
				}
			}
		}
		// Handle adding managed CPTs.
		foreach ( $cpts as $key => $val ) {
			if ( ! array_key_exists( $key, $wpmem_newsettings[ $chk ] ) ) {
				if ( 'autoex' == $chk ) {
					// Auto excerpt is an array.
					$wpmem_newsettings[ $chk ][ $key ] = array(
						'enabled' => 0,
						'length'  => '',
					);
				} else {
					// All other settings are 0|1.
					$wpmem_newsettings[ $chk ][ $key ] = 0;
				}
			}
		}
	}

	wpmem_admin_new_settings( $wpmem_newsettings );

	return __( 'Custom Post Type settings were updated', 'wp-members' );
}


/**
 * Updates the plugin options.
 *
 * @since 2.8.0
 *
 * @global object $wpmem The WP_Members object.
 * @return string        The options updated message.
 */
function wpmem_update_options() {
	global $wpmem;

	// Check nonce.
	check_admin_referer( 'wpmem-update-settings' );

	$wpmem_settings_msurl  = ( $_POST['wpmem_settings_mspage'] == 'use_custom' ) ? esc_url( $_POST['wpmem_settings_msurl'] ) : '';
	$wpmem_settings_mspage = ( $_POST['wpmem_settings_mspage'] == 'use_custom' ) ? '' : filter_var( $_POST['wpmem_settings_mspage'], FILTER_SANITIZE_NUMBER_INT );
	if ( $wpmem_settings_msurl != wpmem_use_ssl() && $wpmem_settings_msurl != 'use_custom' && ! $wpmem_settings_mspage ) {
		$msurl = trim( $wpmem_settings_msurl );
	} else {
		$msurl = $wpmem_settings_mspage;
	}

	$wpmem_settings_regurl  = ( $_POST['wpmem_settings_regpage'] == 'use_custom' ) ? esc_url( $_POST['wpmem_settings_regurl'] ) : '';
	$wpmem_settings_regpage = ( $_POST['wpmem_settings_regpage'] == 'use_custom' ) ? '' : filter_var( $_POST['wpmem_settings_regpage'], FILTER_SANITIZE_NUMBER_INT );
	if ( $wpmem_settings_regurl != wpmem_use_ssl() && $wpmem_settings_regurl != 'use_custom' && ! $wpmem_settings_regpage ) {
		$regurl = trim( $wpmem_settings_regurl );
	} else {
		$regurl = $wpmem_settings_regpage;
	}

	$wpmem_settings_logurl  = ( $_POST['wpmem_settings_logpage'] == 'use_custom' ) ? esc_url( $_POST['wpmem_settings_logurl'] ) : '';
	$wpmem_settings_logpage = ( $_POST['wpmem_settings_logpage'] == 'use_custom' ) ? '' : filter_var( $_POST['wpmem_settings_logpage'], FILTER_SANITIZE_NUMBER_INT );
	if ( $wpmem_settings_logurl != wpmem_use_ssl() && $wpmem_settings_logurl != 'use_custom' && ! $wpmem_settings_logpage ) {
		$logurl = trim( $wpmem_settings_logurl );
	} else {
		$logurl = $wpmem_settings_logpage;
	}

	$wpmem_settings_cssurl = esc_url( $_POST['wpmem_settings_cssurl'] );
	$cssurl = ( $wpmem_settings_cssurl != wpmem_use_ssl() ) ? trim( $wpmem_settings_cssurl ) : '';

	$wpmem_settings_style = ( isset( $_POST['wpmem_settings_style'] ) ) ? sanitize_text_field( $_POST['wpmem_settings_style'] ) : false;

	$wpmem_newsettings = array(
		'version' => WPMEM_VERSION,
		'notify'    => filter_var( wpmem_get( 'wpmem_settings_notify', 0 ), FILTER_SANITIZE_NUMBER_INT ),
		'mod_reg'   => filter_var( wpmem_get( 'wpmem_settings_moderate', 0 ), FILTER_SANITIZE_NUMBER_INT ),
		'captcha'   => filter_var( wpmem_get( 'wpmem_settings_captcha', 0 ), FILTER_SANITIZE_NUMBER_INT ),
		'use_exp'   => filter_var( wpmem_get( 'wpmem_settings_time_exp', 0 ), FILTER_SANITIZE_NUMBER_INT ),
		'use_trial' => filter_var( wpmem_get( 'wpmem_settings_trial', 0 ), FILTER_SANITIZE_NUMBER_INT ),
		'warnings'  => filter_var( wpmem_get( 'wpmem_settings_ignore_warnings', 0 ), FILTER_SANITIZE_NUMBER_INT ),
		'dropins'   => filter_var( wpmem_get( 'wpmem_settings_enable_dropins', 0 ), FILTER_SANITIZE_NUMBER_INT ),
		'user_pages' => array(
			'profile'  => ( $msurl  ) ? $msurl  : '',
			'register' => ( $regurl ) ? $regurl : '',
			'login'    => ( $logurl ) ? $logurl : '',
		),
		'cssurl'    => ( $cssurl ) ? $cssurl : '',
		'style'     => $wpmem_settings_style,
		'attrib'    => filter_var( wpmem_get( 'attribution', 0 ), FILTER_SANITIZE_NUMBER_INT ),
	);

	// Build an array of post types
	$post_arr = array( 'post', 'page' );
	if ( isset( $wpmem->post_types ) ) {
		$wpmem_newsettings['post_types'] = $wpmem->post_types;
		foreach ( $wpmem_newsettings['post_types'] as $key => $val ) { 
			$post_arr[] = $key;
		}
	}

	// Leave form tag settings alone.
	if ( isset( $wpmem->form_tags ) ) {
		$wpmem_newsettings['form_tags'] = $wpmem->form_tags;
	}

	// Leave email settings alone.
	if ( isset( $wpmem->email ) ) {
		$wpmem_newsettings['email'] = $wpmem->email;
	}

	// Get settings for blocking, excerpts, show login, and show registration for posts, pages, and custom post types.
	$option_group_array = array( 'block', 'show_excerpt', 'show_login', 'show_reg', 'autoex' );
	foreach ( $option_group_array as $option_group_item ) {
		$arr = array();
		foreach ( $post_arr as $post_type ) {
			$post_var = 'wpmem_' . $option_group_item . '_' . $post_type;
			if ( $option_group_item == 'autoex' ) {
				// Auto excerpt is an array.
				$arr[ $post_type ]['enabled'] = ( isset( $_POST[ $post_var ]           ) ) ? filter_var( $_POST[ $post_var ], FILTER_SANITIZE_NUMBER_INT ) : 0;
				$arr[ $post_type ]['length']  = ( isset( $_POST[ $post_var . '_len'  ] ) ) ? ( ( $_POST[ $post_var . '_len' ] == '' ) ? 0 : filter_var( $_POST[ $post_var . '_len' ], FILTER_SANITIZE_NUMBER_INT ) ) : '';
				$arr[ $post_type ]['text']    = ( isset( $_POST[ $post_var . '_text' ] ) ) ? sanitize_text_field( $_POST[ $post_var . '_text' ] ) : '';
			} else {
				// All other settings are 0|1.
				$arr[ $post_type ] = ( isset( $_POST[ $post_var ] ) ) ? filter_var( $_POST[ $post_var ], FILTER_SANITIZE_NUMBER_INT ) : 0;
			}
		}
		$wpmem_newsettings[ $option_group_item ] = $arr;
	}

	/*
	 * If we are setting registration to be moderated, 
	 * check to see if the current admin has been 
	 * activated so they don't accidentally lock themselves
	 * out later.
	 */
	if ( isset( $_POST['wpmem_settings_moderate'] ) == 1 ) {
		global $current_user;
		wp_get_current_user();
		$user_ID = $current_user->ID;
		update_user_meta( $user_ID, 'active', 1 );
	}

	wpmem_admin_new_settings( $wpmem_newsettings );

	return __( 'WP-Members settings were updated', 'wp-members' );
}


/**
 * Puts new settings into the current object.
 *
 * @since 3.0.9
 *
 * @global $wpmem
 * @param $new
 * @return $settings
 */
function wpmem_admin_new_settings( $new ) {

	// Update saved settings.
	update_option( 'wpmembers_settings', $new );

	// Update the current WP_Members object with the new settings.
	global $wpmem;
	foreach ( $new as $key => $val ) {
		if ( 'user_pages' == $key ) {
			foreach ( $val as $subkey => $subval ) {
				$val[ $subkey ] = ( is_numeric( $subval ) ) ? get_page_link( $subval ) : $subval;
			}
		}
		$wpmem->$key = $val;
	}
}


/**
 * Create the stylesheet dropdown selection.
 *
 * @since 2.8
 *
 * @param $style string The stored stylesheet setting.
 */
function wpmem_admin_style_list( $style ) {

	$list = array(
		'No Float'                   => WPMEM_DIR . 'css/generic-no-float.css',
		'Rigid'                      => WPMEM_DIR . 'css/generic-rigid.css',
		'Twenty Sixteen - no float'  => WPMEM_DIR . 'css/wp-members-2016-no-float.css',
		'Twenty Fifteen'             => WPMEM_DIR . 'css/wp-members-2015.css',
		'Twenty Fifteen - no float'  => WPMEM_DIR . 'css/wp-members-2015-no-float.css',
		'Twenty Fourteen'            => WPMEM_DIR . 'css/wp-members-2014.css',
		'Twenty Fourteen - no float' => WPMEM_DIR . 'css/wp-members-2014-no-float.css',
		'Twenty Thirteen'            => WPMEM_DIR . 'css/wp-members-2013.css',
		'Twenty Twelve'              => WPMEM_DIR . 'css/wp-members-2012.css',
		'Twenty Eleven'              => WPMEM_DIR . 'css/wp-members-2011.css',
		'Twenty Ten'                 => WPMEM_DIR . 'css/wp-members.css',
		//'Kubrick'                    => WPMEM_DIR . 'css/wp-members-kubrick.css',
	);

	/**
	 * Filters the list of stylesheets in the plugin options dropdown.
	 *
	 * @since 2.8.0
	 *
	 * @param array $list An array of stylesheets that can be applied to the plugin's forms.
	 */
	$list = apply_filters( 'wpmem_admin_style_list', $list );

	$selected = false;
	foreach ( $list as $name => $location ) {
		$selected = ( $location == $style ) ? true : $selected;
		echo '<option value="' . $location . '" ' . selected( $location, $style ) . '>' . $name . "</option>\n";
	}
	$selected = ( ! $selected ) ? ' selected' : '';
	echo '<option value="use_custom"' . $selected . '>' . __( 'USE CUSTOM URL BELOW', 'wp-members' ) . '</option>';

	return;
}


/**
 * Create a dropdown selection of pages.
 *
 * @since 2.8.1
 * @todo  Consider wp_dropdown_pages. Can be retrieved as HTML (echo=false) and str_replaced to add custom values.
 *
 * @param string $val
 */
function wpmem_admin_page_list( $val, $show_custom_url = true ) {

	$selected = ( $val == 'http://' || $val == 'https://' ) ? 'select a page' : false;
	$pages    = get_pages();

	echo '<option value=""'; echo ( $selected == 'select a page' ) ? ' selected' : ''; echo '>'; echo esc_attr( __( 'Select a page', 'wp-members' ) ); echo '</option>';

	foreach ( $pages as $page ) {
		$selected = ( get_page_link( $page->ID ) == $val ) ? true : $selected; //echo "VAL: " . $val . ' PAGE LINK: ' . get_page_link( $page->ID );
		$option   = '<option value="' . $page->ID . '"' . selected( get_page_link( $page->ID ), $val, 'select' ) . '>';
		$option  .= $page->post_title;
		$option  .= '</option>';
		echo $option;
	}
	if ( $show_custom_url ) {
		$selected = ( ! $selected ) ? ' selected' : '';
		echo '<option value="use_custom"' . $selected . '>' . __( 'USE CUSTOM URL BELOW', 'wp-members' ) . '</option>'; 
	}
}

// End of file.