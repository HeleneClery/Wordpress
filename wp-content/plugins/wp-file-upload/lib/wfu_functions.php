<?php

//********************* Debug Functions ***************************************************************************************************

/**
 *  Hook on plugin's functions
 *  
 *  This is a very powerful function that enables almost all plugin functions to
 *  be redeclared. In order to make a function redeclarable we just put the
 *  following code at the top of its function block:
 *  $a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { 
 *  case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
 *  Then the function can be hooked through the filter wfu_debug-{__FUNCTION__}.
 *  The hook function takes the same parameters as the original function, plus
 *  one, which comes first and determines the behaviour of the hook function.
 *  The hook function must return an array having two items, 'output' and
 *  'result'. Item 'output' is the return value of the hook function (if
 *  required). Item 'result' denotes how the hook function will be handled. If
 *  'result' is 'X' then the result of the hook function will be ignored. If
 *  'result' is 'R' then the original function will terminate returning the
 *  output of the hook function. So it is like having been entirely substituted
 *  by the hook function. If 'result' is 'D' then the original function will die
 *  returning the output of the hook function. This applies to ajax handlers.
 *  It is noted that together with the hook, a global variable with name
 *  wfu_debug-{__FUNCTION__} must also be declared otherwise the hook will not
 *  work. This has been added to improve performance.
 *  
 *  @param string $function the function name of the original function
 *  @param array $args an array of parameters of the original function
 *  @param string $out it stores the output of the hook function

 *  @return string returns how the hook function will be handled ('X': hook
 *          output must be ignored, 'R': the original function must return the
 *          hook's output, 'D': the original function must die returning the
 *          hook's output)
 */
function WFU_FUNCTION_HOOK($function, $args, &$out) {
	// exit if plugin's debug mode is off or the hook has not been declared in
	// global variables
	if ( WFU_VAR("WFU_DEBUG") != "ON" || !isset($GLOBALS["wfu_debug-".$function]) ) return 'X';
	// exit if function name is empty or invalid
	if ( $function == "" || preg_replace("/[^0-9a-zA-Z_]/", "", $function) != $function ) return 'X';
	// run the hook
	array_splice($args, 0, 0, array( array( "output" => "", "result" => "X" ) ));
	$res = apply_filters_ref_array("wfu_debug-".$function, $args);
	// exit if $res is invalid
	if ( !is_array($res) || !isset($res["output"]) || !isset($res["result"]) ) return 'X';
	$out = $res["output"];
	// if result is 'X' then the caller must ignore the hook
	// if result is 'R' then the caller must return the hook's output
	// if result is 'D' then the caller must die returning the hook's output
	return $res["result"];
}

//********************* String Functions ***************************************************************************************************

function wfu_upload_plugin_clean($filename) {
	$clean = sanitize_file_name($filename);
	if ( WFU_VAR("WFU_SANITIZE_FILENAME_MODE") != "loose" ) {
		$name = wfu_filename($clean);
		$ext = wfu_fileext($clean);
		if ( WFU_VAR("WFU_SANITIZE_FILENAME_DOTS") == "true" ) $name_search = array ( '@[^a-zA-Z0-9_]@' );
		else $name_search = array ( '@[^a-zA-Z0-9._]@' );
		$ext_search = array ( '@[^a-zA-Z0-9._]@' );	 
		$replace = array ( '-' );
		$clean_name =  preg_replace($name_search, $replace, remove_accents($name));
		$clean_ext =  preg_replace($ext_search, $replace, remove_accents($ext));
		$clean = $clean_name.".".$clean_ext;
	}

	return $clean;
}

function _wildcard_to_preg_preg_replace_callback($matches) {
    global $wfu_preg_replace_callback_var;
    array_push($wfu_preg_replace_callback_var, $matches[0]);
    $key = count($wfu_preg_replace_callback_var) - 1;
    return "[".$key."]";
}

function wfu_upload_plugin_wildcard_to_preg($pattern, $strict = false) {
	global $wfu_preg_replace_callback_var;
	$wfu_preg_replace_callback_var = array();
	$pattern = preg_replace_callback("/\[(.*?)\]/", "_wildcard_to_preg_preg_replace_callback", $pattern);
	if ( !$strict ) $pattern = '/^' . str_replace(array('\*', '\?', '\[', '\]'), array('.*', '.', '[', ']'), preg_quote($pattern)) . '$/is';
	else $pattern = '/^' . str_replace(array('\*', '\?', '\[', '\]'), array('[^.]*', '.', '[', ']'), preg_quote($pattern)) . '$/is';
	foreach ($wfu_preg_replace_callback_var as $key => $match)
		$pattern = str_replace("[".$key."]", $match, $pattern);
	return $pattern;
}

function wfu_upload_plugin_wildcard_to_mysqlregexp($pattern) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	if ( substr($pattern, 0, 6) == "regex:" ) return str_replace("\\", "\\\\", substr($pattern, 6));
	else return str_replace("\\", "\\\\", '^'.str_replace(array('\*', '\?', '\[', '\]'), array('.*', '.', '[', ']'), preg_quote($pattern)).'$');
}

function wfu_upload_plugin_wildcard_match($pattern, $str, $strict = false) {
	$pattern = wfu_upload_plugin_wildcard_to_preg($pattern, $strict);
	return preg_match($pattern, $str);
}

function wfu_plugin_encode_string($string) {
	$array = unpack('H*', $string);
	return $array[1];

	$array = unpack('C*', $string);
	$new_string = "";	
	for ($i = 1; $i <= count($array); $i ++) {
		$new_string .= sprintf("%02X", $array[$i]);
	}
	return $new_string;
}

function wfu_plugin_decode_string($string) {
	return pack('H*', $string);

	$new_string = "";	
	for ($i = 0; $i < strlen($string); $i += 2 ) {
		$new_string .= sprintf("%c", hexdec(substr($string, $i ,2)));
	}
	return $new_string;
}

function wfu_create_random_string($len) {
	$base = 'ABCDEFGHKLMNOPQRSTWXYZabcdefghjkmnpqrstwxyz123456789';
	$max = strlen($base) - 1;
	$activatecode = '';
	if ( WFU_VAR("WFU_ALTERNATIVE_RANDOMIZER") != "true" )
		mt_srand((double)microtime()*1000000);
	else mt_srand((double)substr(uniqid("", true), 15));
	while (strlen($activatecode) < $len)
		$activatecode .= $base{mt_rand(0, $max)};
	return $activatecode;
}

function wfu_join_strings($delimeter) {
	$arr = func_get_args();
	unset($arr[0]);
	foreach ($arr as $key => $item)
		if ( $item == "" ) unset($arr[$key]);
	return join($delimeter, $arr);
}

function wfu_create_string($size) {
	$piece = str_repeat("0", 1024);
	$str = "";
	$reps = $size / 1024;
	$rem = $size - 1024 * $reps;
	for ( $i = 0; $i < $reps; $i++ ) $str .= $piece;
	$str .= substr($piece, 0, $rem);
	return $str;
}

function wfu_html_output($output) {
	$output = str_replace(array("\r\n", "\r", "\n"), "<br/>", $output);
	return str_replace(array("\t", " "), "&nbsp;", $output);
}

function wfu_sanitize_code($code) {
	return preg_replace("/[^A-Za-z0-9]/", "", $code);
}

function wfu_sanitize_int($code) {
	return preg_replace("/[^0-9+\-]/", "", $code);
}

function wfu_sanitize_tag($code) {
	return preg_replace("/[^A-Za-z0-9_]/", "", $code);
}

function wfu_sanitize_url($url) {
	return filter_var(strip_tags($url), FILTER_SANITIZE_URL);
}

function wfu_sanitize_urls($urls, $separator) {
	$urls_arr = explode($separator, $urls);
	foreach( $urls_arr as &$url ) $url = wfu_sanitize_url($url);
	return implode($separator, $urls_arr);
}

function wfu_slash( $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $k => $v ) {
			if ( is_array( $v ) ) {
				$value[$k] = wfu_slash( $v );
			}
			else {
				$value[$k] = addslashes( $v );
			}
		}
	}
	else {
		$value = addslashes( $value );
	}

	return $value;
}

function wfu_generate_global_short_token($timeout) {
	$token = wfu_create_random_string(16);
	$expire = time() + (int)$timeout;
	update_option('wfu_gst_'.$token, $expire);
	return $token;
}

function wfu_verify_global_short_token($token) {
	$timeout = get_option('wfu_gst_'.$token);
	if ( $timeout === false ) return false;
	delete_option('wfu_gst_'.$token);
	return ( $timeout > time() );
}

//********************* Array Functions ****************************************************************************************************

function wfu_encode_array_to_string($arr) {
	$arr_str = json_encode($arr);
	$arr_str = wfu_plugin_encode_string($arr_str);
	return $arr_str;
}

function wfu_decode_array_from_string($arr_str) {
	$arr_str = wfu_plugin_decode_string($arr_str);
	$arr = json_decode($arr_str, true);
	return $arr;
}

function wfu_plugin_parse_array($source) {
	$keys = array_keys($source);
	$new_arr = array();
	for ($i = 0; $i < count($keys); $i ++) 
		$new_arr[$keys[$i]] = wp_specialchars_decode($source[$keys[$i]]);
	return $new_arr;
}

function wfu_array_remove_nulls(&$arr) {
	foreach ( $arr as $key => $arri )
		if ( $arri == null )
			array_splice($arr, $key, 1);
}

function wfu_safe_array($arr) {
	return array_map("htmlspecialchars", $arr);
}

function wfu_sanitize($var) {
	$typ = gettype($var);
	if ( $typ == "boolean" || $typ == "integer" || $typ == "double" || $typ == "resource" || $typ == "NULL" )
		return $var;
	elseif ( $typ == "string" )
		return htmlspecialchars($var);
	elseif ( $typ == "array" || $typ == "object" ) {
		foreach ( $var as &$item ) $item = wfu_sanitize($item);
		return $var;
	}
	else
		return $typ;
}

function _wfu_preg_replace_callback_alt($contents, $token) {
	$in_block = false;
	$prev_pos = 0;
	$new_contents = '';
	$ret['items'] = array();
	$ret['tokens'] = array();
	$ii = 0;
	while ( ($pos = strpos($contents, '"', $prev_pos)) !== false ) {
		if ( !$in_block ) {
			$new_contents .= substr($contents, $prev_pos, $pos - $prev_pos + 1);
			$in_block = true;
		}
		else {
			$ret['items'][$ii] = substr($contents, $prev_pos, $pos - $prev_pos);
			$ret['tokens'][$ii] = $token.sprintf('%03d', $ii);
			$new_contents .= $token.sprintf('%03d', $ii).'"';
			$ii ++;
			$in_block = false;
		}
		$prev_pos = $pos + 1;
	}
	if ( $in_block ) {
		$ret['items'][$ii] = substr($contents, $prev_pos);
		$ret['tokens'][$ii] = $token.sprintf('%03d', $ii);
		$new_contents .= $token.sprintf('%03d', $ii).'"';
	}
	else
		$new_contents .= substr($contents, $prev_pos);
	$ret['contents'] = $new_contents;
	return $ret;
}

function wfu_shortcode_string_to_array($shortcode) {
	$i = 0;
	$m1 = array();
	$m2 = array();
	//for some reason preg_replace_callback does not work in all cases, so it has been replaced by a similar custom inline routine
//	$mm = preg_replace_callback('/"([^"]*)"/', function ($matches) use(&$i, &$m1, &$m2) {array_push($m1, $matches[1]); array_push($m2, "attr".$i); return "attr".$i++;}, $shortcode);
	$ret = _wfu_preg_replace_callback_alt($shortcode, "attr");
	$mm = $ret['contents'];
	$m1 = $ret['items'];
	$m2 = $ret['tokens'];
	$arr = explode(" ", $mm);
	$attrs = array();
	foreach ( $arr as $attr ) {
		if ( trim($attr) != "" ) {
			$attr_arr = explode("=", $attr, 2);
			$key = "";
			if ( count($attr_arr) > 0 ) $key = $attr_arr[0];
			$val = "";
			if ( count($attr_arr) > 1 ) $val = $attr_arr[1];
			if ( trim($key) != "" ) $attrs[trim($key)] = str_replace('"', '', $val);
		}
	}
	$attrs2 = str_replace($m2, $m1, $attrs);
	return $attrs2;
}

function wfu_array_sort_function_string_asc($a, $b) {
	return strcmp(strtolower($a), strtolower($b));
}

function wfu_array_sort_function_string_asc_with_id0($a, $b) {
	$cmp = strcmp(strtolower($a["value"]), strtolower($b["value"]));
	if ( $cmp == 0 ) $cmp = ( (int)$a["id0"] < (int)$b["id0"] ? -1 : 1 );
	return $cmp;
}

function wfu_array_sort_function_string_desc($a, $b) {
	return -strcmp(strtolower($a), strtolower($b));
}

function wfu_array_sort_function_string_desc_with_id0($a, $b) {
	$cmp = strcmp(strtolower($a["value"]), strtolower($b["value"]));
	if ( $cmp == 0 ) $cmp = ( (int)$a["id0"] < (int)$b["id0"] ? -1 : 1 );
	return -$cmp;
}

function wfu_array_sort_function_numeric_asc($a, $b) {
	$aa = (double)$a;
	$bb = (double)$b;
	if ( $aa < $bb ) return -1;
	elseif ( $aa > $bb ) return 1;
	else return 0;
}

