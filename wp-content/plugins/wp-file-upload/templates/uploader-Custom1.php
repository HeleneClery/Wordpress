<?php

/**
 * Defines a custom upload template
 * 
 * This is a very simple example of creation of a custom upload template by
 * extending the original template.
 * 
 * This custom template is a child of the original template class, so it is not
 * required to declare all functions of the template but only those that are
 * different.
 */
class WFU_UploaderTemplate_Custom1 extends WFU_Original_Template {

private static $instance = null;
public static $name = "WFU_Original_Template";

public static function get_instance() {
	if ( null == self::$instance ) {
		self::$instance = new static();
		self::$name = get_called_class();
	}

	return self::$instance;
}

function wfu_uploadform_template($data) {?>
<?php /*************************************************************************
          the following lines contain initialization of PHP variables
*******************************************************************************/
/* do not change this line */extract($data);
/*
 *  The following variables are available for use:
 *  
 *  @var $ID int the upload ID
 *  @var $width string assigned width of select button element
 *  @var $height string assigned height of select button element
 *  @var $responsive bool true if responsive mode is enabled
 *  @var $testmode bool true if the plugin is in test mode
 *  @var $label string the title of the select button element
 *  @var $filename string the name that the selected file must have when
 *       submitted for upload by the form; it must be passed to the 'name'
 *       attribute of the form's input element of 'file' type
 *  $var hidden_elements array holds an array of hidden elements that must be
 *       added in the HTML form so that the plugin works correctly; every item
 *       of the array has three properties, the 'id', the 'name' and the 'value'
 *       of the hidden element
 *  @var $index int the index of occurrence of the element inside the plugin,
 *       in case that it appears more than once
 *  @var $params array all plugin's attributes defined through the shortcode
 *  
 *  It is noted that $ID can also be used inside CSS, Javascript and HTML code.
 */
	$styles = "";
	//for responsive plugin adjust container's widths if a % width has been defined
	if ( $responsive && strlen($width) > 1 && substr($width, -1, 1) == "%" ) $styles = 'width: 100%;';
	elseif ( $width != "" ) $styles = "width: $width; ";
	if ( $height != "" ) $styles .= "height: $height; ";
	$styles_form = $styles;
	if ( $testmode ) $styles .= 'z-index: 500;';
/*******************************************************************************
              the following lines contain CSS styling rules
*********************************************************************/ ?><style>
form.file_input_uploadform
{
	position: relative; 
	width: 100px; /*relax*/
	height: 27px; /*relax*/
	overflow: hidden;
	margin: 0px;
	padding: 0px;
}

input[type="button"].file_input_button
{
	width: 100px; /*relax*/
	height: 27px; /*relax*/
	position: absolute; /*relax*/
	top: 0px; /*relax*/
	margin: 0px; /*relax*/
	padding: 0px; /*relax*/
	background-color: #EEEEEE; /*relax*/
	color: #555555; /*relax*/
	background-image: url("<?php echo WPFILEUPLOAD_DIR; ?>images/white-grad-active.png"); /*relax*/
	background-position: left top; /*relax*/
	background-repeat: repeat-x; /*relax*/
	border-style: solid; /*relax*/
	border-width: 1px; /*relax*/
	border-color: #BBBBBB; /*relax*/
	-webkit-border-radius: 2px; /*relax*/
	-moz-border-radius: 2px; /*relax*/
	-khtml-border-radius: 2px; /*relax*/
	border-radius: 2px; /*relax*/
}

input[type="button"].file_input_button_hover
{
	width: 100px; /*relax*/
	height: 27px; /*relax*/
	position: absolute; /*relax*/
	top: 0px; /*relax*/
	margin: 0px; /*relax*/
	padding: 0px; /*relax*/
	background-color: #EEEEEE; /*relax*/
	color: #111111; /*relax*/
	background-image: url("<?php echo WPFILEUPLOAD_DIR; ?>images/white-grad-active.png"); /*relax*/
	background-position: left top; /*relax*/
	background-repeat: repeat-x; /*relax*/
	border-style: solid; /*relax*/
	border-width: 1px; /*relax*/
	border-color: #333333; /*relax*/
	-webkit-border-radius: 2px; /*relax*/
	-moz-border-radius: 2px; /*relax*/
	-khtml-border-radius: 2px; /*relax*/
	border-radius: 2px; /*relax*/
}

input[type="button"].file_input_button:disabled, input[type="button"].file_input_button_hover:disabled
{
	width: 100px; /*relax*/
	height: 27px; /*relax*/
	position: absolute; /*relax*/
	top: 0px; /*relax*/
	margin: 0px; /*relax*/
	padding: 0px; /*relax*/
	background-color: #EEEEEE; /*relax*/
	color: silver; /*relax*/
	background-image: url("<?php echo WPFILEUPLOAD_DIR; ?>images/white-grad-active.png"); /*relax*/
	background-position: left top; /*relax*/
	background-repeat: repeat-x; /*relax*/
	border-style: solid; /*relax*/
	border-width: 1px; /*relax*/
	border-color: #BBBBBB; /*relax*/
	-webkit-border-radius: 2px; /*relax*/
	-moz-border-radius: 2px; /*relax*/
	-khtml-border-radius: 2px; /*relax*/
	border-radius: 2px; /*relax*/
}

input[type="file"].file_input_hidden
{
	font-size: 45px; 
	position: absolute;
	right: 0px; 
	top: 0px; 
	margin: 0px;
	padding: 0px;
	-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
	filter: alpha(opacity=0);
	-moz-opacity: 0;
	-khtml-opacity: 0;
	opacity: 0;
}
</style><?php /*****************************************************************
               the following lines contain Javascript code 
*********************************************/ ?><script type="text/javascript">
/* do not change this line */GlobalData.WFU[$ID].uploadform.init = function() {
/***
 *  The following uploadform methods can be defined by the template, together
 *  with other initialization actions:
 *
 *  @method attachActions attaches necessary actions of the plugin that must be
 *          run when the select button is clicked or when the user changes the
 *          selected file
 *  @method reset resets the upload form
 *  @method submit submits the upload form
 *  @method lock locks the upload form
 *  @method unlock unlocks the upload form
 *  @method changeFileName changes the name that the selected file must have
 *  @method files returns the list of files selected by the user
 */
/**
 *  attaches necessary actions of the plugin
 *  
 *  This function attaches necessary actions of the plugin that must be run when
 *  the select button of the form is clicked or when the user changes the
 *  selected file.
 *  
 *  @param clickaction object this is a function that must be called when the
 *         user clicks the select button in order to select a file; it takes no
 *         parameters
 *  @param changeaction object this is a function that must be called when the
 *         user selects a file; a boolean true or false must be passed as
 *         parameter, denoting if a file has been selected or not
 *  
 *  @return void
 */
this.attachActions = function(clickaction, changeaction) {
	document.getElementById("upfile_$ID").onclick = function() { clickaction(); };
	document.getElementById("upfile_$ID").onchange = function() { changeaction(document.getElementById("upfile_$ID").value != ""); };
}

/**
 *  resets the upload form
 *  
 *  This function runs right after an upload has finished, in order to clear the
 *  list of files. It has a meaning only when the upload is done using classic
 *  HTML Forms and not AJAX.
 *  
 *  @return void
 */
this.reset = function() {
	document.getElementById("uploadform_$ID").reset();
}

/**
 *  submits the upload form
 *  
 *  This function runs when the upload starts, in order to submit the files
 *  using the classic HTML Forms and not AJAX.
 *  
 *  @return void
 */
this.submit = function() {
	document.getElementById("upfile_$ID").disabled = false;
	document.getElementById("uploadform_$ID").submit();
}

/**
 *  locks the upload form
 *  
 *  This function runs right before an upload starts, in order to disable the
 *  form and select button elements, so that the user cannot select files while
 *  an upload is on progress.
 *  
 *  @return void
 */
this.lock = function() {
	document.getElementById("input_$ID").disabled = true;
	document.getElementById("upfile_$ID").disabled = true;
}

/**
 *  unlocks the upload form
 *  
 *  This function runs right after finish of an upload, in order to re-enable
 *  the form and the select button.
 *  
 *  @return void
 */
this.unlock = function() {
	document.getElementById("input_$ID").disabled = false;
	document.getElementById("upfile_$ID").disabled = false;
}

/**
 *  changes the name that the selected file must have
 *  
 *  This function changes the name that the selected file must have when
 *  submitted for upload by the form. it must be passed to the 'name' attribute
 *  of the form's input element of 'file' type.
 *  
 *  @param new_filename string the new name of the file
 *  
 *  @return void
 */
this.changeFileName = function(new_filename) {
	document.getElementById("upfile_$ID").name = new_filename;
}

/**
 *  returns the list of files selected by the user
 *  
 *  This function returns the list of files selected by the user, which are
 *  stored in the input element of type "file" of the form.
 *  
 *  @return object
 */
this.files = function() {
	var inputfile = document.getElementById("upfile_$ID");
	var farr = inputfile.files;
	//fix in case files attribute is not supported
	if (!farr) { if (inputfile.value) farr = [{name:inputfile.value}]; else farr = []; }
	return farr;
}
/* do not change this line */}
</script><?php /****************************************************************
               the following lines contain the HTML output 
****************************************************************************/ ?>
<form class="file_input_uploadform" id="uploadform_$ID" name="uploadform_$ID" method="post" enctype="multipart/form-data" style="<?php echo $styles_form; ?>">
<?php if ( $testmode ): ?>
	<input align="center" type="button" id="input_$ID" value="<?php echo $label; ?>" class="gamipress-link file_input_button" style="<?php echo $styles; ?>" onmouseout="javascript: document.getElementById('input_$ID').className = 'gamipress-link file_input_button';" onmouseover="javascript: document.getElementById('input_$ID').className = 'gamipress-link file_input_button_hover';" onclick="alert('<?php echo WFU_NOTIFY_TESTMODE; ?>');" />
<?php else: ?>
	<input align="center" type="button" id="input_$ID" value="<?php echo $label; ?>" class="gamipress-link file_input_button" style="<?php echo $styles; ?>" />
<?php endif ?>
	<input type="file" class="file_input_hidden" name="<?php echo $filename; ?>" id="upfile_$ID" tabindex="1" onmouseout="javascript: document.getElementById('input_$ID').className = 'gamipress-link file_input_button';" onmouseover="javascript: document.getElementById('input_$ID').className = 'gamipress-link file_input_button_hover';"<?php echo ""; ?> />
<?php foreach( $hidden_elements as $elem ): ?>
	<input type="hidden" id="<?php echo $elem["id"]; ?>" name="<?php echo $elem["name"]; ?>" value="<?php echo $elem["value"]; ?>" />
<?php endforeach ?>
</form>
<?php /*************************************************************************
                            end of HTML output 
*****************************************************************************/ }

function wfu_submit_template($data) {?>
<?php /*************************************************************************
          the following lines contain initialization of PHP variables
*******************************************************************************/
/* do not change this line */extract($data);
/*
 *  The following variables are available for use:
 *  
 *  @var $ID int the upload ID
 *  @var $width string assigned width of upload button element
 *  @var $height string assigned height of upload button element
 *  @var $responsive bool true if responsive mode is enabled
 *  @var $testmode bool true if the plugin is in test mode
 *  @var $allownofile bool true if it is allowed to submit the upload form
 *       without any file selected
 *  @var $label string the title of the upload button element
 *  @var $index int the index of occurrence of the element inside the plugin,
 *       in case that it appears more than once
 *  @var $params array all plugin's attributes defined through the shortcode
 *  
 *  It is noted that $ID can also be used inside CSS, Javascript and HTML code.
 */
	$styles = "";
	//for responsive plugin adjust container's widths if a % width has been defined
	if ( $responsive && strlen($width) > 1 && substr($width, -1, 1) == "%" ) $styles = 'width: 100%;';
	elseif ( $width != "" ) $styles = "width: $width; ";
	if ( $height != "" ) $styles .= "height: $height; ";
/*******************************************************************************
              the following lines contain CSS styling rules
*********************************************************************/ ?><style>
input[type="button"].file_input_submit
{
	width: 100px; /*relax*/
	height: 27px; /*relax*/
	position: relative; /*relax*/
	margin: 0px; /*relax*/
	padding: 0px; /*relax*/
	background-color: #EEEEEE; /*relax*/
	color: #555555; /*relax*/
	background-image: url("<?php echo WPFILEUPLOAD_DIR; ?>images/white-grad-active.png"); /*relax*/
	background-position: left top; /*relax*/
	background-repeat: repeat-x; /*relax*/
	border-style: solid; /*relax*/
	border-width: 1px; /*relax*/
	border-color: #BBBBBB; /*relax*/
	-webkit-border-radius: 2px; /*relax*/
	-moz-border-radius: 2px; /*relax*/
	-khtml-border-radius: 2px; /*relax*/
	border-radius: 2px; /*relax*/
}

input[type="button"].file_input_submit:hover, input[type="button"].file_input_submit:focus
{
	width: 100px; /*relax*/
	height: 27px; /*relax*/
	position: relative; /*relax*/
	margin: 0px; /*relax*/
	padding: 0px; /*relax*/
	background-color: #EEEEEE; /*relax*/
	color: #111111; /*relax*/
	background-image: url("<?php echo WPFILEUPLOAD_DIR; ?>images/white-grad-active.png"); /*relax*/
	background-position: left top; /*relax*/
	background-repeat: repeat-x; /*relax*/
	border-style: solid; /*relax*/
	border-width: 1px; /*relax*/
	border-color: #333333; /*relax*/
	-webkit-border-radius: 2px; /*relax*/
	-moz-border-radius: 2px; /*relax*/
	-khtml-border-radius: 2px; /*relax*/
	border-radius: 2px; /*relax*/
}

input[type="button"].file_input_submit:disabled
{
	width: 100px; /*relax*/
	height: 27px; /*relax*/
	position: relative; /*relax*/
	margin: 0px; /*relax*/
	padding: 0px; /*relax*/
	background-color: #EEEEEE; /*relax*/
	color: silver; /*relax*/
	background-image: url("<?php echo WPFILEUPLOAD_DIR; ?>images/white-grad-active.png"); /*relax*/
	background-position: left top; /*relax*/
	background-repeat: repeat-x; /*relax*/
	border-style: solid; /*relax*/
	border-width: 1px; /*relax*/
	border-color: #BBBBBB; /*relax*/
	-webkit-border-radius: 2px; /*relax*/
	-moz-border-radius: 2px; /*relax*/
	-khtml-border-radius: 2px; /*relax*/
	border-radius: 2px; /*relax*/
}
</style><?php /*****************************************************************
               the following lines contain Javascript code 
*********************************************/ ?><script type="text/javascript">
/* do not change this line */GlobalData.WFU[$ID].submit.init = function() {
/***
 *  The following upload button methods can be defined by the template, together
 *  with other initialization actions:
 *
 *  @method attachClickAction attaches necessary action of the plugin that must
 *          be run when the upload button is clicked
 *  @method updateLabel updates the label of the upload button
 *  @method toggle enables or disables the upload button
 */
/**
 *  attaches necessary click action of the plugin
 *  
 *  This function attaches necessary action of the plugin that must be ran when
 *  the upload button is clicked.
 *  
 *  @param clickaction object this is a function that must be called when the
 *         user clicks the upload button in order to upload the selected file
 *  
 *  @return void
 */
this.attachClickAction = function(clickaction) {
	document.getElementById("upload_$ID").onclick = function() { clickaction(); };
}

/**
 *  updates the label of the upload button
 *  
 *  @param new_label string the new label of the upload button
 *  
 *  @return void
 */
this.updateLabel = function(new_label) {
	document.getElementById("upload_$ID").value = new_label;
}

/**
 *  enables or disables the upload button
 *  
 *  @param status bool if true the the upload button must be enabled, if false
 *         then the upload button must be disabled
 *  
 *  @return void
 */
this.toggle = function(status) {
	document.getElementById("upload_$ID").disabled = !status;
}
/* do not change this line */}
</script><?php /****************************************************************
               the following lines contain the HTML output 
****************************************************************************/ ?>
<?php if ( $testmode ): ?>
<input align="center" type="button" id="upload_$ID" name="upload_$ID" value="<?php echo $label; ?>" class="gamipress-link file_input_submit" style="<?php echo $styles; ?>" />
<?php else: ?>
<input align="center" type="button" id="upload_$ID" name="upload_$ID" value="<?php echo $label; ?>" class="gamipress-link file_input_submit" style="<?php echo $styles; ?>"<?php echo ( $allownofile ? '' : ' disabled="disabled"' ); ?> />
<?php endif ?>
<?php /*************************************************************************
                            end of HTML output 
*****************************************************************************/ }

}

?>
