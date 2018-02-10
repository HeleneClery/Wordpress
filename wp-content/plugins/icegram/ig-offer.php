<?php 
 if( get_option('ig_offer_christmas_done_icegram') == 1 ) return;
?>
<style type="text/css">
.ig_offer{
    width: 90%;
    height: auto;
    margin: 1em auto;
    text-align: center;
    background-color: #00003a;
    font-size: 1.2em;
    /*font-family: sans-serif;*/
    letter-spacing: 3px;
    line-height: 1.2em;
    padding: 2em;
    background-image: url('<?php echo  $this->plugin_url ?>/assets/images/christmas.png');
    background-repeat: no-repeat;
    background-size: contain;
    background-position: left;
}
.ig_offer_heading{
    color: #64badd;
    color: #64ddc1;
    padding: 1em 0;
    line-height: 1.2em;
}
.ig_main_heading {
    font-size: 3em;
    color: #FFFFFF;
    font-weight: 600;
    margin-bottom: 0.6em;
    line-height: 1.2em;
    position: relative;
}

.ig_text{
    font-size: 0.9em;
}
.ig_left_text{
    padding: 0.6em 5.4em 0.6em;
    color: #8a8a8a;
}
.ig_right_text{
    color: #FFFFFF;
    font-weight: 600;
    max-width: 50%;
    padding: 10px 56px;
    width: auto;
    margin: 0;
    display: inline-block;
    text-decoration: none;
    background: #b70f0f;
}
.ig_right_text:hover, .ig_right_text:active{
    color: inherit; 
}
.ig_offer_content{
    margin-left: 15%;
}
</style>
<div class="ig_offer">
    <div style="float:right;"><img src="<?php echo  $this->plugin_url ?>/assets/images/icegram-logo-16bit-gray-30.png"/></div>
        <div  class="ig_offer_content">
	        <div class="ig_offer_heading">It's time to be merry! </div>
		    <div class="ig_main_heading">Grab FLAT 20% OFF Storewide </div>
			<div class="ig_text">
				<div class="ig_left_text" style="font-size:1.1em;">Offer applicable on all premium plans of <span style="color:#64ddc1;font-weight:bold">Icegram, Rainmaker & Email Subscribers</span></div>
                <a href="?ig_dismiss_admin_notice=1&ig_option_name=ig_offer_christmas_done" target="_blank" class="ig_right_text">Start Shopping</a>
                <div class="ig_left_text">Offer ends on 26th December, 2017 - so hurry.. </div>
            </div>
		</div>
</div>