function wfu_array_sort_function_numeric_asc_with_id0($a, $b) {
	$aa = (double)$a["value"];
	$bb = (double)$b["value"];
	if ( $aa < $bb ) return -1;
	elseif ( $aa > $bb ) return 1;
	elseif ( (int)$a["id0"] < (int)$b["id0"] ) return -1;
	else return 1;
}

function wfu_array_sort_function_numeric_desc($a, $b) {
	$aa = (double)$a;
	$bb = (double)$b;
	if ( $aa > $bb ) return -1;
	elseif ( $aa < $bb ) return 1;
	else return 0;
}

function wfu_array_sort_function_numeric_desc_with_id0($a, $b) {
	$aa = (double)$a["value"];
	$bb = (double)$b["value"];
	if ( $aa > $bb ) return -1;
	elseif ( $aa < $bb ) return 1;
	elseif ( (int)$a["id0"] > (int)$b["id0"] ) return -1;
	else return 1;
}

function wfu_array_sort($array, $on, $order = SORT_ASC, $with_id0 = false) {
    $new_array = array();
    $sortable_array = array();
	
	$pos = strpos($on, ":");
	if ( $pos !== false ) {
		$sorttype = substr($on, $pos + 1);
		if ( $sorttype == "" ) $sorttype = "s";
		$on = substr($on, 0, $pos);
	}
	else $sorttype = "s";

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = ( $with_id0 ? array( "id0" => $v["id0"], "value" => $v2 ) : $v2 );
                    }
                }
            } else {
                $sortable_array[$k] = $v;
				$with_id0 = false;
            }
        }

		uasort($sortable_array, "wfu_array_sort_function_".( $sorttype == "n" ? "numeric" : "string" )."_".( $order == SORT_ASC ? "asc" : "desc" ).( $with_id0 ? "_with_id0" : "" ));

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

function wfu_echo_array($arr) {
	if ( !is_array($arr) ) return;
	echo '<pre>'.print_r($arr, true).'</pre>';
}

function wfu_minify_code($lang, $code) {
	$ret = array( "result" => false, "minified_code" => "" );
	$php_version = preg_replace("/-.*/", "", phpversion());
	$unsupported = false;
	$ret = wfu_compare_versions($php_version, '5.3.0');
	$unsupported = ( $ret['status'] && $ret['result'] == 'lower' );
	if ( !$unsupported ) {
		$path = ABSWPFILEUPLOAD_DIR;
		include_once $path.'vendor/minifier/minify/src/Minify.php';
		include_once $path.'vendor/minifier/minify/src/CSS.php';
		include_once $path.'vendor/minifier/minify/src/JS.php';
		include_once $path.'vendor/minifier/minify/src/Exception.php';
		include_once $path.'vendor/minifier/minify/src/Exceptions/BasicException.php';
		include_once $path.'vendor/minifier/minify/src/Exceptions/FileImportException.php';
		include_once $path.'vendor/minifier/minify/src/Exceptions/IOException.php';
		include_once $path.'vendor/minifier/path-converter/src/ConverterInterface.php';
		include_once $path.'vendor/minifier/path-converter/src/Converter.php';
		$minifier = null;
		eval('$minifier = new MatthiasMullie\Minify\\'.strtoupper($lang).'($code);');
		if ( $minifier !== null ) {
			$ret["result"] = true;
			$ret["minified_code"] = $minifier->minify();
		}
	}
	
	return $ret;
}

function wfu_css_to_HTML($css) {
	if ( WFU_VAR("WFU_MINIFY_INLINE_CSS") == "true" ) {
		$ret = wfu_minify_code("CSS", $css);
		if ( $ret["result"] ) $css = $ret["minified_code"];
	}
	$echo_str = "\n\t".'<style>';
	$echo_str .= "\n".$css;
	$echo_str .= "\n\t".'</style>';

	return $echo_str;
}

function wfu_js_to_HTML($js) {
	if ( WFU_VAR("WFU_MINIFY_INLINE_JS") == "true" ) {
		$ret = wfu_minify_code("JS", $js);
		if ( $ret["result"] ) $js = $ret["minified_code"];
	}
	$echo_str = '<script type="text/javascript">';
	$echo_str .= "\n".$js;
	$echo_str .= "\n".'</script>';

	return $echo_str;
}

function wfu_init_run_js_script() {
	$script = 'if (typeof wfu_js_decode_obj == "undefined") function wfu_js_decode_obj(obj_str) { var obj = null; if (obj_str == "window") obj = window; else { var match = obj_str.match(new RegExp(\'GlobalData(\\\\.(WFU|WFUB)\\\\[(.*?)\\\\](\\\\.(.*))?)?$\')); if (match) { obj = GlobalData; if (match[3]) obj = obj[match[2]][match[3]]; if (match[5]) obj = obj[match[5]]; } } return obj; }';
	$script .= "\n".'if (typeof wfu_run_js == "undefined") function wfu_run_js(obj_str, func) { if (typeof GlobalData == "undefined") { if (typeof window.WFU_JS_BANK == "undefined") WFU_JS_BANK = []; WFU_JS_BANK.push({obj_str: obj_str, func: func}) } else { var obj = wfu_js_decode_obj(obj_str); if (obj) obj[func].call(obj); } }';
	return wfu_js_to_HTML($script);
}

function wfu_PHP_array_to_JS_object($arr) {
	$ret = "";
	foreach ( $arr as $prop => $value ) {
		if ( is_string($value) ) $ret .= ( $ret == "" ? "" : ", " )."$prop: \"$value\"";
		elseif ( is_numeric($value) ) $ret .= ( $ret == "" ? "" : ", " )."$prop: $value";
		elseif ( is_bool($value) ) $ret .= ( $ret == "" ? "" : ", " )."$prop: ".( $value ? "true" : "false" );
	}
	return ( $ret == "" ? "{ }" : "{ $ret }" );
}

//********************* Shortcode Attribute Functions **************************************************************************************

function wfu_insert_category($categories, $before_category, $new_category) {
	if ( $before_category == "" ) $index = count($categories);
	else {
		$index = array_search($before_category, array_keys($categories));
		if ( $index === false ) $index = count($categories);
	}
	
	return array_merge(array_slice($categories, 0, $index), $new_category, array_slice($categories, $index));
}

function wfu_insert_attributes($attributes, $in_category, $in_subcategory, $position, $new_attributes) {
	$index = -1;
	if ( $in_category == "" ) {
		if ( $position == "first" ) $index = 0;
		elseif ( $position == "last" ) $index = count($attributes);
	}
	else {
		foreach ( $attributes as $pos => $attribute ) {
			$match = ( $attribute["category"] == $in_category );
			if ( $in_subcategory != "" ) $match = $match && ( $attribute["subcategory"] == $in_subcategory );
			if ( $match ) {
				if ( $position == "first" ) {
					$index = $pos;
					break;
				}
				elseif ( $position == "last" ) {
					$index = $pos + 1;
				}
			}
		}
	}
	if ( $index > -1 ) array_splice($attributes, $index, 0, $new_attributes);
	
	return $attributes;
}

//********************* Plugin Options Functions *******************************************************************************************

function wfu_get_server_environment() {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$php_env = '';
	if ( PHP_INT_SIZE == 4 ) $php_env = '32bit';
	elseif ( PHP_INT_SIZE == 8 ) $php_env = '64bit';
	else {
		$int = "9223372036854775807";
		$int = intval($int);
		if ($int == 9223372036854775807) $php_env = '64bit';
		elseif ($int == 2147483647) $php_env = '32bit';
	}

	return $php_env;
}

function wfu_ajaxurl() {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));
	return ( $plugin_options['admindomain'] == 'siteurl' || $plugin_options['admindomain'] == '' ? site_url("wp-admin/admin-ajax.php") : ( $plugin_options['admindomain'] == 'adminurl' ? admin_url("admin-ajax.php") : home_url("wp-admin/admin-ajax.php") ) );
}

function WFU_VAR($varname) {
	if ( !isset($GLOBALS["WFU_GLOBALS"][$varname]) ) return false;
	if ( $GLOBALS["WFU_GLOBALS"][$varname][5] ) return $GLOBALS["WFU_GLOBALS"][$varname][3];
	//in case the environment variable is hidden then return the default value
	else return $GLOBALS["WFU_GLOBALS"][$varname][2];
}

function wfu_get_plugin_version() {
	$plugin_data = get_plugin_data(WPFILEUPLOAD_PLUGINFILE);
	return $plugin_data['Version'];
}

function wfu_get_latest_version() {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));
	$postfields = array();
	$postfields['action'] = 'wfuca_check_latest_version_free';
	$postfields['version_hash'] = WFU_VERSION_HASH;
	$url = ( $plugin_options["altserver"] == "1" && trim(WFU_VAR("WFU_ALT_IPTANUS_SERVER")) != "" ? ( trim(WFU_VAR("WFU_ALT_VERSION_SERVER")) != "" ? trim(WFU_VAR("WFU_ALT_VERSION_SERVER")) : trim(WFU_VAR("WFU_ALT_IPTANUS_SERVER")).'/wp-admin/admin-ajax.php' ) : WFU_VERSION_SERVER_URL );
	$result = null;
	if ( WFU_VAR("WFU_DISABLE_VERSION_CHECK") != "true" )
		$result = wfu_post_request($url, $postfields, false, false, 10);
	return $result;
}

function wfu_compare_versions($current, $latest) {
	$ret['status'] = true;
	$ret['custom'] = false;
	$ret['result'] = 'equal';
	$res = preg_match('/^([0-9]*)\.([0-9]*)\.([0-9]*)(.*)/', $current, $cur_data);
	if ( !$res || count($cur_data) < 5 )
		return array( 'status' => false, 'custom' => false, 'result' => 'current version invalid' );
	if ( $cur_data[1] == '' || $cur_data[2] == '' || $cur_data[3] == '' )
		return array( 'status' => false, 'custom' => false, 'result' => 'current version invalid' );
	$custom = ( $cur_data[4] != '' );
	$res = preg_match('/^([0-9]*)\.([0-9]*)\.([0-9]*)/', $latest, $lat_data);
	if ( !$res || count($lat_data) < 4 )
		return array( 'status' => false, 'custom' => $custom, 'result' => 'latest version invalid' );
	if ( $lat_data[1] == '' || $lat_data[2] == '' || $lat_data[3] == '' )
		return array( 'status' => false, 'custom' => $custom, 'result' => 'latest version invalid' );
	if ( intval($cur_data[1]) < intval($lat_data[1]) )
		return array( 'status' => true, 'custom' => $custom, 'result' => 'lower' );
	elseif ( intval($cur_data[1]) > intval($lat_data[1]) )
		return array( 'status' => false, 'custom' => $custom, 'result' => 'current version invalid' );
	if ( intval($cur_data[2]) < intval($lat_data[2]) )
		return array( 'status' => true, 'custom' => $custom, 'result' => 'lower' );
	elseif ( intval($cur_data[2]) > intval($lat_data[2]) )
		return array( 'status' => false, 'custom' => $custom, 'result' => 'current version invalid' );
	if ( intval($cur_data[3]) < intval($lat_data[3]) )
		return array( 'status' => true, 'custom' => $custom, 'result' => 'lower' );
	elseif ( intval($cur_data[3]) > intval($lat_data[3]) )
		return array( 'status' => false, 'custom' => $custom, 'result' => 'current version invalid' );
	return array( 'status' => true, 'custom' => $custom, 'result' => 'equal' );	
}

//********************* File / Directory Functions ************************************************************************************************

function wfu_abspath() {
	$path = WP_CONTENT_DIR;
	//remove trailing slash if exists
	if ( substr($path, -1) == '/' ) $path = substr($path, 0, -1);
	$pos = strrpos($path, '/');
	//to find abspath we go one dir up from content path
	if ( $pos !== false ) $path = substr($path, 0, $pos + 1);
	//else if we cannot go up we stay at content path adding a trailing slash
	else $path .= '/';
	
	return $path;
}

function wfu_fileext($basename, $with_dot = false) {
	if ( $with_dot ) return preg_replace("/^.*?(\.[^.]*)?$/", "$1", $basename);
	else return preg_replace("/^.*?(\.([^.]*))?$/", "$2", $basename);
}

function wfu_filename($basename) {
	return preg_replace("/^(.*?)(\.[^.]*)?$/", "$1", $basename);
}

function wfu_basename($path) {
	if ( !$path || $path == "" ) return "";
	return preg_replace('/.*(\\\\|\\/)/', '', $path);
}

function wfu_basedir($path) {
	if ( !$path || $path == "" ) return "";
	return substr($path, 0, strlen($path) - strlen(wfu_basename($path)));
}

function wfu_path_abs2rel($path) {
	$abspath_notrailing_slash = substr(wfu_abspath(), 0, -1);
//	return ( substr($path, 0, 6) == 'ftp://' || substr($path, 0, 7) == 'ftps://' || substr($path, 0, 7) == 'sftp://' ? $path : str_replace($abspath_notrailing_slash, "", $path) );
	return ( substr($path, 0, 6) == 'ftp://' || substr($path, 0, 7) == 'ftps://' || substr($path, 0, 7) == 'sftp://' ? $path : substr($path, strlen($abspath_notrailing_slash)) );
}

