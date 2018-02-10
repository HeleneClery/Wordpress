<?php
/**
 * WP-Members Installation Functions
 *
 * Functions to install and upgrade WP-Members.
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
 * - wpmem_do_install
 * - wpmem_upgrade_settings
 * - wpmem_upgrade_email
 * - wpmem_upgrade_dialogs
 * - wpmem_downgrade_dialogs
 * - wpmem_upgrade_captcha
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Installs or upgrades the plugin.
 *
 * @since 2.2.2
 * @since 3.1.6 Returns $wpmem_settings.
 *
 * @return array $wpmem_settings
 */
function wpmem_do_install() {

	/*
	 * If you need to force an install, set $chk_force = true.
	 *
	 * Important notes:
	 *
	 * 1. This will override any settings you already have for any of the plugin settings.
	 * 2. This will not effect any WP settings or registered users.
	 */

	$chk_force = false;

	if ( ! get_option( 'wpmembers_settings' ) || $chk_force == true ) {

		$wpmem_settings = wpmem_install_settings();
		wpmem_install_fields();
		wpmem_install_dialogs();
		wpmem_append_email();
		update_option( 'wpmembers_style', plugin_dir_url ( __FILE__ ) . 'css/generic-no-float.css', '', 'yes' );

	} else {
		
		$wpmem_settings = wpmem_upgrade_settings();
		wpmem_upgrade_captcha();
		wpmem_append_email();
		
	}
	
	return $wpmem_settings;
}


/**
 * Updates the existing settings if doing an update.
 *
 * @since 3.0.0
 * @since 3.1.0 Changed from wpmem_update_settings() to wpmem_upgrade_settings().
 *
 * @return array $wpmem_newsettings
 */
function wpmem_upgrade_settings() {
	
	// Update dialogs for 3.1.1
	wpmem_upgrade_dialogs();

	$wpmem_settings = get_option( 'wpmembers_settings' );

	// Is this an update from pre-3.0 or 3.0+?
	$is_three = ( array_key_exists( 'version', $wpmem_settings ) ) ? true : false;

	// If install is 3.0 or higher.
	if ( $is_three ) {
		
		// reCAPTCHA v1 is obsolete.
		if ( isset( $wpmem_settings['captcha'] ) && 1 == $wpmem_settings['captcha'] ) {
			$wpmem_settings['captcha'] = 3;
		}
	
		// If old auto excerpt settings exists, update it.
		if ( isset( $wpmem_settings['autoex']['auto_ex'] ) ) {
			// Update Autoex setting.
			if ( $wpmem_settings['autoex']['auto_ex'] == 1 || $wpmem_settings['autoex']['auto_ex'] == "1" ) {
				// If Autoex is set, move it to posts/pages.
				$wpmem_settings['autoex']['post'] = array( 'enabled' => 1, 'length' => $wpmem_settings['autoex']['auto_ex_len'] );
				$wpmem_settings['autoex']['page'] = array( 'enabled' => 1, 'length' => $wpmem_settings['autoex']['auto_ex_len'] );
			} else {
				// If it is not turned on (!=1), set it to off in new setting (-1).
				$wpmem_settings['autoex']['post'] = array( 'enabled' => 0, 'length' => '' );
				$wpmem_settings['autoex']['page'] = array( 'enabled' => 0, 'length' => '' );
			}
			unset( $wpmem_settings['autoex']['auto_ex'] );
			unset( $wpmem_settings['autoex']['auto_ex_len'] );
		}
		
		// If post types settings does not exist, set as empty array.
		if ( ! isset( $wpmem_settings['post_types'] ) ) {
			 $wpmem_settings['post_types'] = array();
		}
		
		// If form tags is not set, add default.
		if ( ! isset( $wpmem_settings['form_tags'] ) ) {
			$wpmem_settings['form_tags'] = array( 'default' => 'Registration Default' );
		}
		
		// If email is not set, add it with existing setting or default.
		if ( ! isset( $wpmem_settings['email'] ) ) {
			$from = get_option( 'wpmembers_email_wpfrom' );
			$name = get_option( 'wpmembers_email_wpname' );
			$wpmem_settings['email'] = array(
				'from'      => ( $from ) ? $from : '',
				'from_name' => ( $name ) ? $name : '',
			);
		}
		
		// Version number should be updated no matter what.
		$wpmem_settings['version'] = WPMEM_VERSION;
		
		update_option( 'wpmembers_settings', $wpmem_settings );
		return $wpmem_settings;
	} else {
		// Update pre 3.0 installs (must be 2.5.1 or higher).
		// Handle show registration setting change.
		$show_reg = ( $wpmem_settings[7] == 0 ) ? 1 : 0;
		// Create new settings array.
		$wpmem_newsettings = array(
			'version' => WPMEM_VERSION,
			'block'   => array(
				'post' => $wpmem_settings[1],
				'page' => $wpmem_settings[2],
			),
			'show_excerpt' => array(
				'post' => $wpmem_settings[3],
				'page' => $wpmem_settings[3],
			),
			'show_reg' => array(
				'post' => $show_reg,
				'page' => $show_reg,
			),
			'show_login' => array(
				'post' => 1,
				'page' => 1,
			),
			'notify'     => $wpmem_settings[4],
			'mod_reg'    => $wpmem_settings[5],
			'captcha'    => ( 1 == $wpmem_settings[6] ) ? 3 : $wpmem_settings[6], // reCAPTCHA v1 is obsolete, move to v2.
			'use_exp'    => $wpmem_settings[9],
			'use_trial'  => $wpmem_settings[10],
			'warnings'   => $wpmem_settings[11],
			'user_pages' => array(
				'profile'  => get_option( 'wpmembers_msurl'  ),
				'register' => get_option( 'wpmembers_regurl' ),
				'login'    => get_option( 'wpmembers_logurl' ),
			),
			'cssurl'     => get_option( 'wpmembers_cssurl' ),
			'style'      => get_option( 'wpmembers_style'  ),
			'attrib'     => get_option( 'wpmembers_attrib' ),
		);
		// Handle auto excerpt setting change and add to setting array.
		$autoex = get_option( 'wpmembers_autoex' );
		if ( $autoex['auto_ex'] == 1 || $autoex['auto_ex'] == "1" ) {
			// If Autoex is set, move it to posts/pages.
			$wpmem_newsettings['autoex']['post'] = array( 'enabled' => 1, 'length' => $autoex['auto_ex_len'] );
			$wpmem_newsettings['autoex']['page'] = array( 'enabled' => 1, 'length' => $autoex['auto_ex_len'] );
		} else {
			// If it is not turned on, set it to off in new setting.		
			$wpmem_newsettings['autoex']['post'] = array( 'enabled' => 0, 'length' => '' );
			$wpmem_newsettings['autoex']['page'] = array( 'enabled' => 0, 'length' => '' );
		}
		
		// Add new settings.
		$wpmem_newsettings['post_types'] = array();
		$wpmem_settings['form_tags'] = array( 'default' => 'Registration Default' );
		$from = get_option( 'wpmembers_email_wpfrom' );
		$name = get_option( 'wpmembers_email_wpname' );
		$wpmem_settings['email'] = array(
			'from'      => ( $from ) ? $from : '',
			'from_name' => ( $name ) ? $name : '',
		);
		
		// Merge settings.
		$wpmem_newsettings = array_merge( $wpmem_settings, $wpmem_newsettings ); 
		
		update_option( 'wpmembers_settings', $wpmem_newsettings );
		
		return $wpmem_newsettings;
	}
}


