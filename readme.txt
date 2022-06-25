=== Simple Taxonomy Ordering ===
Contributors: yikesinc, eherman24, liljimmi, yikesitskevin, jpowersdev
Tags: admin, term, meta, simple, order, taxonomy, metadata, termmeta, reorder
Requires at least: 4.4
Tested up to: 6.0
Stable tag: 2.3.4

Quickly and easily reorder taxonomy terms with an easy to use and intuitive drag and drop interface.

== Description ==

Order all of the taxonomy terms on your site with a simple to use, intuitive drag and drop interface. The plugin works for WordPress core taxonomies -- Categories and Tags -- and any custom taxonomies you have created.

Activate the plugin, enable your taxonomy on the settings page, and drag and drop the taxonomies into the desired position. It couldn't be easier.

On the front end of the site your taxonomy terms will display in the order set in the dashboard.

<strong>Requires WordPress 4.4 or later due to the use of the term meta.</strong>

== Other Notes ==

**Query Usage**

* If you're trying to query for taxonomy terms (e.g. using `WP_Query` or functions like `get_terms()`), and you'd like them to be returned in the order specified by the plugin, you need to add the tax_position parameter in your call. For example: `'meta_key' => 'tax_position'` and  `'orderby' => 'tax_position'`. Thanks to @certainlyakey on GitHub for pointing this out.

== Screenshots ==

1. Simple Taxonomy Ordering settings page, allows you to specify which taxonomy you want to enable drag & drop ordering on.

== Installation ==

* Unzip and upload contents to your plugins directory (usually wp-content/plugins/).
* Activate the plugin.
* Head to the settings page, 'Settings > Simple Taxonomy Ordering'.
* Select the taxonomies you want to enable drag and drop ordering on. Save the settings.
* Head to the taxonomy edit page and re-order the taxonomies as needed.
* Profit

== Changelog ==

= 2.3.4 =
* Fixes custom order not being displayed on edit-tags pages.