function wfu_path_rel2abs($path) {
	if ( substr($path, 0, 1) == "/" ) $path = substr($path, 1);
	return ( substr($path, 0, 6) == 'ftp://' || substr($path, 0, 7) == 'ftps://' || substr($path, 0, 7) == 'sftp://' ? $path : wfu_abspath().$path );
}

function wfu_delete_file_execute($filepath, $userid) {
	$filedata = wfu_get_filedata($filepath);
	$retid = wfu_log_action('delete', $filepath, $userid, '', 0, 0, '', null);
	$result = unlink($filepath);
	if ( !$result ) wfu_revert_log_action($retid);
	else {
		//delete linked attachment if exists and it is allowed to be deleted
		if ( $filedata != null && isset($filedata["media"]) && WFU_VAR("WFU_UPDATE_MEDIA_ON_DELETE") == "true" )
			wp_delete_attachment( $filedata["media"]["attach_id"] );
	}
	
	return $result;
}

function wfu_upload_plugin_full_path( $params ) {
	$path = $params["uploadpath"];
	if ( $params["accessmethod"] == 'ftp' && $params["ftpinfo"] != '' && $params["useftpdomain"] == "true" ) {
		$ftpdata_flat =  str_replace(array('\:', '\@'), array('\_', '\_'), $params["ftpinfo"]);
		//remove parent folder symbol (..) in path so that the path does not go outside host
		$ftpdata_flat =  str_replace('..', '', $ftpdata_flat);
		$pos1 = strpos($ftpdata_flat, ":");
		$pos2 = strpos($ftpdata_flat, "@");
		if ( $pos1 && $pos2 && $pos2 > $pos1 ) {
			$ftp_username = str_replace(array('\:', '\@'), array(':', '@'), substr($params["ftpinfo"], 0, $pos1));
			$ftp_password = str_replace(array('\:', '\@'), array(':', '@'), substr($params["ftpinfo"], $pos1 + 1, $pos2 - $pos1 - 1));
			$ftp_host = substr($params["ftpinfo"], $pos2 + 1);
			$ftp_port = preg_replace("/^[^:]*:?/", "", $ftp_host);
			$ftp_host_clean = preg_replace("/:.*/", "", $ftp_host);
			$is_sftp = false;
			if ( substr($ftp_port, 0, 1) == "s" ) {
				$is_sftp = true;
				$ftp_port = substr($ftp_port, 1);
				if ( $ftp_port == "" ) $ftp_port = "22";
			}
			if ( $ftp_port != "" ) $ftp_host = $ftp_host_clean.":".$ftp_port;
			$ftp_username = str_replace('@', '%40', $ftp_username);   //if username contains @ character then convert it to %40
			$ftp_password = str_replace('@', '%40', $ftp_password);   //if password contains @ character then convert it to %40
			$start_folder = ( $is_sftp ? 's' : '' ).'ftp://'.$ftp_username.':'.$ftp_password."@".$ftp_host.'/';
		}
		else $start_folder = 'ftp://'.$params["ftpinfo"].'/';
	}
	else $start_folder = WP_CONTENT_DIR.'/';
	if ($path) {
		if ( $path == ".." || substr($path, 0, 3) == "../" ) {
			$start_folder = wfu_abspath();
			$path = substr($path, 2, strlen($path) - 2);
		}
		//remove additional parent folder symbols (..) in path so that the path does not go outside the $start_folder
		$path =  str_replace('..', '', $path);
		if ( substr($path, 0, 1) == "/" ) $path = substr($path, 1, strlen($path) - 1);
		if ( substr($path, -1, 1) == "/" ) $path = substr($path, 0, strlen($path) - 1);
		$full_upload_path = $start_folder;
		if ( $path != "" ) $full_upload_path .= $path.'/';
	}
	else {
		$full_upload_path = $start_folder;
	}
	return $full_upload_path;
}

function wfu_upload_plugin_directory( $path ) {
	$dirparts = explode("/", $path);
	return $dirparts[count($dirparts) - 1];
}

//function to extract sort information from path, which is stored as [[-sort]] inside the path
function wfu_extract_sortdata_from_path($path) {
	$pos1 = strpos($path, '[[');
	$pos2 = strpos($path, ']]');
	$ret['path'] = $path;
	$ret['sort'] = "";
	if ( $pos1 !== false && $pos2 !== false )
		if ( $pos2 > $pos1 ) {
			$ret['sort'] = substr($path, $pos1 + 2, $pos2 - $pos1 - 2);
			$ret['path'] = str_replace('[['.$ret['sort'].']]', '', $path);
		}
	return $ret;
}

//extract sort information from path and return the flatten path
function wfu_flatten_path($path) {
	$ret = wfu_extract_sortdata_from_path($path);
	return $ret['path'];
}

function wfu_delTree($dir) {
	$files = array_diff(scandir($dir), array('.','..'));
	foreach ($files as $file) {
		is_dir("$dir/$file") ? wfu_delTree("$dir/$file") : unlink("$dir/$file");
	}
	return rmdir($dir);
}

function wfu_getTree($dir) {
	$tree = array();
	$files = @scandir($dir);
	if ( !is_array($files) ) $files = array();
	$files = array_diff($files, array('.','..'));
	foreach ($files as $file) {
		if ( is_dir("$dir/$file") ) array_push($tree, $file);
	}
	return $tree;
}
function wfu_parse_folderlist($subfoldertree) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$ret['path'] = array();
	$ret['label'] = array();
	$ret['level'] = array();
	$ret['default'] = array();

	if ( substr($subfoldertree, 0, 4) == "auto" ) return $ret;
	$subfolders = explode(",", $subfoldertree);
	if ( count($subfolders) == 0 ) return $ret;
	if ( count($subfolders) == 1 && trim($subfolders[0]) == "" ) return $ret;
	$dir_levels = array ( "root" );
	$prev_level = 0;
	$level0_count = 0;
	$default = -1;
	foreach ($subfolders as $subfolder) {
		$subfolder = trim($subfolder);			
		$star_count = 0;
		$start_spaces = "";
		$is_default = false;
		//check for folder level
		while ( $star_count < strlen($subfolder) ) {
			if ( substr($subfolder, $star_count, 1) == "*" ) {
				$star_count ++;
				$start_spaces .= "&nbsp;&nbsp;&nbsp;";
			}
			else break;
		}
		if ( $star_count - $prev_level <= 1 && ( $star_count > 0 || $level0_count == 0 ) ) {
			$subfolder = substr($subfolder, $star_count, strlen($subfolder) - $star_count);
			// check for default value
			if ( substr($subfolder, 0, 1) == '&' ) {
				$subfolder = substr($subfolder, 1);
				$is_default = true;
			}
			//split item in folder path and folder name
			$subfolder_items = explode('/', $subfolder);
			if ( count($subfolder_items) > 1 && $subfolder_items[1] != "" ) {
				$subfolder_dir = $subfolder_items[0];
				$subfolder_label = $subfolder_items[1];
			}
			else {
				$subfolder_dir = $subfolder;
				$subfolder_label = $subfolder;
			}
			if ( $subfolder_dir != "" ) {
				// set is_default flag to true only for the first default item
				if ( $is_default && $default == -1 ) $default = count($ret['path']);
				else $is_default = false;
				// set flag that root folder has been included (so that it is not included it again)
				if ( $star_count == 0 ) $level0_count = 1;
				if ( count($dir_levels) > $star_count ) $dir_levels[$star_count] = $subfolder_dir;
				else array_push($dir_levels, $subfolder_dir);
				$subfolder_path = "";
				for ( $i_count = 1; $i_count <= $star_count; $i_count++) {
					$subfolder_path .= $dir_levels[$i_count].'/';
				}
				array_push($ret['path'], $subfolder_path);
				array_push($ret['label'], $subfolder_label);
				array_push($ret['level'], $star_count);
				array_push($ret['default'], $is_default);
				$prev_level = $star_count;
			}
		}
	}

	return $ret;
}

function wfu_filesize($filepath) {
	$fp = fopen($filepath, 'r');
	$pos = 0;
	if ($fp) {
		$size = 1073741824;
		fseek($fp, 0, SEEK_SET);
		while ($size > 1) {
			fseek($fp, $size, SEEK_CUR);
			if (fgetc($fp) === false) {
				fseek($fp, -$size, SEEK_CUR);
				$size = (int)($size / 2);
			}
			else {
				fseek($fp, -1, SEEK_CUR);
				$pos += $size;
			}
		}
		while (fgetc($fp) !== false)  $pos++;
		fclose($fp);
	}

    return $pos;
}

function wfu_filesize2($filepath) {
    $fp = fopen($filepath, 'r');
    $return = false;
    if (is_resource($fp)) {
      if (PHP_INT_SIZE < 8) {
        // 32bit
        if (0 === fseek($fp, 0, SEEK_END)) {
          $return = 0.0;
          $step = 0x7FFFFFFF;
          while ($step > 0) {
            if (0 === fseek($fp, - $step, SEEK_CUR)) {
              $return += floatval($step);
            } else {
              $step >>= 1;
            }
          }
        }
      } elseif (0 === fseek($fp, 0, SEEK_END)) {
        // 64bit
        $return = ftell($fp);
      }
      fclose($fp);
    }
    return $return;
}

function wfu_fseek($fp, $pos, $first = 1) {
	// set to 0 pos initially, one-time
	if ( $first ) fseek($fp, 0, SEEK_SET);

	// get pos float value
	$pos = floatval($pos);

	// within limits, use normal fseek
	if ( $pos <= PHP_INT_MAX )
		fseek($fp, $pos, SEEK_CUR);
	// out of limits, use recursive fseek
	else {
		fseek($fp, PHP_INT_MAX, SEEK_CUR);
		$pos -= PHP_INT_MAX;
		wfu_fseek($fp, $pos, 0);
	}
}

function wfu_fseek2($fp, $pos) {
	$pos = floatval($pos);
	if ( $pos <= PHP_INT_MAX ) {
		return fseek($fp, $pos, SEEK_SET);
	}
	else {
		$fsize = wfu_filesize2($filepath);
		$opp = $fsize - $pos;
		if ( 0 === ($ans = fseek($fp, 0, SEEK_END)) ) {
			$maxstep = 0x7FFFFFFF;
			$step = $opp;
			if ( $step > $maxstep ) $step = $maxstep;
			while ($step > 0) {
				if ( 0 === ($ans = fseek($fp, - $step, SEEK_CUR)) ) {
					$opp -= floatval($step);
				}
				else {
					$maxstep >>= 1;
				}
				$step = $opp;
				if ( $step > $maxstep ) $step = $maxstep;
			}
		}
	}
	return $ans;
}

function wfu_debug_log($message) {
	$logpath = WP_CONTENT_DIR.'/debug_log.txt';
	file_put_contents($logpath, $message, FILE_APPEND);
}

function wfu_safe_store_filepath($path) {
	$code = wfu_create_random_string(16);
	$_SESSION['wfu_filepath_safe_storage'][$code] = $path;
	return $code;
}

function wfu_get_filepath_from_safe($code) {
	//sanitize $code
	$code = wfu_sanitize_code($code);
	if ( $code == "" ) return false;
	//return filepath from session variable, if exists
	if ( !isset($_SESSION['wfu_filepath_safe_storage'][$code]) ) return false;
	return $_SESSION['wfu_filepath_safe_storage'][$code];
}

function wfu_file_extension_restricted($filename) {
	return ( 
		substr($filename, -4) == ".php" ||
		substr($filename, -3) == ".js" ||
		substr($filename, -4) == ".pht" ||
		substr($filename, -5) == ".php3" ||
		substr($filename, -5) == ".php4" ||
		substr($filename, -5) == ".php5" ||
		substr($filename, -6) == ".phtml" ||
		substr($filename, -4) == ".htm" ||
		substr($filename, -5) == ".html" ||
		substr($filename, -9) == ".htaccess" ||
		strpos($filename, ".php.") !== false ||
		strpos($filename, ".js.") !== false ||
		strpos($filename, ".pht.") !== false ||
		strpos($filename, ".php3.") !== false ||
		strpos($filename, ".php4.") !== false ||
		strpos($filename, ".php5.") !== false ||
		strpos($filename, ".phtml.") !== false ||
		strpos($filename, ".htm.") !== false ||
		strpos($filename, ".html.") !== false ||
		strpos($filename, ".htaccess.") !== false
	);
}

function wfu_human_time($time) {
	$time = (int)$time;
	$days = (int)($time/86400);
	$time -= $days * 86400;
	$hours = (int)($time/3600);
	$time -= $hours * 3600;
	$minutes = (int)($time/60);
	$secs = $time - $minutes * 60;
	$human_time = ( $days > 0 ? $days."d" : "" ).( $hours > 0 ? $hours."h" : "" ).( $minutes > 0 ? $minutes."m" : "" ).( $secs > 0 ? $secs."s" : "" );
	if ( $human_time == "" ) $human_time == "0s";
	return $human_time;
}

function wfu_human_filesize($size, $unit = "") {
	if ( ( !$unit && $size >= 1<<30 ) || $unit == "GB" )
		return number_format($size / (1<<30), 2)."GB";
	if( ( !$unit && $size >= 1<<20 ) || $unit == "MB" )
		return number_format($size / (1<<20), 2)."MB";
	if( ( !$unit && $size >= 1<<10 ) || $unit == "KB" )
		return number_format($size / (1<<10), 2)."KB";
	return number_format($size)." bytes";
}

