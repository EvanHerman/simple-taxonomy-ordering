=== Simple Taxonomy Ordering ===
Contributors: yikesinc, eherman24, liljimmi, yikesitskevin
Tags: admin, term, meta, simple, order, taxonomy, metadata, termmeta, reorder
Requires at least: 4.4
Tested up to: 4.9.5
Stable tag: 1.2.7

Quickly and easily reorder taxonomy terms with an easy to use and intuitive drag and drop interface.

== Description ==

Order all of the taxonomy terms on your site with a simple to use, intuitive drag and drop interface. The plugin works for WordPress core taxonomies -- Categories and Tags -- and any custom taxonomies you have created.

Activate the plugin, enable your taxonomy on the settings page, and drag and drop the taxonomies into the desired position. It couldn't be easier.

On the front end of the site your taxonomy terms will display in the order set in the dashboard.

Integrates well with <a href="https://wordpress.org/plugins/easy-digital-downloads/">Easy Digital Downloads</a> and <a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a>, allowing you to re-order product categories and terms.

We've also built in support within the plugin itself. On the edit taxonomy page, click on the 'Help' tab in the top right of the screen to display additional help.

<strong>Requires WordPress 4.4 or later, due to the use of the term meta.</strong>

== Other Notes ==

**Filters**

* yikes-mailchimp-form-title-FORM_ID - alter the output of the form title of the specified form

**Query Usage**

* If you're trying to query for taxonomy terms (e.g. using `WP_Query` or functions like `get_terms()`), and you'd like them to be returned in the order specified by the plugin, you need to add the tax_position parameter in your call. For example: `'meta_key' => 'tax_position'` and  `'orderby' => 'tax_position'`. Thanks to @certainlyakey on GitHub for pointing this out.

== Screenshots ==

1. Simple Taxonomy Ordering settings page, allows you to specify which taxonomy you want to enable drag & drop ordering on.

== Installation ==

* Unzip and upload contents to your plugins directory (usually wp-content/plugins/).
* Activate the plugin.
* Head to the settings page, 'Settings > Simple Tax. Ordering'.
* Select the taxonomies you want to enable drag and drop ordering on. Save the settings.
* Head to the taxonomy edit page and re-order the taxonomies as needed.
* Profit

== Changelog ==

= 1.2.7 =
* Added some variable checks to prevent PHP Notices.

= 1.2.6 =
* Changed the global (localized) JS variable from `localized_data` to `simple_taxonomy_ordering_data` to avoid any potential conflicts.

= 1.2.5 =
* Fixed an issue where terms weren't being returned if the termmeta table was empty. A big thanks to @doppiogancio on GitHub for finding this and helping us reach the solution.

= 1.2.4 = 
* Fixed a JS issue that occurs when HTML is added to category description. A big thanks to @mateuszbajak for finding this and fixing it!

= 1.2.3 = 
* Fixed a bug where the same SQL join statement was being added to a query twice on the front end (props to @burisk for calling this out)

= 1.2.2 = 
* Added a CAST call to order taxonomies as integers instead of strings (props to Timothy Couckuyt / @devplus_timo for calling this out)

= 1.2.1 =
* Removed the `disableSelection()` call to allow selection of quick edit fields

= 1.2 =
* Added i18n: added domain path, languages folder, .pot file, and load_text_domain() hook

= 1.1 =
* Reverted query, added missing ORDER BY argument.

= 1.0 =
* Altered the query run when ordering terms (Props to Daniel Schwab for the [pull request](https://github.com/yikesinc/yikes-inc-simple-taxonomy-ordering/pull/2).

= 0.1 =
* Initial release
