<style type="text/css">body{background:#FFF;}</style>
<style type="text/css">
       /* Common styles */
        .pricing {
            display: -webkit-flex;
            display: flex;
            -webkit-flex-wrap: wrap;
            flex-wrap: wrap;
            -webkit-justify-content: center;
            justify-content: center;
            width: 100%;
            margin: 0 auto 3em;
        }

        .pricing__item {
            position: relative;
            display: -webkit-flex;
            display: flex;
            -webkit-flex-direction: column;
            flex-direction: column;
            -webkit-align-items: stretch;
            align-items: stretch;
            text-align: center;
            -webkit-flex: 0 1 230px;
            flex: 0 1 230px;
        }

        .pricing__feature-list {
            text-align: left;
        }

        .pricing__action {
            color: inherit;
            border: none;
            background: none;
        }

        .pricing__action:focus {
            outline: none;
        }

        /* Pema */
        .pricing--pema .pricing__item {
            /*font-family: 'Alegreya Sans', sans-serif;*/
            padding: 2em;
            margin: 1em;
            color: #262b38;
            background: #fff;
            cursor: default;
            overflow: hidden;
            box-shadow: 0 0 10px 3px rgba(0,0,0,0.05);
        }

        @media screen and (min-width: 66.250em) {
            .pricing--pema .pricing__item {
                margin: 1.5em 0;
            }
            .pricing--pema .pricing__item--featured {
                z-index: 10;
                margin: 0;
                font-size: 1em;
                background-color: rgb(249, 246, 214);
                outline: 1px solid rgba(58, 115, 190, 0.3);
                outline-offset: -8px;
            }
        }

        .pricing--pema .pricing__title {
            font-size: 2em;
            margin: 0.5em 0 .2em;
            color: #555;
        }

        .pricing--pema .icon {
            display: inline-block;
            min-width: 2em;
            color: #8A9790;
            /*vertical-align: middle;*/
        }

        .pricing--pema .pricing__price {
            font-size: 4em;
            font-weight: 800;
            color: #1e73be;
            position: relative;
            z-index: 100;
        }

        .pricing--pema .pricing__currency {
            font-size: 0.5em;
            /*vertical-align: text-bottom;*/
        }

        .pricing--pema .pricing__period {
            font-size: 0.25em;
            display: inline-block;
            padding: 0 0 0 0.5em;
            color: #AAA;
        }

        .pricing--pema .pricing__sentence {
            /*font-weight: bold;*/
            margin: 1.2em 0;
            padding: 0;
            color: #1e73be;
            line-height: 1.4;
            font-size: 0.9em;
            color: rgb(0, 73, 148);
        }
        .pricing--pema .pricing__headline {
            font-size: 1.2em;
            font-weight: bold;
            color: #555;
        }

        .pricing--pema .pricing__feature-list {
            /*font-size: 0.85em;*/
            margin: 0;
            padding: 1.5em 0.5em 2.5em;
            list-style: none;
        }

        .pricing--pema .pricing__feature {
            padding: 0.15em 0;
        }

        .pricing--pema .pricing__action {
            /*font-weight: bold;
            margin-top: auto;
            padding: 1em 2em;*/
            color: #fff;
            /*border-radius: 5px;*/
            background: #1e73be;
            -webkit-transition: background-color 0.3s;
            transition: background-color 0.3s;
            margin: 1.5em 0;
        }

        .pricing--pema .pricing__action:hover,
        .pricing--pema .pricing__action:focus {
            /*background-color: #4F5F56;*/
        }
        .button.button-primary.large.pricing__action a{
        	text-decoration: none;
        	color: #FFF !important;
        }
   </style>
<div class="wrap upgrade_page">
<h1><?php _e('The Various Plans Available', 'icegram'); ?></h1>
<!-- <div class="size-full-x"> -->
<section class="pricing-section bg-6">
	<div class="pricing pricing--pema">
		<div class="pricing__item">
			<h3 class="pricing__title">Plus</h3>
			<p class="pricing__sentence">For measuring & improving conversions<br/></p>
			<div class="pricing__price"><span class="pricing__currency">$27</span><span class="pricing__period">/yr</span></div>
			<button class="button button-primary large pricing__action"><a href="https://www.icegram.com/?buy-now=19926&qty=1&with-cart=1&utm_source=ig_inapp&utm_medium=ig_plus&utm_campaign=ig_upgrade" target="_blank">Sign Up</a></button>
			<div class="pricing__headline">Everything in Free and:</div>
			<ul class="pricing__feature-list">
			 	<li class="pricing__feature">+ 1 Site license</li>
			 	<li class="pricing__feature">+ Impression vs Conversion report</li>
			 	<li class="pricing__feature">+ Top 5 message stats</li>
			 	<li class="pricing__feature">+ Top 5 campaigns stats</li>
			</ul>
		</div>
		<div class="pricing__item pricing__item--featured">
			<h3 class="pricing__title">Pro</h3>
			<p class="pricing__sentence">For reducing abandonments</p>
			<div class="pricing__price"><span class="pricing__currency">$97</span><span class="pricing__period">/yr</span></div>
			<button class="button button-primary large pricing__action"><a href="https://www.icegram.com/?buy-now=16522&qty=1&coupon=&with-cart=1&utm_source=ig_inapp&utm_medium=ig_pro&utm_campaign=ig_upgrade" target="_blank">Sign Up</a></button>
			<div class="pricing__headline">Everything in Plus and:</div>
			<ul class="pricing__feature-list">
			 	<li class="pricing__feature">+ 1 Site license</li>
			 	<li class="pricing__feature">+ Exit Intent Targeting</li>
			 	<li class="pricing__feature">+ After CTA Click Control</li>
			 	<li class="pricing__feature">+ Additional 17 Themes</li>
			 	<li class="pricing__feature">+ Scroll popup</li>
			 	<li class="pricing__feature">+ Inline message</li>
			 	<li class="pricing__feature">+ Stickies</li>
			 	<li class="pricing__feature">+ Ribbons</li>
			 	<li class="pricing__feature">+ Badges</li>
			</ul>
		</div>
		<div class="pricing__item">
			<h3 class="pricing__title">Max</h3>
			<p class="pricing__sentence">For increasing sales & customers</p>

			<div class="pricing__price"><span class="pricing__currency">$147</span><span class="pricing__period">/yr</span></div>
			<button class="button button-primary large pricing__action"><a href="https://www.icegram.com/?buy-now=16542&qty=1&coupon=&with-cart=1&utm_source=ig_inapp&utm_medium=ig_max&utm_campaign=ig_upgrade" target="_blank">Sign Up</a></button>
			<div class="pricing__headline">Everything in Pro and:</div>
			<ul class="pricing__feature-list">
			 	<li class="pricing__feature">+ 3 Site license</li>
			 	<li class="pricing__feature">+ A/B Testing</li>
			 	<li class="pricing__feature">+ Geographical Targeting</li>
			 	<li class="pricing__feature">+ Optin Entry Animations</li>
			 	<li class="pricing__feature">+ Optin Exit Animations</li>
			 	<li class="pricing__feature">+ Pack of 24 Pro Themes</li>
			 	<li class="pricing__feature">+ Overlay</li>
			 	<li class="pricing__feature">+ Tab</li>
			 	<li class="pricing__feature">+ Sidebar</li>
			 	<li class="pricing__feature">+ Interstitial</li>
			</ul>
		</div>
	</div>
</section>
</body>
</html>