function wfu_file_exists($path) {
	if ( file_exists($path) ) return true;
	
	return false;
}

//********************* User Functions *****************************************************************************************************

function wfu_get_user_role($user, $param_roles) {
	$result_role = 'nomatch';
	if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
		/* Go through the array of the roles of the current user */
		foreach ( $user->roles as $user_role ) {
			$user_role = strtolower($user_role);
			/* if this role matches to the roles in $param_roles or it is administrator or $param_roles allow all roles then it is approved */
			if ( in_array($user_role, $param_roles) || $user_role == 'administrator' || in_array('all', $param_roles) ) {
				/*  We approve this role of the user and exit */
				$result_role = $user_role;
				break;
			}
		}
	}
	/*  if the user has no roles (guest) and guests are allowed, then it is approved */
	elseif ( in_array('guests', $param_roles) ) {
		$result_role = 'guest';
	}
	return $result_role;		
}

function wfu_get_user_valid_role_names($user) {
	global $wp_roles;
	
	$result_roles = array();
	if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
		/* get all valid roles */
		$roles = $wp_roles->get_names();
		/* Go through the array of the roles of the current user */
		foreach ( $user->roles as $user_role ) {
			$user_role = strtolower($user_role);
			/* If one role of the current user matches to the roles allowed to upload */
			if ( in_array($user_role, array_keys($roles)) ) array_push($result_roles, $user_role);
		}
	}

	return $result_roles;		
}

//*********************** DB Functions *****************************************************************************************************

//log action to database
function wfu_log_action($action, $filepath, $userid, $uploadid, $pageid, $blogid, $sid, $userdata) {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "wfu_log";
	$table_name2 = $wpdb->prefix . "wfu_userdata";
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));

	if ( !file_exists($filepath) && $action != 'datasubmit' && substr($action, 0, 5) != 'other' ) return;
	$parts = pathinfo($filepath);
	$relativepath = wfu_path_abs2rel($filepath);
//	if ( substr($relativepath, 0, 1) != '/' ) $relativepath = '/'.$relativepath;
	
	$retid = 0;
	if ( $action == 'upload' || $action == 'include' || $action == 'datasubmit' ) {
		if ( $action == 'upload' || $action == 'include' ) {
			// calculate and store file hash if this setting is enabled from Settings
			$filehash = '';
			if ( $plugin_options['hashfiles'] == '1' ) $filehash = md5_file($filepath);
			// calculate file size
			$filesize = filesize($filepath);
			// first make obsolete records having the same file path because the old file has been replaced
			$oldrecs = $wpdb->get_results('SELECT * FROM '.$table_name1.' WHERE filepath = \''.$relativepath.'\' AND date_to = 0');
			if ( $oldrecs ) {
				foreach ( $oldrecs as $oldrec ) wfu_make_rec_obsolete($oldrec);
			}
		}
		// attempt to create new log record
		$now_date = date('Y-m-d H:i:s');
		if ( $wpdb->insert($table_name1,
			array(
				'userid' 	=> $userid,
				'uploaduserid' 	=> $userid,
				'uploadtime' 	=> time(),
				'sessionid' => session_id(),
				'filepath' 	=> ( $action == 'datasubmit' ? '' : $relativepath ),
				'filehash' 	=> ( $action == 'datasubmit' ? '' : $filehash ),
				'filesize' 	=> ( $action == 'datasubmit' ? 0 : $filesize ),
				'uploadid' 	=> $uploadid,
				'pageid' 	=> $pageid,
				'blogid' 	=> $blogid,
				'sid' 		=> $sid,
				'date_from' 	=> $now_date,
				'date_to' 	=> 0,
				'action' 	=> $action
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s' )) !== false ) {
			$retid = $wpdb->insert_id;
			// if new log record has been created, also create user data records
			if ( $userdata != null && $uploadid != '' ) {
				foreach ( $userdata as $userdata_key => $userdata_field ) {
					$existing = $wpdb->get_row('SELECT * FROM '.$table_name2.' WHERE uploadid = \''.$uploadid.'\' AND property = \''.$userdata_field['label'].'\' AND date_to = 0');
					if ($existing == null)
						$wpdb->insert($table_name2,
							array(
								'uploadid' 	=> $uploadid,
								'property' 	=> $userdata_field['label'],
								'propkey' 	=> $userdata_key,
								'propvalue' 	=> $userdata_field['value'],
								'date_from' 	=> $now_date,
								'date_to' 	=> 0
							),
							array( '%s', '%s', '%d', '%s', '%s', '%s' ));
				}
			}
		}
	}
	//for rename action the $action variable is of the form: $action = 'rename:'.$newfilepath; in order to pass the new file path
	elseif ( substr($action, 0, 6) == 'rename' ) {
		//get new filepath
		$newfilepath = substr($action, 7);
		$relativepath = wfu_path_abs2rel($newfilepath);
//		if ( substr($relativepath, 0, 1) != '/' ) $relativepath = '/'.$relativepath;
		//get stored file data from database without user data
		$filerec = wfu_get_file_rec($filepath, false);
		//log action only if there are previous stored file data
		if ( $filerec != null ) {
			$now_date = date('Y-m-d H:i:s');
			//make previous record obsolete
			$wpdb->update($table_name1,
				array( 'date_to' => $now_date ),
				array( 'idlog' => $filerec->idlog ),
				array( '%s' ),
				array( '%d' )
			);
			//insert new rename record
			if ( $wpdb->insert($table_name1,
				array(
					'userid' 	=> $userid,
					'uploaduserid' 	=> $filerec->uploaduserid,
					'uploadtime' 	=> $filerec->uploadtime,
					'sessionid' => $filerec->sessionid,
					'filepath' 	=> $relativepath,
					'filehash' 	=> $filerec->filehash,
					'filesize' 	=> $filerec->filesize,
					'uploadid' 	=> $filerec->uploadid,
					'pageid' 	=> $filerec->pageid,
					'blogid' 	=> $filerec->blogid,
					'sid' 		=> $filerec->sid,
					'date_from' 	=> $now_date,
					'date_to' 	=> 0,
					'action' 	=> 'rename',
					'linkedto' 	=> $filerec->idlog,
					'filedata' 	=> $filerec->filedata
				),
				array( '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s' ) ) !== false )
				$retid = $wpdb->insert_id;
		}
	}
	elseif ( $action == 'delete' ) {
		//get stored file data from database without user data
		$filerec = wfu_get_file_rec($filepath, false);
		//log action only if there are previous stored file data
		if ( $filerec != null ) {
			$now_date = date('Y-m-d H:i:s');
			//make previous record obsolete
			$wpdb->update($table_name1,
				array( 'date_to' => $now_date ),
				array( 'idlog' => $filerec->idlog ),
				array( '%s' ),
				array( '%d' )
			);
			//insert new delete record
			if ( $wpdb->insert($table_name1,
				array(
					'userid' 	=> $userid,
					'uploaduserid' 	=> $filerec->uploaduserid,
					'uploadtime' 	=> $filerec->uploadtime,
					'sessionid' => $filerec->sessionid,
					'filepath' 	=> $filerec->filepath,
					'filehash' 	=> $filerec->filehash,
					'filesize' 	=> $filerec->filesize,
					'uploadid' 	=> $filerec->uploadid,
					'pageid' 	=> $filerec->pageid,
					'blogid' 	=> $filerec->blogid,
					'sid' 		=> $filerec->sid,
					'date_from' 	=> $now_date,
					'date_to' 	=> $now_date,
					'action' 	=> 'delete',
					'linkedto' 	=> $filerec->idlog,
					'filedata' 	=> $filerec->filedata
				),
				array( '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s' )) != false )
				$retid = $wpdb->insert_id;
		}
	}
	elseif ( $action == 'download' ) {
		//get stored file data from database without user data
		$filerec = wfu_get_file_rec($filepath, false);
		//log action only if there are previous stored file data
		if ( $filerec != null ) {
			$now_date = date('Y-m-d H:i:s');
			//make previous record obsolete
			$wpdb->update($table_name1,
				array( 'date_to' => $now_date ),
				array( 'idlog' => $filerec->idlog ),
				array( '%s' ),
				array( '%d' )
			);
			//insert new download record
			if ( $wpdb->insert($table_name1,
				array(
					'userid' 	=> $userid,
					'uploaduserid' 	=> $filerec->uploaduserid,
					'uploadtime' 	=> $filerec->uploadtime,
					'sessionid' => $filerec->sessionid,
					'filepath' 	=> $filerec->filepath,
					'filehash' 	=> $filerec->filehash,
					'filesize' 	=> $filerec->filesize,
					'uploadid' 	=> $filerec->uploadid,
					'pageid' 	=> $filerec->pageid,
					'blogid' 	=> $filerec->blogid,
					'sid' 		=> $filerec->sid,
					'date_from' 	=> $now_date,
					'date_to' 	=> 0,
					'action' 	=> 'download',
					'linkedto' 	=> $filerec->idlog,
					'filedata' 	=> $filerec->filedata
				),
				array( '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s' )) != false )
				$retid = $wpdb->insert_id;
		}
	}
	//for modify action the $action variable is of the form: $action = 'modify:'.$now_date; in order to pass the exact modify date
	elseif ( substr($action, 0, 6) == 'modify' ) {
		$now_date = substr($action, 7);
		//get stored file data from database without user data
		$filerec = wfu_get_file_rec($filepath, false);
		//log action only if there are previous stored file data
		if ( $filerec != null ) {
			//make previous record obsolete
			$wpdb->update($table_name1,
				array( 'date_to' => $now_date ),
				array( 'idlog' => $filerec->idlog ),
				array( '%s' ),
				array( '%d' )
			);
			//insert new modify record
			if ( $wpdb->insert($table_name1,
				array(
					'userid' 	=> $userid,
					'uploaduserid' 	=> $filerec->uploaduserid,
					'uploadtime' 	=> $filerec->uploadtime,
					'sessionid' => $filerec->sessionid,
					'filepath' 	=> $filerec->filepath,
					'filehash' 	=> $filerec->filehash,
					'filesize' 	=> $filerec->filesize,
					'uploadid' 	=> $filerec->uploadid,
					'pageid' 	=> $filerec->pageid,
					'blogid' 	=> $filerec->blogid,
					'sid' 		=> $filerec->sid,
					'date_from' 	=> $now_date,
					'date_to' 	=> 0,
					'action' 	=> 'modify',
					'linkedto' 	=> $filerec->idlog,
					'filedata' 	=> $filerec->filedata
				),
				array( '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s' )) != false )
				$retid = $wpdb->insert_id;
		}
	}
	elseif ( substr($action, 0, 10) == 'changeuser' ) {
		$new_user = substr($action, 11);
		//get stored file data from database without user data
		$filerec = wfu_get_file_rec($filepath, false);
		//log action only if there are previous stored file data
		if ( $filerec != null ) {
			$now_date = date('Y-m-d H:i:s');
			//make previous record obsolete
			$wpdb->update($table_name1,
				array( 'date_to' => $now_date ),
				array( 'idlog' => $filerec->idlog ),
				array( '%s' ),
				array( '%d' )
			);
			//insert new modify record
			if ( $wpdb->insert($table_name1,
				array(
					'userid' 	=> $userid,
					'uploaduserid' 	=> $new_user,
					'uploadtime' 	=> $filerec->uploadtime,
					'sessionid' => $filerec->sessionid,
					'filepath' 	=> $filerec->filepath,
					'filehash' 	=> $filerec->filehash,
					'filesize' 	=> $filerec->filesize,
					'uploadid' 	=> $filerec->uploadid,
					'pageid' 	=> $filerec->pageid,
					'blogid' 	=> $filerec->blogid,
					'sid' 		=> $filerec->sid,
					'date_from' 	=> $now_date,
					'date_to' 	=> 0,
					'action' 	=> 'changeuser',
					'linkedto' 	=> $filerec->idlog,
					'filedata' 	=> $filerec->filedata
				),
				array( '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s' )) != false )
				$retid = $wpdb->insert_id;
		}
	}
	elseif ( substr($action, 0, 5) == 'other' ) {
		$info = substr($action, 6);
		$now_date = date('Y-m-d H:i:s');
		//insert new other type record
		if ( $wpdb->insert($table_name1,
			array(
				'userid' 	=> $userid,
				'uploaduserid' 	=> -1,
				'uploadtime' 	=> 0,
				'sessionid'	=> '',
				'filepath' 	=> $info,
				'filehash' 	=> '',
				'filesize' 	=> 0,
				'uploadid' 	=> '',
				'pageid' 	=> 0,
				'blogid' 	=> 0,
				'sid' 		=> '',
				'date_from' 	=> $now_date,
				'date_to' 	=> $now_date,
				'action' 	=> 'other',
				'linkedto' 	=> -1
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%d' )) != false )
			$retid = $wpdb->insert_id;
	}
	return $retid;
}

//revert previously saved action
function wfu_revert_log_action($idlog) {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "wfu_log";

	$filerec = $wpdb->get_row('SELECT * FROM '.$table_name1.' WHERE idlog = '.$idlog);
	if ( $filerec != null ) {
		$prevfilerec = $wpdb->get_row('SELECT * FROM '.$table_name1.' WHERE idlog = '.$filerec->idlog);
		if ( $prevfilerec != null ) {
			$wpdb->update($table_name1,
				array( 'date_to' => date('Y-m-d H:i:s') ),
				array( 'idlog' => $filerec->idlog ),
				array( '%s' ),
				array( '%d' )
			);
			$wpdb->update($table_name1,
				array( 'date_to' => 0 ),
				array( 'idlog' => $prevfilerec->idlog ),
				array( '%s' ),
				array( '%d' )
			);
		}
	}
}

//find user by its id and return a non-empty username
function wfu_get_username_by_id($id) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$user = get_user_by('id', $id);
	if ( $user == false && $id > 0 ) $username = 'unknown';
	elseif ( $user == false && $id == -999 ) $username = 'system';
	elseif ( $user == false ) $username = 'guest';
	else $username = $user->user_login;
	return $username;
}

//get the most current database record for file $filepath and also include any userdata if $include_userdata is true
function wfu_get_file_rec($filepath, $include_userdata) {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "wfu_log";
	$table_name2 = $wpdb->prefix . "wfu_userdata";
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));

	if ( !file_exists($filepath) ) return null;

	$relativepath = wfu_path_abs2rel($filepath);
//	if ( substr($relativepath, 0, 1) != '/' ) $relativepath = '/'.$relativepath;
	//if file hash is enabled, then search file based on its path and hash, otherwise find file based on its path and size
	if ( isset($plugin_options['hashfiles']) && $plugin_options['hashfiles'] == '1' ) {
		$filehash = md5_file($filepath);
		$filerec = $wpdb->get_row('SELECT * FROM '.$table_name1.' WHERE filepath = \''.$relativepath.'\' AND filehash = \''.$filehash.'\' AND date_to = 0 ORDER BY date_from DESC');
	}
	else {
		$stat = stat($filepath);
		$filerec = $wpdb->get_row('SELECT * FROM '.$table_name1.' WHERE filepath = \''.$relativepath.'\' AND filesize = '.$stat['size'].' AND date_to = 0 ORDER BY date_from DESC');
	}
	//get user data
	if ( $filerec != null && $include_userdata ) {
		$filerec->userdata = null;
		if ( $filerec->uploadid != '' ) {
			$filerec->userdata = $wpdb->get_results('SELECT * FROM '.$table_name2.' WHERE uploadid = \''.$filerec->uploadid.'\' AND date_to = 0 ORDER BY propkey');
		}
	}
	return $filerec;
}

//get database record for id
function wfu_get_file_rec_from_id($idlog) {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "wfu_log";
	$filerec = $wpdb->get_row('SELECT * FROM '.$table_name1.' WHERE idlog = '.$idlog);

	return $filerec;
}

function wfu_get_latest_rec_from_id($idlog) {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "wfu_log";
	$filerec = $wpdb->get_row('SELECT * FROM '.$table_name1.' WHERE idlog = '.$idlog);
	while ( $filerec != null && $filerec->date_to != "0000-00-00 00:00:00" )
		$filerec = $wpdb->get_row('SELECT * FROM '.$table_name1.' WHERE linkedto = '.$filerec->idlog);
	
	return $filerec;
}

/**
 *  gets the filedata property from file record in database
 *  
 *  This function returns the filedata property of the corresponding record of
 *  the file in the database holding data about its transfer to a service
 *  account like Dropbox, provided that this record is still valid. If the
 *  record does not exist or exists but it is absolete, then the function
 *  returns null, otherwise it returns an array.
 *  
 *  The [$service]["filepath"] item of the array is set to the final $filepath
 *  of the file, in case that the original filename was renamed.
 *  
 *  @param int $idlog file id of the file
 *  @param bool $is_new it is true if the function is called during addition of
 *         a new file
 *  @return mixed
 */
function wfu_get_latest_filedata_from_id($idlog, $is_new = false) {
	//get latest database record of file, if it is still valid
	$filerec = wfu_get_latest_rec_from_id($idlog);
	//return null if the record does not exist or it is obsolete
	if ( $filerec == null ) return null;

	return wfu_get_filedata_from_rec($filerec, $is_new, true, false);
}

function wfu_get_filedata($filepath, $include_general_data = false) {
	$filerec = wfu_get_file_rec($filepath, false);
	if ( $filerec == null ) return null;

	return wfu_get_filedata_from_rec($filerec, true, false, $include_general_data);
}

function wfu_get_filedata_from_rec($filerec, $is_new = false, $update_transfer = false, $include_general_data = false) {
	//return filedata, if it does not exist and we do not want to create a new
	//filedata structure return null, otherwise return an empty array
	if ( !isset($filerec->filedata) || is_null($filerec->filedata) ) $filedata = ( $is_new ? array() : null );
	else {
		$filedata = wfu_decode_array_from_string($filerec->filedata);
		if ( !is_array($filedata) ) $filedata = ( $is_new ? array() : null );
	}
	if ( !is_null($filedata) ) {
		//update filepath property in filedata of "transfer" type, if service
		//records exist
		if ( $update_transfer ) {
			foreach ( $filedata as $key => $data )
				if ( !isset($data["type"]) || $data["type"] == "transfer" )
					$filedata[$key]["filepath"] = $filerec->filepath;
		}
		//add idlog in filedata if $include_general_data is true
		if ( $include_general_data )
			$filedata["general"] = array(
				"type"	=> "data",
				"idlog"	=> $filerec->idlog
			);
	}
	
	return $filedata;
}

function wfu_save_filedata_from_id($idlog, $filedata) {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "wfu_log";
	return $wpdb->update($table_name1, array( 'filedata' => wfu_encode_array_to_string($filedata) ), array( 'idlog' => $idlog ), array( '%s' ), array( '%d' ));
}

//get userdata from uploadid
function wfu_get_userdata_from_uploadid($uploadid) {
	global $wpdb;
	$table_name2 = $wpdb->prefix . "wfu_userdata";
	$userdata = $wpdb->get_results('SELECT * FROM '.$table_name2.' WHERE uploadid = \''.$uploadid.'\' AND date_to = 0 ORDER BY propkey');

	return $userdata;
}

//reassign file hashes for all valid files in the database
function wfu_reassign_hashes() {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "wfu_log";
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));
	if ( $plugin_options['hashfiles'] == '1' ) {
		$filerecs = $wpdb->get_results('SELECT * FROM '.$table_name1.' WHERE filehash = \'\' AND date_to = 0');
		foreach( $filerecs as $filerec ) {
			//calculate full file path
			$filepath = wfu_path_rel2abs($filerec->filepath);
			if ( file_exists($filepath) ) {
				$filehash = md5_file($filepath);
				$wpdb->update($table_name1,
					array( 'filehash' => $filehash ),
					array( 'idlog' => $filerec->idlog ),
					array( '%s' ),
					array( '%d' )
				);
			}
		}
	}
}