/**
 * Adds the fields for email messages.
 *
 * Was append_email() since 2.7, changed to wpmem_append_email() in 3.0.
 *
 * @since 2.7
 */
function wpmem_append_email() {

	// Email for a new registration.
	$subj = 'Your registration info for [blogname]';
	$body = 'Thank you for registering for [blogname]

Your registration information is below.
You may wish to retain a copy for your records.

username: [username]
password: [password]

You may log in here:
[reglink]

You may change your password here:
[user-profile]
';

	$arr = array(
		"subj" => $subj,
		"body" => $body,
	);

	if ( ! get_option( 'wpmembers_email_newreg' ) ) {
		update_option( 'wpmembers_email_newreg', $arr, false );
	}

	$arr = $subj = $body = '';

	// Email for new registration, registration is moderated.
	$subj = 'Thank you for registering for [blogname]';
	$body = 'Thank you for registering for [blogname]. 
Your registration has been received and is pending approval.
You will receive login instructions upon approval of your account
';

	$arr = array(
		"subj" => $subj,
		"body" => $body,
	);

	if ( ! get_option( 'wpmembers_email_newmod' ) ) {
		update_option( 'wpmembers_email_newmod', $arr, false );
	}

	$arr = $subj = $body = '';

	// Email for registration is moderated, user is approved.
	$subj = 'Your registration for [blogname] has been approved';
	$body = 'Your registration for [blogname] has been approved.

Your registration information is below.
You may wish to retain a copy for your records.

username: [username]
password: [password]

You may log in and change your password here:
[user-profile]

You originally registered at:
[reglink]
';

	$arr = array( 
		"subj" => $subj,
		"body" => $body,
	);

	if ( ! get_option( 'wpmembers_email_appmod' ) ) {
		update_option( 'wpmembers_email_appmod', $arr, false );
	}

	$arr = $subj = $body = '';

	// Email for password reset.
	$subj = 'Your password reset for [blogname]';
	$body = 'Your password for [blogname] has been reset

Your new password is included below. You may wish to retain a copy for your records.

password: [password]
';

	$arr = array(
		"subj" => $subj,
		"body" => $body,
	);

	if ( ! get_option( 'wpmembers_email_repass' ) ) { 
		update_option( 'wpmembers_email_repass', $arr, false );
	}

	$arr = $subj = $body = '';

	// Email for admin notification.
	$subj = 'New user registration for [blogname]';
	$body = 'The following user registered for [blogname]:

username: [username]
email: [email]

[fields]
This user registered here:
[reglink]

user IP: [user-ip]

activate user: [activate-user]
';

		$arr = array(
		"subj" => $subj,
		"body" => $body,
	);

	if ( ! get_option( 'wpmembers_email_notify' ) ) {
		update_option( 'wpmembers_email_notify', $arr, false );
	}

	$arr = $subj = $body = '';

	// Email footer (no subject).
	$body = '----------------------------------
This is an automated message from [blogname]
Please do not reply to this address';

	if ( ! get_option( 'wpmembers_email_footer' ) ) {
		update_option( 'wpmembers_email_footer', $body, false );
	}
	
	$arr = $subj = $body = '';
	
	// Email for retrieve username.
	$subj = 'Username for [blogname]';
	$body = 'Your username for [blogname] is below.

username: [username]
';

		$arr = array(
		"subj" => $subj,
		"body" => $body,
	);

	if ( ! get_option( 'wpmembers_email_getuser' ) ) {
		update_option( 'wpmembers_email_getuser', $arr, false );
	}

	return true;
}


