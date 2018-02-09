=== Simple Job Board ===
Contributors: PressTigers
Donate link: http://www.presstigers.com
Tags: job board, career, job listing, job manager, job portal
Requires at least: 4.4
Tested up to: 4.9
Stable tag: 2.4.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Powerful & robust plugin to create a job board on your website in a simple & elegant way.

== Description ==

The plugin is available in English, French(Français), Arabic (العربية), Brazilian Portuguese(Português do Brasil), Italian(Italiano), Russian(Русский), Chinese(简体中文), Dutch(Nederlands), Serbian(Српски језик) and Swedish(Svenska).

= Looking for an easy, user-friendly and robust Job board plugin? = 
Simple Job Board by <a href="http://www.presstigers.com">PressTigers</a> is an easy, light weight plugin that adds a job board to your WordPress website. 
This plugin is extendible and easy to use. A customized job board is created to manage various job offers via Wordpress with the Simple Job Board. You can add multiple job listings and can show them on any page by inserting [jobpost] shortcode. You can add multiple job features and customized application forms for every distinct job listing. You can also add notes to an application right from the dashboard.

= Live Demo =
Please click here for [Simple Job Board Demo](http://demo.presstigers.com/job-board-extensions).

= Simple Job Board Add-ons =

* [Multiple Attachment Fields](http://market.presstigers.com/product/multiple-attachment-fields-add-on)
* [Email Application](http://market.presstigers.com/product/email-application-add-on)
* [How to Apply](http://market.presstigers.com/product/how-to-apply-add-on/)
* [SJB Application to PDF](http://market.presstigers.com/product/job-application-to-pdf-add-on/)
* [Job Board CAPTCHA](http://market.presstigers.com/product/job-board-captcha-add-on)
* [Company Details & Filter](http://market.presstigers.com/product/company-filter-add-on)
* [Job Industry Filter](http://market.presstigers.com/product/job-industry-filter-add-on)
* [Job Level Filter](http://market.presstigers.com/product/job-level-filter-add-on)
* [Content Replacement](http://market.presstigers.com/product/extended-support)
* Not in the list, for custom add-on please [contact us](http://market.presstigers.com/contact-us)

= Plugin Features =

*  Add, categorize and manage all jobs using the granular WordPress User Interface.
*  Allow job listers to add job types in job listings.
*  Add job location to an individual job created.
*  Add category shortcode to any post to enlist job listing of that particular category.
*  Add job Location to any post by using specified shortcode.
*  Add Job Type to any post by using specified shortcode.
*  Add a combination of multiple shortcodes for a job listing.
*  Use the Anti-hotlinking option to enhance the security of your documents.
*  Upload documents in various extensions.	
*  View Applicants' list who applied for a particular job.
*  Set job listing, job features, application form, filters and email notifications for a job through global settings.

For more plugin documentation, see [other notes](https://wordpress.org/plugins/simple-job-board/other_notes) section.

= Can you contribute? =
If you are an awesome contributor for translations or plugin development, please contact us at support@presstigers.com

== Credits ==
* International Telephone Input(http://intl-tel-input.com)
* Google Fonts(https://fonts.google.com)
* jQuery UI(https://jqueryui.com)
* WP Color Picker Alpha(https://github.com/23r9i0/wp-color-picker-alpha)

== Configurations & Templating ==

= Follow the following steps for a fully functional Job Board: =
1. After installation, go to "Job Board" menu in the admin panel, and add a new job listing.
1. Add multiple job features and a fully customized application form right from the job listing editor.
1. To list all the job listings and start receiving applications, add [jobpost] shortcode in an existing page or add a new page and write shortcode anywhere in the page editor.
1. After someone fills an application form from the front-end, you will receive it right in the dashboard.
1. You can add special notes to an application by opening its detail page.

= Job Board Templating = 

The job board templating feature allows you to change the following file templates.

* content-job-listing-grid-view.php
* content-job-listing-list-view.php
* content-single-job-listing.php
* single-jobpost.php
* archive-jobpost.php
* job-filters.php 
* search/keyword-search.php
* search/category-filter.php
* search/location-filter.php
* search/type-filter.php
* search/search-btn.php
* listing/content-no-jobs-found.php
* listing/listing-wrapper-start.php
* listing/listing-wrapper-end.php
* listing/job-listings-start.php
* listing/job-listings-end.php
* listing/job-pagination.php
* listing/grid-view or list-view/company.php
* listing/grid-view or list-view/job-title-company.php
* listing/grid-view or list-view/location.php
* listing/grid-view or list-view/logo.php
* listing/grid-view or list-view/posted-date.php
* listing/grid-view or list-view/short-description.php
* listing/grid-view or list-view/title.php
* listing/grid-view or list-view/type.php
* single-jobpost/job-application.php
* single-jobpost/job-features.php
* single-jobpost/job-meta/company-logo.php
* single-jobpost/job-meta/company-name.php
* single-jobpost/job-meta/company-tagline.php
* single-jobpost/job-meta/job-location.php
* single-jobpost/job-meta/job-posted-date.php
* single-jobpost/job-meta/job-title.php
* single-jobpost/job-meta/job-type.php
* widget/job-widget-start.php
* widget/job-widget-end.php
* widget/content-recent-jobs-widget.php


1. To change a template, please add "simple_job_board" folder in your activated theme's root directory.
1. Add above mentioned file from plugin simple-job-board >templates folder keeping the same file directory structure and do whatever you want.

Enjoy your work with Simple Job Board templating.

== Installation ==

1. Download plugin.
1. Upload `simple-job-board.zip` to the `/wp-content/plugins/` directory to your web server.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Add a standard WordPress page or post and use the [jobpost] shortcode in the editor to make it a Job Board.

== Frequently Asked Questions ==

= How to create a job listing? =
In your WordPress admin panel, go to "Job Board" menu and add a new job listing. All the job listings will be shown in the admin panel and on the front-end.

= How to show job listings on the front-end? = 
To list all the job listings and start receiving applications, add [jobpost] shortcode in an existing page or add a new page and write shortcode anywhere in the page editor.

= Job Page Expands Across Entire Page =
It's container class naming issue. We can't set all websites container classes because every website has its own CSS and naming conventions.

So, we are giving the facility to Job Board's users for adding container class or Id under Settings> Appearance tab. Please add your website container class in "Job Board Container Class:" under Job Board> Settings> Appearance tab.

= Where can I assign global settings for same job posts? =  
You can assign global job listing settings to each job post through settings.

= How can I add company information for a job post? = 
Once you are in new job page, you can add company information in job data section.

= Can I upload a resume with different extensions? = 
Yes, you can upload a resume document with .pdf, .odt, .txt, .rtf, .doc, .docx extensions from the settings page.

= Can I show only 5 latest jobs on front-end with pagination? = 
Yes, you can show any number of posts on your website with pagination feature by using shortcode with "posts" attribute i.e. [jobpost posts="5"]

= Can I show job listings for particular "Category" using a shortcode? = 
Yes, you can use a shortcode on post page i.e. [jobpost category="category-slug"]

= Can I show job listings for particular "Type" using a shortcode? = 
Yes, you can use a shortcode on post page i.e. [jobpost type="type-slug"]

= Can I show job listings for particular "Location" using a shortcode? = 
Yes, you can use a shortcode on post page i.e. [jobpost location="location-slug"]

= Can I use combination for various shortcodes to display job listings? = 
Yes, you can use various combinations of shortcodes with spaces i.e. [jobpost location="location-slug" category="category-slug" type="type-slug"]

= How Can I view the Applicant list for a Job Post? = 
In your WordPress admin panel, go to "Job Board" menu and "Applicants" section

= Where can I find more information about Simple Job Board? =  
You can visit <a href="http://www.presstigers.com">PressTigers Website</a> or <a href="http://www.presstigers.com/blog">blog</a> page.

== Screenshots ==

1. **Job Board Creation** - Allow users to create a Job Board with ease by using a shortcode.
2. **Job Categories** - Categorize your similar jobs under a group of categories with the help of a shortcode.
3. **Job Type** - This allows users to specify the type of jobs you offer to them.
4. **Job Location** - Let your users create jobs according to a certain demographic location.
5. **List of Applicants** - You can get all the applicants applied for jobs over here, by clicking each of applicant you can get further details and download the resume.
6. **Application Notes** - This section helps site administrators to add additional notes to received Resumes.
7. **General** - Allow users to change the slug of custom post types and taxonomy.
8. **Appearance** - Let your users change view & typography of job listing on front-end.
9. **Job Features** - Allow your users to add their own set of features to a job listing or a single job post.
10. **Application Form Fields** - This will help in creating customized job form.
11. **Filters** - Give your users complete control over job listing filters.
12. **Email Notifications** - This section will enable various notification options for users.
13. **File Extensions** - Upload Documents with various extensions along with document security.
14. **Modified Job Listing List View** - Modified Front end view of a job listing. 
15. **Job Listing Grid View** - Added Grid view of Front end job listing. 
16. **Modified Job details page** - Job board detail/single page. Job features and job application form is placed on it.

== Changelog ==

= 2.4.6 =
WP 4.9 Compatibility - Resolved the color picker issue in settings's appearance tab.
Fix - Resolved the container class uppercase naming issue. 
Fix - Added missing parameters for archive page pagination template. 
Fix - Fixed the SSL certification issues for telinput.

= 2.4.5 =
Fix - Resolved the resume downloading issue.
Fix - Fixed the job application form builder issue with multilingual characters.
Fix - Added website Home URL instead of WordPress Site Address in email template.
Tweak – Revised French translation.
Tweak - Added post id as a parameter in the filter hooks of email notification templates for developers.
Tweak - Breakdown the HR, Amin and Applicant notification templates into modules for developers usage to make customizations easy.
Tweak - Introduced new filters in email notification templates.

= 2.4.4 =
* Feature - Added translations for Serbian and Swedish.
* Tweak - Modified the job posted date templates for making it easily customizable for developers.
* Fix - Resolved the "File not found." error while using theme and plugin editor.
* Fix - Verified the user authentication before downloading a resume.
* Fix - Fixed reflected XSS attack issues in case of keyword search.
* Fix - Removed the duplicate arrows of job filters' selects in Internet Explorer.

= 2.4.3 =
* Feature - Translated email templates for Dutch( Nederlands ).
* Feature - Added a widget for displaying recent jobs.
* Feature - Enable/Disable SJB plugin fonts from Appearance settings.
* Tweak - Improved the uploaded files(resume) security by restricting file's direct link access.
* Fix - Resolved the CSS conflicts.
* Fix - Resolved the long pagination display issue.
* Fix - Added missing datepicker scroll arrows.
* Fix - Dimmed color for phone number placeholder.
* Note - Updated .pot file.

= 2.4.2 =
* Fix - Resolved the job listing & detail page layout issues.
* Fix - Fixed the job listing page layout problem with the content of WP page/post editor.

= 2.4.1 =
* Fix - Fixed the fatal errors in Settings sections.

= 2.4.0 =
* Feature - Added email notification From & Reply-to parameters. 
* Tweak - Revised the whole HTML structure.
* Tweak - Revised French translation.
* Fix - Resolved the search filters issue with custom permalinks structure.
* Fix/Tweak - Resolved applicant name issue with multilingual by providing a solution with an additional field on SJB form builder( Expose in Applicant Listing ).
* Fix - Resolved cross-browser compatibility issue for datepicker.
* Fix - Improved user experience on company logo & details hide/show for both job listing & detail pages.
* Note - Updated .pot file.
* Note - Improved plugin security.

= 2.3.2 =
* Feature - Added label editable feature to job features and application form sections.
* Feature - Logo enable/disable settings for job detail page.
* Note - Added SJB add-ons listing section.
* Tweak - Applied more filters for email notifications setting.
* Fix - Resolved the job feature label auto capitalization problem.

= 2.3.1 =
* Fix - Resolved appearance settings issue.
* Fix - Resolved CSS float property issue.

= 2.3.0 =
* Feature - Introduced plugin level templating.
* Feature - Added support for RTL languages.
* Feature - Added translation for Dutch.
* Feature - Added more filters for email notifications.
* Tweak - Resolved permalinks reset issue on plugin activation.
* Tweak - Revised notification templates for Applicant, Admin, and HR.
* Tweak - Revised Job Board CSS for optimized typography.
* Tweak - Optimized validation for resume upload.
* Fix - Resolved cross-browser compatibility issue for admin field types dropdown.
* Fix - Resolved required validation issue of multiple checkboxes.
* Fix - Resolved the application form label auto capitalization problem.

= 2.2.4 =
* Fix - Resolved the pagination issue with the job search.
* Fix - Resolved the required field validation issue in case of radio button and checkbox.
* Tweak - Revised the uploaded resume validation.
* Tweak - Added complete field types dropdown in application form's settings.
* Tweak - Added pagination as the default behavior of job listing.

= 2.2.3 =
* Feature - Added "sjb_applicant_details_notification" filter in email template.
* Feature - Added "sjb_category_filter_dropdown_after" & "sjb_job_type_filter_dropdown_after" action hooks in job filter template.
* Feature - Made compatible with other plugin's shortcode in job editor.
* Feature - Job listing and detail page typography settings under Appearance tab.
* Feature - Added translation for Chinese.
* Feature - Added Job Board shortcode generator in TinyMCE editor.
* Tweak - Updated anti-hotlinking rules for more specific to job board uploaded data.
* Tweak - Set phone input default country flag to user's country based on their IP address.
* Tweak - Revised the job detail page template parts directory structure. 
* Fix - Resolved the Job board CSS conflicting issue with phone field country flag listing.    
* Fix - Resolved the uploaded file path and URL issues.
* Fix - Resolved the company logo uploading issue on WP admin job detail page.
* Fix - Resolved the HTML structure breaking issue.
* Fix - Resolved checkboxes missing data issue on applicant detail page.

= 2.2.2 =
* Fix - Resolved the single-jobpost.php templating issue.     
* Feature - Added Settings for job board templates container class and id.  

= 2.2.1 =
* Fix - Resolved the website sidebar break issue.     
* Fix - Resolved the CSS conflict issue.    
* Fix - Loaded the job board resources(CSS & JavaScript) only to job board pages.     
* Fix - Updated missing translations in Arabic, French, Italian,Russian and Portuguese languages.   
* Fix - Added missing Hooks and Filters.

== Upgrade Notice ==
= 2.4.6 =
2.4.6 is a minor release with WP 4.9 compatability.