function wfu_make_rec_obsolete($filerec) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	global $wpdb;
	$table_name1 = $wpdb->prefix . "wfu_log";
	$filedata = wfu_get_filedata_from_rec($filerec, true);
	//update db record accordingly
	$wpdb->update($table_name1,
		array( 'date_to' => date('Y-m-d H:i:s'), 'filedata' => wfu_encode_array_to_string($filedata) ),
		array( 'idlog' => $filerec->idlog ),
		array( '%s', '%s' ),
		array( '%d' )
	);
}

//update database to reflect the current status of files
function wfu_sync_database() {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	global $wpdb;
	$table_name1 = $wpdb->prefix . "wfu_log";
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));

	$filerecs = $wpdb->get_results('SELECT * FROM '.$table_name1.' WHERE action <> \'other\' AND action <> \'datasubmit\' AND date_to = 0');
	$obsolete_count = 0;
	foreach( $filerecs as $filerec ) {
		$obsolete = true;
		//calculate full file path
		$filepath = wfu_path_rel2abs($filerec->filepath);
		if ( file_exists($filepath) ) {
			if ( $plugin_options['hashfiles'] == '1' ) {
				$filehash = md5_file($filepath);
				if ( $filehash == $filerec->filehash ) $obsolete = false;
			}
			else {
				$filesize = filesize($filepath);
				if ( $filesize == $filerec->filesize ) $obsolete = false;
			}
		}
		if ( $obsolete ) {
			wfu_make_rec_obsolete($filerec);
			$obsolete_count ++;
		}
	}
	return $obsolete_count;
}

function wfu_get_recs_of_user($userid) {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "wfu_log";
	$table_name2 = $wpdb->prefix . "wfu_userdata";
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));

	//if $userid starts with 'guest' then retrieval of records is done using sessionid and uploaduserid is zero (for guests)
	if ( substr($userid, 0, 5) == 'guest' )
		$filerecs = $wpdb->get_results('SELECT * FROM '.$table_name1.' WHERE action <> \'other\' AND action <> \'datasubmit\' AND uploaduserid = 0 AND sessionid = \''.substr($userid, 5).'\' AND date_to = 0');
	else
		$filerecs = $wpdb->get_results('SELECT * FROM '.$table_name1.' WHERE action <> \'other\' AND action <> \'datasubmit\' AND uploaduserid = '.$userid.' AND date_to = 0');
	$out = array();
	foreach( $filerecs as $filerec ) {
		$obsolete = true;
		//calculate full file path
		$filepath = wfu_path_rel2abs($filerec->filepath);
		if ( file_exists($filepath) ) {
			if ( $plugin_options['hashfiles'] == '1' ) {
				$filehash = md5_file($filepath);
				if ( $filehash == $filerec->filehash ) $obsolete = false;
			}
			else {
				$filesize = filesize($filepath);
				if ( $filesize == $filerec->filesize ) $obsolete = false;
			}
		}
		if ( $obsolete ) {
			wfu_make_rec_obsolete($filerec);
		}
		else {
			$filerec->userdata = null;
			if ( $filerec->uploadid != '' ) 
				$filerec->userdata = $wpdb->get_results('SELECT * FROM '.$table_name2.' WHERE uploadid = \''.$filerec->uploadid.'\' AND date_to = 0 ORDER BY propkey');
			array_push($out, $filerec);
		}
	}
	
	return $out;
}

function wfu_get_filtered_recs($filter) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	global $wpdb;
	$table_name1 = $wpdb->prefix . "wfu_log";
	$table_name2 = $wpdb->prefix . "wfu_userdata";
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));

	$queries = array();
	// add default filters
	array_push($queries, 'action <> \'other\' AND action <> \'datasubmit\'');
	array_push($queries, 'date_to = 0');
	// construct user filter
	if ( isset($filter['user']) ) {
		if ( $filter['user']['all'] ) {
			if ( $filter['user']['guests'] ) $query = 'uploaduserid >= 0';
			else $query = 'uploaduserid > 0';
		}
		elseif ( count($filter['user']['ids']) == 1 && substr($filter['user']['ids'][0], 0, 5) == 'guest' )
			$query = 'uploaduserid = 0 AND sessionid = \''.substr($filter['user']['ids'][0], 5).'\'';
		else {
			if ( $filter['user']['guests'] ) array_push($filter['user']['ids'], '0');
			if ( count($filter['user']['ids']) == 1 ) $query = 'uploaduserid = '.$filter['user']['ids'][0];
			else $query = 'uploaduserid in ('.implode(",",$filter['user']['ids']).')';
		}
		array_push($queries, $query);
	}
	// construct size filter
	if ( isset($filter['size']) ) {
		if ( isset($filter['size']['lower']) && isset($filter['size']['upper']) )
			$query = 'filesize > '.$filter['size']['lower'].' AND filesize < '.$filter['size']['upper'];
		elseif ( isset($filter['size']['lower']) ) $query = 'filesize > '.$filter['size']['lower'];
		else $query = 'filesize < '.$filter['size']['upper'];
		array_push($queries, $query);
	}
	// construct date filter
	if ( isset($filter['date']) ) {
		if ( isset($filter['date']['lower']) && isset($filter['date']['upper']) )
			$query = 'uploadtime > '.$filter['date']['lower'].' AND uploadtime < '.$filter['date']['upper'];
		elseif ( isset($filter['date']['lower']) ) $query = 'uploadtime > '.$filter['date']['lower'];
		else $query = 'uploadtime < '.$filter['date']['upper'];
		array_push($queries, $query);
	}
	// construct file pattern filter
	if ( isset($filter['pattern']) ) {
		$query = 'filepath REGEXP \''.wfu_upload_plugin_wildcard_to_mysqlregexp($filter['pattern']).'\'';
		array_push($queries, $query);
	}
	// construct page/post filter
	if ( isset($filter['post']) ) {
		if ( count($filter['post']['ids']) == 1 ) $query = 'pageid = '.$filter['post']['ids'][0];
			else $query = 'pageid in ('.implode(",",$filter['post']['ids']).')';
		array_push($queries, $query);
	}
	// construct blog filter
	if ( isset($filter['blog']) ) {
		if ( count($filter['blog']['ids']) == 1 ) $query = 'blogid = '.$filter['blog']['ids'][0];
			else $query = 'blogid in ('.implode(",",$filter['blog']['ids']).')';
		array_push($queries, $query);
	}
	// construct userdata filter
	if ( isset($filter['userdata']) ) {
		if ( $filter['userdata']['criterion'] == "equal to" ) $valuecriterion = 'propvalue = \''.$filter['userdata']['value'].'\'';
		elseif ( $filter['userdata']['criterion'] == "starts with" ) $valuecriterion = 'propvalue LIKE \''.$filter['userdata']['value'].'%\'';
		elseif ( $filter['userdata']['criterion'] == "ends with" ) $valuecriterion = 'propvalue LIKE \'%'.$filter['userdata']['value'].'\'';
		elseif ( $filter['userdata']['criterion'] == "contains" ) $valuecriterion = 'propvalue LIKE \'%'.$filter['userdata']['value'].'%\'';
		elseif ( $filter['userdata']['criterion'] == "not equal to" ) $valuecriterion = 'propvalue <> \''.$filter['userdata']['value'].'\'';
		elseif ( $filter['userdata']['criterion'] == "does not start with" ) $valuecriterion = 'propvalue NOT LIKE \''.$filter['userdata']['value'].'%\'';
		elseif ( $filter['userdata']['criterion'] == "does not end with" ) $valuecriterion = 'propvalue NOT LIKE \'%'.$filter['userdata']['value'].'\'';
		elseif ( $filter['userdata']['criterion'] == "does not contain" ) $valuecriterion = 'propvalue NOT LIKE \'%'.$filter['userdata']['value'].'%\'';
		else $valuecriterion = 'propvalue = \''.$filter['userdata']['value'].'\'';
		$query = 'uploadid in (SELECT DISTINCT uploadid FROM '.$table_name2.' WHERE date_to = 0 AND property = \''.$filter['userdata']['field'] .'\' AND '.$valuecriterion.')';
		array_push($queries, $query);
	}
	
	$filerecs = $wpdb->get_results('SELECT * FROM '.$table_name1.' WHERE '.implode(' AND ', $queries));
	$out = array();
	foreach( $filerecs as $filerec ) {
		$obsolete = true;
		//calculate full file path
		$filepath = wfu_path_rel2abs($filerec->filepath);
		if ( file_exists($filepath) ) {
			if ( $plugin_options['hashfiles'] == '1' ) {
				$filehash = md5_file($filepath);
				if ( $filehash == $filerec->filehash ) $obsolete = false;
			}
			else {
				$filesize = filesize($filepath);
				if ( $filesize == $filerec->filesize ) $obsolete = false;
			}
		}
		if ( $obsolete ) {
			wfu_make_rec_obsolete($filerec);
		}
		else {
			$filerec->userdata = null;
			if ( $filerec->uploadid != '' ) 
				$filerec->userdata = $wpdb->get_results('SELECT * FROM '.$table_name2.' WHERE uploadid = \''.$filerec->uploadid.'\' AND date_to = 0 ORDER BY propkey');
			array_push($out, $filerec);
		}
	}
	
	return $out;
}