/**
 * Checks the dialogs array for necessary changes.
 *
 * @since 2.9.3
 * @since 3.0.0 Changed from update_dialogs() to wpmem_update_dialogs().
 * @since 3.1.0 Changed from wpmem_update_dialogs() to wpmem_upgrade_dialogs().
 * @since 3.1.1 Converts numeric dialog array to associative.
 */
function wpmem_upgrade_dialogs() {

	$wpmem_dialogs = get_option( 'wpmembers_dialogs' );
	
	if ( ! array_key_exists( 'restricted_msg', $wpmem_dialogs ) ) {
		// Update is needed.
		$new_arr  = array();
		$new_keys = array( 'restricted_msg', 'user', 'email', 'success', 'editsuccess', 'pwdchangerr', 'pwdchangesuccess', 'pwdreseterr', 'pwdresetsuccess' );
		foreach ( $wpmem_dialogs as $key => $val ) {
			$new_arr[ $new_keys[ $key ] ] = $val;
		}
		update_option( 'wpmembers_dialogs', $new_arr, '', 'yes' );
	}

	return;
}


/**
 * Downgrades dialogs array for pre-3.1.1 version rollback.
 *
 * @since 3.1.1
 */
function wpmem_downgrade_dialogs() {
	
	$wpmem_dialogs = get_option( 'wpmembers_dialogs' );
	
	if ( array_key_exists( 'restricted_msg', $wpmem_dialogs ) ) {
		// Update is needed.
		$new_arr  = array();
		$i = 0;
		foreach ( $wpmem_dialogs as $key => $val ) {
			$new_arr[ $i ] = $val;
			$i++;
		}
		update_option( 'wpmembers_dialogs', $new_arr, '', 'yes' );
	}

	return;
}


/**
 * Checks the captcha settings and updates accordingly.
 *
 * Was update_captcha() since 2.9.5, changed to wpmem_update_captcha() in 3.0.
 *
 * @since 2.9.5
 * @since 3.0.0 Changed from update_captcha() to wpmem_update_captcha().
 * @since 3.1.0 Changed from wpmem_update_captcha() to wpmem_upgrade_captcha().
 */
function wpmem_upgrade_captcha() {

	$captcha_settings = get_option( 'wpmembers_captcha' );

	// If there captcha settings, update them.
	if ( $captcha_settings && ! array_key_exists( 'recaptcha', $captcha_settings ) ) {

		// Check to see if the array keys are numeric.
		$is_numeric = false;
		foreach ( $captcha_settings as $key => $setting ) {
			$is_numeric = ( is_int( $key ) ) ? true : $is_numeric;
		}

		if ( $is_numeric ) {
			$new_captcha = array();
			// These are old recaptcha settings.
			$new_captcha['recaptcha']['public']  = $captcha_settings[0];
			$new_captcha['recaptcha']['private'] = $captcha_settings[1];
			$new_captcha['recaptcha']['theme']   = $captcha_settings[2];
			update_option( 'wpmembers_captcha', $new_captcha );
		}
	}
	return;
}

/**
 * Does install of default settings.
 *
 * @since 3.1.5
 * @since 3.1.6 Returns $wpmem_settings
 *
 * @return array $wpmem_settings
 */
