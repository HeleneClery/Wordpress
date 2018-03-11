=== Argent ===

Contributors: automattic
Requires at least: 4.1
Tested up to: 4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Argent Theme, Copyright 2016 Automattic
Argent is distributed under the terms of the GNU GPL

== Description ==

Argent is a clean and modern portfolio theme, geared towards creative professionals like designers, artists, and photographers. With its simple homepage template featuring portfolio projects, Argent aims to draw viewers right at what matters most: your wonderful work.

== Installation ==

1. In your admin panel, go to Appearance > Themes and click the Add New button.
2. Click Upload and Choose File, then select the theme's .zip file. Click Install Now.
3. Click Activate to use your new theme right away.

== Frequently Asked Questions ==

= How to setup the front page like the demo site? =

The demo site URL: http://argentdemo.wordpress.com/?demo

When you first activate Argent, your homepage will display posts in a traditional blog format. If you'd like to use the Front Page Template instead, follow these steps:

1. Create or edit a page, and then assign it the Front Page Template from the Page Attributes module.
2. Go to Settings > Reading and set "Front page displays" to "A static page".
3. Select the page you just assigned the Front Page template to as "Front page" and then choose another page as "Posts page" to serve your blog posts.

Now that you have set your home page, you can start customizing by navigating to Customize → Theme Options.

The Front Page Template consists of two sections: Static Page and Portfolio.

Full Front Page setup instructions can be found at https://theme.wordpress.com/themes/argent/

= How to set up Portfolio? =

Argent takes advantage of the Jetpack's Portfolio feature (http://jetpack.me/support/custom-content-types/), offering unique layouts and organization for your portfolio projects. To add a project, go to Portfolio → Add New in your WP Admin dashboard.

# Projects #

You can include a full-width image carousel in your projects. Argent will take the first gallery in your project and turn it into a carousel automatically. To set it up, follow these steps:

1. Create a new Portfolio project.
2. Insert an image gallery into project content. The placement of gallery inside project content does not matter, the gallery will always be displayed directly below project title.
3. Continue adding content to your project – you can add more images, pull quotes, videos etc.
4. Be sure to add featured image to your projects. Although it won’t be displayed in single project view, it’s used on the portfolio archives page (see below).
5. Save or publish your project.

# Portfolio archives page #

All projects are displayed on the portfolio archive page in grid layout. This page can be added to a Custom Menu using the Links Panel.

The portfolio archive page can be found at http://mygroovysite.wordpress.com/portfolio/ — just replace http://mygroovysite.wordpress.com/ with the URL of your website.


== Quick Specs (all measurements in pixels) ==

1. The main column width is 660, except in single posts, where it’s 490.
2. A widget in the Footer Widget Area is 300.
3. Featured Images for posts should be at least 660 wide.

== Credits ==