function wfu_get_uncached_option($option, $default = false) {
	$GLOBALS['wp_object_cache']->delete( 'your_option_name', 'options' );
	return get_option($option, $default);
}

function wfu_get_option($option, $default) {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "options";
	$val = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM $table_name1 WHERE option_name = %s", $option));
	if ( $val === null && $default !== false ) $val = $default;
	elseif ( is_array($default) ) $val = wfu_decode_array_from_string($val);
	return $val;
}

function wfu_update_option($option, $value) {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "options";
	if ( is_array($value) ) $value = wfu_encode_array_to_string($value);
	$wpdb->query($wpdb->prepare("INSERT INTO $table_name1 (option_name, option_value) VALUES (%s, %s) ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)", $option, $value));
}

function wfu_export_uploaded_files($params) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	global $wpdb;
	$table_name1 = $wpdb->prefix . "wfu_log";
	$table_name2 = $wpdb->prefix . "wfu_userdata";
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));
	$sep = WFU_VAR("WFU_EXPORT_DATA_SEPARATOR");
	$sep2 = WFU_VAR("WFU_EXPORT_USERDATA_SEPARATOR");

	$contents = "";
	$header = 'Name'.$sep.'Path'.$sep.'Upload User'.$sep.'Upload Time'.$sep.'Size'.$sep.'Page ID'.$sep.'Blog ID'.$sep.'Shortcode ID'.$sep.'Upload ID'.$sep.'User Data';
	$contents = $header;
	$filerecs = $wpdb->get_results('SELECT * FROM '.$table_name1.' WHERE action <> \'other\' AND date_to = 0');
	foreach( $filerecs as $filerec ) {
		if ( $filerec->action == 'datasubmit' ) $obsolete = false;
		else {
			$obsolete = true;
			//calculate full file path
			$filepath = wfu_path_rel2abs($filerec->filepath);
			if ( file_exists($filepath) ) {
				if ( $plugin_options['hashfiles'] == '1' ) {
					$filehash = md5_file($filepath);
					if ( $filehash == $filerec->filehash ) $obsolete = false;
				}
				else {
					$filesize = filesize($filepath);
					if ( $filesize == $filerec->filesize ) $obsolete = false;
				}
			}
		}
		//export file data if file is not obsolete
		if ( !$obsolete ) {
			$username = wfu_get_username_by_id($filerec->uploaduserid);
			$filerec->userdata = $wpdb->get_results('SELECT * FROM '.$table_name2.' WHERE uploadid = \''.$filerec->uploadid.'\' AND date_to = 0 ORDER BY propkey');
			$line = ( $filerec->action == 'datasubmit' ? 'datasubmit' : wfu_basename($filerec->filepath) );
			$line .= $sep.( $filerec->action == 'datasubmit' ? '' :  wfu_basedir($filerec->filepath) );
			$line .= $sep.$username;
			$line .= $sep.( $filerec->uploadtime == null ? "" : date("Y-m-d H:i:s", $filerec->uploadtime) );
			$line .= $sep.( $filerec->action == 'datasubmit' ? '0' : $filerec->filesize );
			$line .= $sep.( $filerec->pageid == null ? "" : $filerec->pageid );
			$line .= $sep.( $filerec->blogid == null ? "" : $filerec->blogid );
			$line .= $sep.( $filerec->sid == null ? "" : $filerec->sid );
			$line .= $sep.$filerec->uploadid;
			$line2 = "";
			foreach ( $filerec->userdata as $userdata ) {
				if ( $line2 != "" ) $line2 .= $sep2;
				$line2 .= $userdata->property.":".str_replace(array("\n", "\r", "\r\n"), " ", $userdata->propvalue);
			}
			$line .= $sep.$line2;
			$contents .= "\n".$line;
		}
	}
	//create file
	$path = tempnam(sys_get_temp_dir(), 'wfu');
	file_put_contents($path, $contents);
	
	return $path;
}

//********************* Widget Functions ****************************************************************************************

function wfu_get_widget_obj_from_id($widgetid) {
	global $wp_registered_widgets;

	if ( !isset($wp_registered_widgets[$widgetid]) ) return false;
	if ( !isset($wp_registered_widgets[$widgetid]['callback']) ) return false;
	if ( !isset($wp_registered_widgets[$widgetid]['callback'][0]) ) return false;
	$obj = $wp_registered_widgets[$widgetid]['callback'][0];
	if ( !($obj instanceof WP_Widget) ) return false;
	
	return $obj;	
}

//********************* Shortcode Options Functions ****************************************************************************************

function wfu_shortcode_attribute_definitions_adjusted($shortcode_atts) {
	//get attribute definitions
	$defs = wfu_attribute_definitions();
	$defs_indexed = array();
	$defs_indexed_flat = array();
	foreach ( $defs as $def ) {
		$defs_indexed[$def["attribute"]] = $def;
		$defs_indexed_flat[$def["attribute"]] = $def["value"];
	}
	//get placement attribute from shortcode
	$placements = "";
	if ( isset($shortcode_atts["placements"]) ) $placements = $shortcode_atts["placements"];
	else $placements = $defs_indexed_flat["placements"];
	//get component definitions
	$components = wfu_component_definitions();
	//analyse components that can appear more than once in placements
	foreach ( $components as $component ) {
		if ( $component["multiplacements"] ) {
			$componentid = $component["id"];
			//count component occurrences in placements
			$component_occurrences = substr_count($placements, $componentid);
			if ( $component_occurrences > 1 && isset($defs_indexed[$componentid]) ) {
				//add incremented attribute definitions in $defs_indexed_flat array if occurrences are more than one
				for ( $i = 2; $i <= $component_occurrences; $i++ ) {
					foreach ( $defs_indexed[$componentid]["dependencies"] as $attribute )
						$defs_indexed_flat[$attribute.$i] = $defs_indexed_flat[$attribute];
				}
			}
		}
	}
	
	return $defs_indexed_flat;
}

function wfu_generate_current_params_index($shortcode_id, $user_login) {
	global $post;
	$cur_index_str = '||'.$post->ID.'||'.$shortcode_id.'||'.$user_login;
	$cur_index_str_search = '\|\|'.$post->ID.'\|\|'.$shortcode_id.'\|\|'.$user_login;
	$index_str = get_option('wfu_params_index');
	$index = explode("&&", $index_str);
	foreach ($index as $key => $value) if ($value == "") unset($index[$key]);
	$index_match = preg_grep("/".$cur_index_str_search."$/", $index);
	if ( count($index_match) == 1 )
		foreach ( $index_match as $key => $value )
			if ( $value == "" ) unset($index_match[$key]);
	if ( count($index_match) <= 0 ) {
		$cur_index_rand = wfu_create_random_string(16);
		array_push($index, $cur_index_rand.$cur_index_str);
	}
	else {
		reset($index_match);
		$cur_index_rand = substr(current($index_match), 0, 16);
		if ( count($index_match) > 1 ) {
			$index_match_keys = array_keys($index_match);
			for ($i = 1; $i < count($index_match); $i++) {
				$ii = $index_match_keys[$i];
				unset($index[array_search($index_match[$ii], $index, true)]);
			}
		}
	}
	if ( count($index_match) != 1 ) {
		$index_str = implode("&&", $index);
		update_option('wfu_params_index', $index_str);
	}
	return $cur_index_rand;
}

function wfu_get_params_fields_from_index($params_index, $session_token = "") {
	$fields = array();
	$index_str = get_option('wfu_params_index');
	$index = explode("&&", $index_str);
	$index_match = preg_grep("/^".$params_index."/", $index);
	if ( count($index_match) >= 1 )
		foreach ( $index_match as $key => $value )
			if ( $value == "" ) unset($index_match[$key]);
	if ( count($index_match) > 0 ) {
		if ( $session_token == "" ) {
			reset($index_match);
			list($fields['unique_id'], $fields['page_id'], $fields['shortcode_id'], $fields['user_login']) = explode("||", current($index_match));
		}
		//some times $params_index corresponds to 2 or more sets of params, so
		//we need to check session token in order to find the correct one
		else {
			$found = false;
			foreach ( $index_match as $value ) {
				list($fields['unique_id'], $fields['page_id'], $fields['shortcode_id'], $fields['user_login']) = explode("||", $value);
				$sid = $fields['shortcode_id'];
				if ( isset($_SESSION["wfu_token_".$sid]) && $_SESSION["wfu_token_".$sid] == $session_token ) {
					$found = true;
					break;
				}
			}
			if ( !$found ) $fields = array();
		}
	}
	return $fields; 
}

function wfu_safe_store_shortcode_data($data) {
	$code = wfu_create_random_string(16);
	$_SESSION['wfu_shortcode_data_safe_storage'][$code] = $data;
	return $code;
}

function wfu_get_shortcode_data_from_safe($code) {
	//sanitize $code
	$code = wfu_sanitize_code($code);
	if ( $code == "" ) return '';
	//return shortcode data from session variable, if exists
	if ( !isset($_SESSION['wfu_shortcode_data_safe_storage'][$code]) ) return '';
	return $_SESSION['wfu_shortcode_data_safe_storage'][$code];
}

function wfu_clear_shortcode_data_from_safe($code) {
	//sanitize $code
	$code = wfu_sanitize_code($code);
	if ( $code == "" ) return;
	//clear shortcode data from session variable, if exists
	if ( !isset($_SESSION['wfu_shortcode_data_safe_storage'][$code]) ) return;
	unset($_SESSION['wfu_shortcode_data_safe_storage'][$code]);
}

function wfu_decode_dimensions($dimensions_str) {
	$components = wfu_component_definitions();
	$dimensions = array();

	foreach ( $components as $comp ) {
		if ( $comp['dimensions'] == null ) $dimensions[$comp['id']] = "";
		else foreach ( $comp['dimensions'] as $dimraw ) {
			list($dim_id, $dim_name) = explode("/", $dimraw);
			$dimensions[$dim_id] = "";
		}
	}
	$dimensions_raw = explode(",", $dimensions_str);
	foreach ( $dimensions_raw as $dimension_str ) {
		$dimension_raw = explode(":", $dimension_str);
		$item = strtolower(trim($dimension_raw[0]));
		foreach ( array_keys($dimensions) as $key ) {
			if ( $item == $key ) $dimensions[$key] = trim($dimension_raw[1]);
		}
	}
	return $dimensions;
}

function wfu_placements_remove_item($placements, $item) {
	$itemplaces = explode("/", $placements);
	$newplacements = array();
	foreach ( $itemplaces as $section ) {
		$items_in_section = explode("+", trim($section));
		$newsection = array();
		foreach ( $items_in_section as $item_in_section ) {
			$item_in_section = strtolower(trim($item_in_section));
			if ( $item_in_section != "" && $item_in_section != $item ) array_push($newsection, $item_in_section);
		}
		if ( count($newsection) > 0 ) array_push($newplacements, implode("+", $newsection));
	}
	if ( count($newplacements) > 0 ) return implode("/", $newplacements);
	else return "";
}