function wpmem_install_settings() {
		
	$wpmem_settings = array(
		'version' => WPMEM_VERSION,
		'block'   => array(
			'post' => ( is_multisite() ) ? 0 : 1,
			'page' => 0,
		),
		'show_excerpt' => array(
			'post' => 0,
			'page' => 0,
		),
		'show_reg' => array(
			'post' => 1,
			'page' => 1,
		),
		'show_login' => array(
			'post' => 1,
			'page' => 1,
		),
		'autoex' => array(
			'post' => array( 'enabled' => 0, 'length' => '' ),
			'page' => array( 'enabled' => 0, 'length' => '' ),
		),
		'notify'    => 0,
		'mod_reg'   => 0,
		'captcha'   => 0,
		'use_exp'   => 0,
		'use_trial' => 0,
		'warnings'  => 0,
		'user_pages' => array(
			'profile'  => '',
			'register' => '',
			'login'    => '',
		),
		'cssurl'    => '',
		'style'     => plugin_dir_url ( __FILE__ ) . 'css/generic-no-float.css',
		'attrib'    => 0,
		'post_types' => array(),
		'form_tags'  => array( 'default' => 'Registration Default' ),
		'email'      => array( 'from' => '', 'from_name' => '' ),
	);
	
	// Using update_option to allow for forced update.
	update_option( 'wpmembers_settings', $wpmem_settings, '', 'yes' );
	
	return $wpmem_settings;
}

/**
 * Installs default fields.
 *
 * @since 3.1.5
 *
 * @return array $fields {
 *    @type array {
 *        order, 
 *        label, 
 *        meta key, 
 *        type, 
 *        display, 
 *        required, 
 *        native, 
 *        checked value, 
 *        checked by default,
 *     }
 * }
 */
function wpmem_install_fields() {
	$fields = array(
		array( 1,  'First Name',         'first_name',       'text',     'y', 'y', 'y' ),
		array( 2,  'Last Name',          'last_name',        'text',     'y', 'y', 'y' ),
		array( 3,  'Address 1',          'addr1',            'text',     'y', 'y', 'n' ),
		array( 4,  'Address 2',          'addr2',            'text',     'y', 'n', 'n' ),
		array( 5,  'City',               'city',             'text',     'y', 'y', 'n' ),
		array( 6,  'State',              'thestate',         'text',     'y', 'y', 'n' ),
		array( 7,  'Zip',                'zip',              'text',     'y', 'y', 'n' ),
		array( 8,  'Country',            'country',          'text',     'y', 'y', 'n' ),
		array( 9,  'Day Phone',          'phone1',           'text',     'y', 'y', 'n' ),
		array( 10, 'Email',              'user_email',       'email',    'y', 'y', 'y' ),
		array( 11, 'Confirm Email',      'confirm_email',    'email',    'n', 'n', 'n' ),
		array( 12, 'Website',            'user_url',         'url',      'n', 'n', 'y' ),
		array( 13, 'Biographical Info',  'description',      'textarea', 'n', 'n', 'y' ),
		array( 14, 'Password',           'password',         'password', 'n', 'n', 'n' ),
		array( 15, 'Confirm Password',   'confirm_password', 'password', 'n', 'n', 'n' ),
		array( 16, 'TOS',                'tos',              'checkbox', 'n', 'n', 'n', 'agree', 'n' ),
	);
	update_option( 'wpmembers_fields', $fields, '', 'yes' ); // using update_option to allow for forced update
	return $fields;
}

/**
 * Installs default dialogs.
 *
 * @since 3.1.5
 */
function wpmem_install_dialogs() {
	$wpmem_dialogs_arr = array(
		'restricted_msg'   => "This content is restricted to site members.  If you are an existing user, please log in.  New users may register below.",
		'user'             => "Sorry, that username is taken, please try another.",
		'email'            => "Sorry, that email address already has an account.<br />Please try another.",
		'success'          => "Congratulations! Your registration was successful.<br /><br />You may now log in using the password that was emailed to you.",
		'editsuccess'      => "Your information was updated!",
		'pwdchangerr'      => "Passwords did not match.<br /><br />Please try again.",
		'pwdchangesuccess' => "Password successfully changed!",
		'pwdreseterr'      => "Either the username or email address do not exist in our records.",
		'pwdresetsuccess'  => "Password successfully reset!<br /><br />An email containing a new password has been sent to the email address on file for your account.",
	);
	// Insert TOS dialog placeholder.
	$dummy_tos = "Put your TOS (Terms of Service) text here.  You can use HTML markup.";
	update_option( 'wpmembers_tos', $dummy_tos );
	update_option( 'wpmembers_dialogs', $wpmem_dialogs_arr, '', 'yes' ); // using update_option to allow for forced update
}

// End of file.