* slick.js script (https://github.com/kenwheeler/slick) by Ken Wheeler distributed under the MIT license.
* Screenshot image by Unsplash (http://pixabay.com/en/macbook-notebook-apple-inc-336651/), licensed under CC0 Public Domain (http://creativecommons.org/publicdomain/zero/1.0/deed.en)

== Changelog ==

= 2 March 2018 =
* Use wp_kses_post rather than wp_filter_post_kses.

= 26 July 2017 =
* Rework '[comment author] says' string to include a translation function, and replace wp_kses_post with just wp_kses and specific markup.

= 21 July 2017 =
* sanitize output after regex replacement.

= 27 June 2017 =
* Centring icons in social media widget, and changing opacity when hovered over.

= 31 May 2017 =
* Updating blockquote styles so they're not applied to the Content Form confirmation message.

= 28 March 2017 =
* Fix padding on menu items for RTL
* Update menu-toggle icon for RTL.

= 22 March 2017 =
* add Custom Colors annotations directly to the theme
* move fonts annotations directly into the theme

= 27 February 2017 =
* Adjusting mobile menu styles to prevent line break between second level menu items and their bullet points.

= 2 February 2017 =
* Add forgotten context and gettext function around comma separators for translators.

= 25 January 2017 =
* Replace get_the_tag_list() with the_tags() for a more straightforward approach that prevents potential fatal errors.

= 20 January 2017 =
* Fix page-content pseudo element alignment.

= 18 January 2017 =
* Make sure .entry-header is display block.

= 17 January 2017 =
* Add new grid-layout tag to stylesheet.

= 7 September 2016 =
* Correct typos.

= 1 July 2016 =
* Switch top position from ems to %, so it's not affected by Custom Font sizes.

= 29 June 2016 =
* Update Headstart featured image URLs.

= 22 June 2016 =
* Fix Home menu position.

= 21 June 2016 =
* Correct character escaping in Headstart annotation.

= 14 June 2016 =
* This theme "outdents" aligned images, which leads to overlapping if multiple aligned images are used in succession. Set a zero margin on subsequent aligned left or right images.

= 9 June 2016 =
* Add missing semicolons
* Update Portfolio Featured Image function so it has the same style as Portfolio Title and Portfolio Content functions
* Update Portfolio CPT with new theme option

= 8 June 2016 =
* Add support for Portfolio CPT new feature

= 23 May 2016 =
* Update copyright dates
* Use solid element instead of font to display slider's bullet points.

= 12 May 2016 =
* Add new classic-menu tag.

= 5 May 2016 =
* Fix link and formatting in 'No projects found' message.
* Move annotations into the `inc` directory.

= 4 May 2016 =
* Move existing annotations into their respective theme directories.

= 27 April 2016 =
* Add default value for front page portfolio header;

= 12 April 2016 =
* Swap screenshot.jpg for .png.

= 11 April 2016 =
* Update screenshot.

= 9 March 2016 =
* Style subscripting and superscripting text.

= 25 February 2016 =
* Add blog-excerpts tag.

= 21 January 2016 =
* Make sure site title doesn't overflow, especially on small screens.

= 2 December 2015 =
* Chaning background color of header area to black with transparency, when user selects custom background color to match Customizer preview with front-end for users without Custom Design; See #3536;

= 30 November 2015 =
* Improve display of portfolio shortcode.

= 10 November 2015 =
* Fix bug to ensure sub-menu items work on touch devices

= 29 October 2015 =
* fix SVN properties.

= 2 October 2015 =
* Enqueue Genericons and add them to fix a self-hosted bug with the Menu icon.

= 21 September 2015 =
* Ensure that site title still looks good when spanning two lines.

= 9 September 2015 =
* Add the site-title border directly to the link so it's not displayed in the customizer when site-title is hidden.

= 10 August 2015 =
* Make sure to target only size-full images when adding the extra large image class.

= 31 July 2015 =
* Remove `.screen-reader-text:hover` and `.screen-reader-text:active` style rules.

= 23 July 2015 =
* Udating readme.txt with recent changes to keep up to date with .org version;
* Adding missing styling to <b> and <strong> elements; Fixes #3279;

= 20 July 2015 =
* Refactor array for single content portfolio galleries to avoid errors for older versions of PHP. Props to @mendezcode for the fix.

= 14 July 2015 =
* Always use https when loading Google Fonts. See #3221;

= 13 July 2015 =
* Adding credit and license info for Slick script to readme.txt, as requested by .org reviewer; bumping up the version number;

= 30 June 2015 =
* Fixed minor textdomain issues; Added missing escaping; Bumped up version number to remain in sync with .org version;

= 19 June 2015 =
* Adding .pot file;

= 17 June 2015 =
* Adding escaping for commment author output;
* Fixing a bug where projects loaded with IS where breaking the grid;
* Setting 'wrapper' param for Jetapck to false, to help with jumpy scrolling;

= 16 June 2015 =
* Adding theme description to style.css and readme.txt;
* Further tweaks to editor-style.css to match recent changes in style.css; Increasing navigation width to match content width in portfolio;
* Link color adjustemnts in editor-style.css (to match recent changes in style.css); Adjustments to images - removing border below linked images and adding margin bottom;
* Adjustemtns around overhanging images;

= 15 June 2015 =
* Tying loose ends - removing overzealous esaping in comment form; Adding support for overhanging images and blocquotes to portfolio projects;
* Changing links color for better contrast; Minor tweaks to facilitate color annotations;

= 5 June 2015 =
* Adding missing escaping, in line with recent updates to _s; Changed Jetpack url to https;
* Adding post title to Read more link;
* Fixing visual issues: uneven spacing around last menu item, hover style for links in footer; , no comments message styling, edit button position in pingbacks and finally setting the height of inputs to match buttons;
* More spacing/indentation fixes;
* Minor spacing/indentation fixes;
* Minor spacing fixes in the code;
* Mixed uppercase/lowercase package names on @package declarations;
* Removing old .pot file;

= 29 May 2015 =
* CSS tweaks for color annotations;

= 28 May 2015 =
* Overdue font family change; Removing numeration from threaded comments;
* Addiing support for editor style in functions.php; Adjusting spcing after various elements;
* Adding edtor-style.css;
* Updating theme screenshot;
* Adding readme.txt file; Adding tags and updating version numver in style.css; Updating  to match recent changes in stylesheet;
* Small tweaks to RTL styles;

= 27 May 2015 =
* CSS adjustements to front page template for mobile devices;
* Custom Header Image height adjustment;
* Adding custom front page template and front page settings; Remove padding around footer widgets;
* Update slider navigation - Add next/prev navigation on hover over right/left portion of the slider;
* Adding .03em letter spacing to uppercase text;

= 26 May 2015 =
* Removing outline from buttons; increasing line-height for project titles;
* Changed body font to Cabin and adjusted font-size; Added hover state styling; Chaged focus on form elements; Various minor CSS adjustments;
* Add class to large images to outdent them visually; Update navigation links anchors;

= 22 May 2015 =
* Varius CSS tweaks to spacing;
* Adding RTL styles;
* Goodreads widget styling adjustments;
* Added styling for calendar widget; Fixed site title color bug;
* More adjustments for wpcom widgets;
* Updated custom fonts function; Added sytles to various wpcom widgets;
* Adding 'footer_widgets' argument to Jetpack setup to prevent infinite mode when footer widgets are present;
* Added pagination to single post via the_content filter to move it above Jetapck sharing and related posts; Slightly modified page links markup to fine tune the styling;
* Forgot to include wpcom.php in functions.php, fixing now;
* Replaced single footer widget area with thre separate for finer control over widget display order;
* Adding wpcom.php and style-wpcom.css files; Style adjustmetns to input fileds and widgets;

= 21 May 2015 =
* Export from dev to pub;