//********************* Plugin Design Functions ********************************************************************************************

function wfu_get_uploader_template($templatename = "") {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	if ($templatename != "") {
		$classname = "WFU_UploaderTemplate_$templatename";
		if ( class_exists($classname) )
			return call_user_func(array($classname, 'get_instance'));
		$filepath = ABSWPFILEUPLOAD_DIR."templates/uploader-$templatename.php";
		if ( file_exists($filepath) ) {
			include_once $filepath;
			$classname = "WFU_UploaderTemplate_$templatename";
			if ( class_exists($classname) )
				return call_user_func(array($classname, 'get_instance'));
		}
	}
	return WFU_Original_Template::get_instance();
}

function wfu_get_browser_template($templatename = "") {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	if ($templatename != "") {
		$classname = "WFU_BrowserTemplate_$templatename";
		if ( class_exists($classname) )
			return call_user_func(array($classname, 'get_instance'));
		$filepath = ABSWPFILEUPLOAD_DIR."templates/browser-$templatename.php";
		if ( file_exists($filepath) ) {
			include_once $filepath;
			$classname = "WFU_BrowserTemplate_$templatename";
			if ( class_exists($classname) )
				return call_user_func(array($classname, 'get_instance'));
		}
	}
	return WFU_Original_Template::get_instance();
}

function wfu_add_div() {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$items_count = func_num_args();
	if ( $items_count == 0 ) return "";
	$items_raw = func_get_args();
	$params = $items_raw[0];
	unset($items_raw[0]);
	$items = array( );
	foreach ( $items_raw as $item_raw ) {
		if ( is_array($item_raw) ) array_push($items, $item_raw);
	}
	$items_count = count($items);
	if ( $items_count == 0 ) return "";
	
	$template = wfu_get_uploader_template($params["uploadertemplate"]);
	$data["ID"] = $params["uploadid"];
	$data["responsive"] = ( $params["fitmode"] == "responsive" );
	$data["items"] = $items;
	$data["params"] = $params;

	ob_start();
	$template->wfu_row_container_template($data);
	$str_output = ob_get_clean();
	return $str_output;
}

function wfu_read_template_output($blockname, $data) {
	$output = array();
	if ( isset($data["params"]["uploadertemplate"]) ) $template = wfu_get_uploader_template($data["params"]["uploadertemplate"]);
	else $template = wfu_get_browser_template($data["params"]["browsertemplate"]);
	$func = "wfu_".$blockname."_template";
	$sid = $data["ID"];
	ob_start();
	call_user_func(array($template, $func), $data);
	$str_output = ob_get_clean();
	
	$str_output = str_replace('$ID', $sid, $str_output);
	//extract css, javascript and HTML from output
	$match = array();
	preg_match("/<style>(.*)<\/style><script.*?>(.*)<\/script>(.*)/s", $str_output, $match);
	if ( count($match) == 4 ) {
		$output["css"] = trim($match[1]);
		$output["js"] = trim($match[2]);
		$html = trim($match[3]);
		$i = 1;
		foreach( preg_split("/((\r?\n)|(\r\n?))/", $html) as $line )
			$output["line".$i++] = $line;
	}
	
	return $output;
}

function wfu_template_to_HTML($blockname, $params, $additional_params, $occurrence_index) {
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));
	$block = call_user_func("wfu_prepare_".$blockname."_block", $params, $additional_params, $occurrence_index);
	if ( isset($params["uploadid"]) ) {
		$ID = $params["uploadid"];
		$WF = "WFU";
	}
	else {
		$ID = $params["browserid"];
		$WF = "WFUB";
	}
	$css = $block["css"];
	if ( $block["js"] != "" ) {
		$js = 'var '.$WF.'_JS_'.$ID.'_'.$blockname.' = function() {';
		$js .= "\n".$block["js"];
		$js .= "\n".'}';
		$js .= "\n".'wfu_run_js("window", "'.$WF.'_JS_'.$ID.'_'.$blockname.'");';
	}
	//relax css rules if this option is enabled
	if ( $plugin_options['relaxcss'] == '1' ) $css = preg_replace('#.*?/\*relax\*/\s*#', '', $css);
	$echo_str = wfu_css_to_HTML($css);
	$echo_str .= "\n".wfu_js_to_HTML($js);
	$k = 1;
	while ( isset($block["line".$k]) ) {
		if ( $block["line".$k] != "" ) $echo_str .= "\n".$block["line".$k];
		$k++;
	}

	return $echo_str;
}

function wfu_extract_css_js_from_components($section_array, &$css, &$js) {
	for ( $i = 1; $i < count($section_array); $i++ ) {
		if ( isset($section_array[$i]["css"]) ) $css .= ( $css == "" ? "" : "\n" ).$section_array[$i]["css"];
		if ( isset($section_array[$i]["js"]) ) $js .= ( $js == "" ? "" : "\n" ).$section_array[$i]["js"];
	}
	return;
}

function wfu_add_loading_overlay($dlp, $code) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$echo_str = $dlp.'<div id="wfu_'.$code.'_overlay" style="margin:0; padding: 0; width:100%; height:100%; position:absolute; left:0; top:0; border:none; background:none; display:none;">';
	$echo_str .= $dlp."\t".'<div style="margin:0; padding: 0; width:100%; height:100%; position:absolute; left:0; top:0; border:none; background-color:rgba(255,255,255,0.8); z-index:1;""></div>';
	$echo_str .= $dlp."\t".'<table style="margin:0; padding: 0; table-layout:fixed; width:100%; height:100%; position:absolute; left:0; top:0; border:none; background:none; z-index:2;"><tbody><tr><td align="center" style="border:none;">';
	$echo_str .= $dlp."\t\t".'<img src="'.WFU_IMAGE_OVERLAY_LOADING.'" /><br /><span>loading...</span>';
	$echo_str .= $dlp."\t".'</td></tr></tbody></table>';
	$echo_str .= $dlp.'</div>';
	
	return $echo_str;
}

function wfu_add_pagination_header($dlp, $code, $curpage, $pages, $nonce = false) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	if ($nonce === false) $nonce = wp_create_nonce( 'wfu-'.$code.'-page' );
	$echo_str = $dlp.'<div style="float:right;">';
	$echo_str .= $dlp."\t".'<label id="wfu_'.$code.'_first_disabled" style="margin:0 4px; font-weight:bold; opacity:0.5; cursor:default; display:'.( $curpage == 1 ? 'inline' : 'none' ).';">&#60;&#60;</label>';
	$echo_str .= $dlp."\t".'<label id="wfu_'.$code.'_prev_disabled" style="margin:0 4px; font-weight:bold; opacity:0.5; cursor:default; display:'.( $curpage == 1 ? 'inline' : 'none' ).';">&#60;</label>';
	$echo_str .= $dlp."\t".'<a id="wfu_'.$code.'_first" href="javascript:wfu_goto_'.$code.'_page(\''.$nonce.'\', \'first\');" style="margin:0 4px; font-weight:bold; display:'.( $curpage == 1 ? 'none' : 'inline' ).';">&#60;&#60;</a>';
	$echo_str .= $dlp."\t".'<a id="wfu_'.$code.'_prev" href="javascript:wfu_goto_'.$code.'_page(\''.$nonce.'\', \'prev\');" style="margin:0 4px; font-weight:bold; display:'.( $curpage == 1 ? 'none' : 'inline' ).';">&#60;</a>';
	$echo_str .= $dlp."\t".'<label style="margin:0 0 0 4px; cursor:default;">Page</label>';
	$echo_str .= $dlp."\t".'<select id="wfu_'.$code.'_pages" style="margin:0 4px;" onchange="wfu_goto_'.$code.'_page(\''.$nonce.'\', \'sel\');">';
	for ( $i = 1; $i <= $pages; $i++ )
		$echo_str .= $dlp."\t\t".'<option value="'.$i.'"'.( $i == $curpage ? ' selected="selected"' : '' ).'>'.$i.'</option>';
	$echo_str .= $dlp."\t".'</select>';
	$echo_str .= $dlp."\t".'<label style="margin:0 4px 0 0; cursor:default;">of '.$pages.'</label>';
	$echo_str .= $dlp."\t".'<label id="wfu_'.$code.'_next_disabled" style="margin:0 4px; font-weight:bold; opacity:0.5; cursor:default; display:'.( $curpage == $pages ? 'inline' : 'none' ).';">&#62;</label>';
	$echo_str .= $dlp."\t".'<label id="wfu_'.$code.'_last_disabled" style="margin:0 4px; font-weight:bold; opacity:0.5; cursor:default; display:'.( $curpage == $pages ? 'inline' : 'none' ).';">&#62;&#62;</label>';
	$echo_str .= $dlp."\t".'<a id="wfu_'.$code.'_next" href="javascript:wfu_goto_'.$code.'_page(\''.$nonce.'\', \'next\');" style="margin:0 4px; font-weight:bold; display:'.( $curpage == $pages ? 'none' : 'inline' ).';">&#62;</a>';
	$echo_str .= $dlp."\t".'<a id="wfu_'.$code.'_last" href="javascript:wfu_goto_'.$code.'_page(\''.$nonce.'\', \'last\');" style="margin:0 4px; font-weight:bold; display:'.( $curpage == $pages ? 'none' : 'inline' ).';">&#62;&#62;</a>';
	$echo_str .= $dlp.'</div>';
	
	return $echo_str;
}

function wfu_add_bulkactions_header($dlp, $code, $actions) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$echo_str = $dlp.'<div style="float:left;">';
	$echo_str .= $dlp."\t".'<select id="wfu_'.$code.'_bulkactions">';
	$echo_str .= $dlp."\t\t".'<option value="" selected="selected">'.( substr($code, 0, 8) == "browser_" ? WFU_BROWSER_BULKACTION_TITLE : "Bulk Actions").'</option>';
	foreach ( $actions as $action )
		$echo_str .= $dlp."\t\t".'<option value="'.$action["name"].'">'.$action["title"].'</option>';
	$echo_str .= $dlp."\t".'</select>';
	$echo_str .= $dlp."\t".'<input type="button" class="button action" value="'.( substr($code, 0, 8) == "browser_" ? WFU_BROWSER_BULKACTION_LABEL : "Apply").'" onclick="wfu_apply_'.$code.'_bulkaction();" />';
	$echo_str .= $dlp."\t".'<img src="'.WFU_IMAGE_OVERLAY_LOADING.'" style="display:none;" />';
	$echo_str .= $dlp.'</div>';
	
	return $echo_str;
}

function wfu_prepare_message_colors($template) {
	$color_array = explode(",", $template);
	$colors['color'] = $color_array[0];
	$colors['bgcolor'] = $color_array[1];
	$colors['borcolor'] = $color_array[2];
	return $colors;
}

//********************* Email Functions ****************************************************************************************************

function wfu_send_notification_email($user, $uploaded_file_paths, $userdata_fields, $params) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	global $blog_id;
	
	//create necessary variables
	$only_filename_list = "";
	$target_path_list = "";
	foreach ( $uploaded_file_paths as $filepath ) {
		$only_filename_list .= ( $only_filename_list == "" ? "" : ", " ).wfu_basename($filepath);
		$target_path_list .= ( $target_path_list == "" ? "" : ", " ).$filepath;
	}
	
	//apply wfu_before_email_notification filter
	$changable_data['recipients'] = $params["notifyrecipients"];
	$changable_data['subject'] = $params["notifysubject"];
	$changable_data['message'] = $params["notifymessage"];
	$changable_data['headers'] = $params["notifyheaders"];
	$changable_data['user_data'] = $userdata_fields;
	$changable_data['filename'] = $only_filename_list;
	$changable_data['filepath'] = $target_path_list;
	$changable_data['error_message'] = '';
	$additional_data['shortcode_id'] = $params["uploadid"];
	$ret_data = apply_filters('wfu_before_email_notification', $changable_data, $additional_data);
	
	if ( $ret_data['error_message'] == '' ) {
		$notifyrecipients = $ret_data['recipients'];
		$notifysubject = $ret_data['subject'];
		$notifymessage = $ret_data['message'];
		$notifyheaders = $ret_data['headers'];
		$userdata_fields = $ret_data['user_data'];
		$only_filename_list = $ret_data['filename'];
		$target_path_list = $ret_data['filepath'];

		if ( 0 == $user->ID ) {
			$user_login = "guest";
			$user_email = "";
		}
		else {
			$user_login = $user->user_login;
			$user_email = $user->user_email;
		}
		$search = array ('/%useremail%/', '/%n%/', '/%dq%/', '/%brl%/', '/%brr%/');	 
		$replace = array ($user_email, "\n", "\"", "[", "]");
		foreach ( $userdata_fields as $userdata_key => $userdata_field ) { 
			$ind = 1 + $userdata_key;
			array_push($search, '/%userdata'.$ind.'%/');  
			array_push($replace, $userdata_field["value"]);
		}   
//		$notifyrecipients =  trim(preg_replace('/%useremail%/', $user_email, $params["notifyrecipients"]));
		$notifyrecipients =  preg_replace($search, $replace, $notifyrecipients);
		$search = array ('/%n%/', '/%dq%/', '/%brl%/', '/%brr%/');	 
		$replace = array ("\n", "\"", "[", "]");
		$notifyheaders =  preg_replace($search, $replace, $notifyheaders);
		$search = array ('/%username%/', '/%useremail%/', '/%filename%/', '/%filepath%/', '/%blogid%/', '/%pageid%/', '/%pagetitle%/', '/%n%/', '/%dq%/', '/%brl%/', '/%brr%/');	 
		$replace = array ($user_login, ( $user_email == "" ? "no email" : $user_email ), $only_filename_list, $target_path_list, $blog_id, $params["pageid"], get_the_title($params["pageid"]), "\n", "\"", "[", "]");
		foreach ( $userdata_fields as $userdata_key => $userdata_field ) { 
			$ind = 1 + $userdata_key;
			array_push($search, '/%userdata'.$ind.'%/');  
			array_push($replace, $userdata_field["value"]);
		}   
		$notifysubject = preg_replace($search, $replace, $notifysubject);
		$notifymessage = preg_replace($search, $replace, $notifymessage);

		if ( $params["attachfile"] == "true" ) {
			$notify_sent = wp_mail($notifyrecipients, $notifysubject, $notifymessage, $notifyheaders, $uploaded_file_paths); 
		}
		else {
			$notify_sent = wp_mail($notifyrecipients, $notifysubject, $notifymessage, $notifyheaders); 
		}
		return ( $notify_sent ? "" : WFU_WARNING_NOTIFY_NOTSENT_UNKNOWNERROR );
	}
	else return $ret_data['error_message'];
}

function wfu_notify_admin($subject, $message) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$admin_email = get_option("admin_email");
	if ( $admin_email === false ) return;
	wp_mail($admin_email, $subject, $message);
}

//********************* Media Functions ****************************************************************************************************

// function wfu_process_media_insert contribution from Aaron Olin with some corrections regarding the upload path
function wfu_process_media_insert($file_path, $userdata_fields, $page_id){
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$wp_upload_dir = wp_upload_dir();
	$filetype = wp_check_filetype( wfu_basename( $file_path ), null );

	$attachment = array(
		'guid'           => $wp_upload_dir['url'] . '/' . wfu_basename( $file_path ), 
		'post_mime_type' => $filetype['type'],
		'post_title'     => preg_replace( '/\.[^.]+$/', '', wfu_basename( $file_path ) ),
		'post_content'   => '',
		'post_status'    => 'inherit'
	);

	$attach_id = wp_insert_attachment( $attachment, $file_path, $page_id ); 
	
	// If file is an image, process the default thumbnails for previews
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
	// Add userdata as attachment metadata
	foreach ( $userdata_fields as $userdata_field )
		$attach_data["WFU User Data"][$userdata_field["label"]] = $userdata_field["value"];
	$update_attach = wp_update_attachment_metadata( $attach_id, $attach_data );
	// link attachment with file in plugin's database
	$filedata = wfu_get_filedata($file_path, true);
	if ( $filedata != null ) {
		$filedata["media"] = array(
			"type"		=> "data",
			"attach_id"	=> $attach_id
		);
		wfu_save_filedata_from_id($filedata["general"]["idlog"], $filedata);
	}

	return $attach_id;	
}

//********************* Form Fields Functions ****************************************************************************************************

function wfu_preg_replace_callback_func($matches) {
	return str_replace("[/]", "/", $matches[0]);
}

// function wfu_parse_userdata_attribute parses the shortcode attribute to a form field array
function wfu_parse_userdata_attribute($value){
	$fields = array();
	//read defaults
	$definitions_unindexed = wfu_formfield_definitions();
	$defaults = array();
	foreach ( $definitions_unindexed as $def ) {
		$default = array();
		$default["type"] = $def["type"];
		$default["label"] = "";
		$default["labelposition"] = "".substr($def["labelposition"], 5);
		$default["required"] = ( substr($def["required"], 5) == "true" );
		$default["donotautocomplete"] = ( substr($def["donotautocomplete"], 5) == "true" );
		$default["validate"] = ( substr($def["validate"], 5) == "true" );
		$default["typehook"] = ( substr($def["typehook"], 5) == "true" );
		$default["hintposition"] = "".substr($def["hintposition"], 5);
		$default["default"] = "".substr($def["default"], 5);
		$default["data"] = "".substr($def["data"], 5);
		$default["group"] = "".substr($def["group"], 5);
		$default["format"] = "".substr($def["format"], 5);
		$defaults[$def["type"]] = $default;
	}
//	$fields_arr = explode("/", $value);
	$value = str_replace("/", "[/]", $value);
	$value = preg_replace_callback("/\(.*\)/", "wfu_preg_replace_callback_func", $value);
	$fields_arr = explode("[/]", $value);
	//parse shortcode attribute to $fields
	foreach ( $fields_arr as $field_raw ) {
		$field_raw = trim($field_raw);
		$fieldprops = $defaults["text"];
		//read old default attribute
		if ( substr($field_raw, 0, 1) == "*" ) {
			$fieldprops["required"] = true;
			$field_raw = substr($field_raw, 1);
		}
		$field_parts = explode("|", $field_raw);
		//proceed if the first part, which is the label, is non-empty
		if ( trim($field_parts[0]) != "" ) {
			//get type, if exists, in order to adjust defaults
			$type_key = -1;
			$new_type = "";
			foreach ( $field_parts as $key => $part ) {
				$part = ltrim($part);
				$flag = substr($part, 0, 2);
				$val = substr($part, 2);
				if ( $flag == "t:" && $key > 0 && array_key_exists($val, $defaults) ) {
					$new_type = $val;
					$type_key = $key;
					break;
				}
			}
			if ( $new_type != "" ) {
				$fieldprops = $defaults[$new_type];
				unset($field_parts[$type_key]);
			}
			//store label
			$fieldprops["label"] = trim($field_parts[0]);
			unset($field_parts[0]);
			//get other properties
			foreach ( $field_parts as $part ) {
				$part = ltrim($part);
				$flag = substr($part, 0, 2);
				$val = "".substr($part, 2);
				if ( $flag == "s:" ) $fieldprops["labelposition"] = $val;
				elseif ( $flag == "r:" ) $fieldprops["required"] = ( $val == "1" );
				elseif ( $flag == "a:" ) $fieldprops["donotautocomplete"] = ( $val == "1" );
				elseif ( $flag == "v:" ) $fieldprops["validate"] = ( $val == "1" );
				elseif ( $flag == "d:" ) $fieldprops["default"] = $val;
				elseif ( $flag == "l:" ) $fieldprops["data"] = $val;
				elseif ( $flag == "g:" ) $fieldprops["group"] = $val;
				elseif ( $flag == "f:" ) $fieldprops["format"] = $val;
				elseif ( $flag == "p:" ) $fieldprops["hintposition"] = $val;
				elseif ( $flag == "h:" ) $fieldprops["typehook"] = ( $val == "1" );
			}
			array_push($fields, $fieldprops);
		}
	}

	return $fields;	
}

//********************* Javascript Related Functions ****************************************************************************************************

// function wfu_inject_js_code generates html code for injecting js code and then erase the trace
function wfu_inject_js_code($code){
	$id = 'code_'.wfu_create_random_string(8);
	$html = '<div id="'.$id.'" style="display:none;"><script type="text/javascript">'.$code.'</script><script type="text/javascript">var div = document.getElementById("'.$id.'"); div.parentNode.removeChild(div);</script></div>';

	return $html;	
}

//********************* Browser Functions ****************************************************************************************************

function wfu_safe_store_browser_params($params) {
	$code = wfu_create_random_string(16);
	$_SESSION['wfu_browser_actions_safe_storage'][$code] = $params;
	return $code;
}

function wfu_get_browser_params_from_safe($code) {
	//sanitize $code
	$code = wfu_sanitize_code($code);
	if ( $code == "" ) return false;
	//return params from session variable, if exists
	if ( !isset($_SESSION['wfu_browser_actions_safe_storage'][$code]) ) return false;
	return $_SESSION['wfu_browser_actions_safe_storage'][$code];
}

//********************* POST/GET Requests Functions ****************************************************************************************************

function wfu_decode_socket_response($response) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$ret = "";
	if (0 === strpos($response, 'HTTP/1.1 200 OK')) {
		$parts = preg_split("#\n\s*\n#Uis", $response);
		if ( count($parts) > 1 ) {
			$rawheader = strtolower(preg_replace("/\s/", "", $parts[0]));
			if ( strpos($rawheader, 'transfer-encoding:chunked') !== false ) {
				$ret = "";
				$pos = 0;
				while ( $pos < strlen($parts[1]) ) {
					$next = strpos($parts[1], "\r\n", $pos);
					$len = ( $next === false || $next == $pos ? 0 : hexdec(substr($parts[1], $pos, $next - $pos)) );
					if ( $len <= 0 ) break;
					$ret .= substr($parts[1], $next + 2, $len);
					$pos = $next + $len + 4;
				}
			}
			else $ret = $parts[1];
		}
	}
	return $ret;
}

function wfu_post_request($url, $params, $verifypeer = false, $internal_request = false, $timeout = 0) {
	$a = func_get_args(); switch(WFU_FUNCTION_HOOK(__FUNCTION__, $a, $out)) { case 'X': break; case 'R': return $out; break; case 'D': die($out); break; }
	$plugin_options = wfu_decode_plugin_options(get_option( "wordpress_file_upload_options" ));
	if ( isset($plugin_options['postmethod']) && $plugin_options['postmethod'] == 'curl' ) {
		// POST request using CURL
		$ch = curl_init($url);
		$options = array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query($params),
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/x-www-form-urlencoded'
			),
			CURLINFO_HEADER_OUT => false,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => $verifypeer
		);
		if ( $timeout > 0 ) $options[CURLOPT_TIMEOUT] = $timeout;
		//for internal requests to /wp-admin area that is password protected
		//authorization is required
		if ( $internal_request && WFU_VAR("WFU_DASHBOARD_PROTECTED") == "true" ) {
			$options[CURLOPT_HTTPAUTH] = CURLAUTH_ANY;
			$options[CURLOPT_USERPWD] = WFU_VAR("WFU_DASHBOARD_USERNAME").":".WFU_VAR("WFU_DASHBOARD_PASSWORD");
		}
		if ( WFU_VAR("WFU_RELAX_CURL_VERIFY_HOST") == "true" ) $options[CURLOPT_SSL_VERIFYHOST] = false;
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		curl_close ($ch);
		return $result;
	}
	elseif ( isset($plugin_options['postmethod']) && $plugin_options['postmethod'] == 'socket' ) {
		// POST request using sockets
		$scheme = "";
		$port = 80;
		$errno = 0;
        $errstr = '';
		$ret = '';
		$url = parse_url($url);
		$host = $url['host'];
		$path = $url['path'];
		if ( $url['scheme'] == 'https' ) { 
			$scheme = "ssl://";
			$port = 443;
			if ( $timeout == 0 ) $timeout = 30;
		}
		elseif ( $url['scheme'] != 'http' ) return '';
		$handle = fsockopen($scheme.$host, $port, $errno, $errstr, ($timeout == 0 ? ini_get("default_socket_timeout") : $timeout));
		if ( $errno !== 0 || $errstr !== '' ) $handle = false;
		if ( $handle !== false ) {
			$content = http_build_query($params);
			$request = "POST " . $path . " HTTP/1.1\r\n";
            $request .= "Host: " . $host . "\r\n";
            $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
			//for internal requests to /wp-admin area that is password protected
			//authorization is required
			if ( $internal_request && WFU_VAR("WFU_DASHBOARD_PROTECTED") == "true" ) {
				$request .= "Authorization: Basic ".base64_encode(WFU_VAR("WFU_DASHBOARD_USERNAME").":".WFU_VAR("WFU_DASHBOARD_PASSWORD"))."\r\n";
			}
           $request .= "Content-length: " . strlen($content) . "\r\n";
            $request .= "Connection: close\r\n\r\n";
            $request .= $content . "\r\n\r\n";
			fwrite($handle, $request, strlen($request));
			$response = '';
			while ( !feof($handle) ) {
                $response .= fgets($handle, 4096);
            }
			fclose($handle);
			$ret = wfu_decode_socket_response($response);
		}
		return $ret;
	}
	else {
		// POST request using file_get_contents
		if ( $internal_request && WFU_VAR("WFU_DASHBOARD_PROTECTED") == "true" ) {
			$url = preg_replace("/^(http|https):\/\//", "$1://".WFU_VAR("WFU_DASHBOARD_USERNAME").":".WFU_VAR("WFU_DASHBOARD_PASSWORD")."@", $url);
		}
		$peer_key = version_compare(PHP_VERSION, '5.6.0', '<') ? 'CN_name' : 'peer_name';
		$http_array = array(
			'method'  => 'POST',
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'content' => http_build_query($params)
		);
		if ( $timeout > 0 ) $http_array['timeout'] = $timeout;
		//for internal requests to /wp-admin area that is password protected
		//authorization is required
		if ( $internal_request && WFU_VAR("WFU_DASHBOARD_PROTECTED") == "true" ) {
			$http_array['header'] .= "Authorization: Basic ".base64_encode(WFU_VAR("WFU_DASHBOARD_USERNAME").":".WFU_VAR("WFU_DASHBOARD_PASSWORD"))."\r\n";
		}
		if ( $verifypeer ) {
			$http_array['verify_peer'] = true;
			$http_array[$peer_key] = 'www.google.com';
		}
		$context_params = array( 'http' => $http_array );
		$context = stream_context_create($context_params);
		return file_get_contents($url, false, $context);
	}
}

?>
