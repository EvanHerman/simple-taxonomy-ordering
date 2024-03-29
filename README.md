<h1 align="center">Simple Taxonomy Ordering
	<a href="https://github.com/EvanHerman/simple-taxonomy-ordering/releases/latest/">
		<img src="https://img.shields.io/static/v1?pluginVersion=&message=v2.3.4&label=&color=999&style=flat-square">
	</a>
</h1>

<h4 align="center">Quickly and easily reorder taxonomy terms with an easy to use and intuitive drag and drop interface.</h4>

<p align="center">
	<a href="https://github.com/EvanHerman/simple-taxonomy-ordering/actions/workflows/phpunit.yml?query=branch%3Amaster" target="_blank">
		<img src="https://github.com/EvanHerman/simple-taxonomy-ordering/actions/workflows/phpunit.yml/badge.svg?branch=master">
	</a>
	<a href="https://github.com/EvanHerman/simple-taxonomy-ordering/actions/workflows/wpcs.yml?query=branch%3Amaster" target="_blank">
		<img src="https://github.com/EvanHerman/simple-taxonomy-ordering/actions/workflows/wpcs.yml/badge.svg?branch=master">
	</a>
</p>

<p align="center">
	<a href="https://codeclimate.com/github/EvanHerman/simple-taxonomy-ordering/maintainability">
		<img src="https://api.codeclimate.com/v1/badges/82ec3b9e928a60ba91d2/maintainability" />
	</a>
	<a href="https://codeclimate.com/github/EvanHerman/simple-taxonomy-ordering/test_coverage">
		<img src="https://api.codeclimate.com/v1/badges/82ec3b9e928a60ba91d2/test_coverage" />
	</a>
</p>

<p align="center">
	<a href="https://wordpress.org/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=4.4+-+6.0&color=blue&style=flat-square&logo=wordpress&logoColor=white" alt="WordPress Versions">
	</a>
	<a href="https://www.php.net/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=5.6+-+8.0&color=777bb4&style=flat-square&logo=php&logoColor=white" alt="PHP Versions">
	</a>
</p>

Installation
===========
1. Install and activate the plugin
2. If you need to enable term sorting on default WordPress taxonomies, please see below.
3. If you would like to enable taxonomy term sorting on custom post type taxonomies, please see below.
4. Once enabled, you can drag & drop re-order your taxonomy terms. Whenever '[get_terms()](https://developer.wordpress.org/reference/functions/get_terms/)' is used to display your terms, they will display in the order you've set.

Usage
===========

#### Default WordPress Taxonomies
After installing and activating the plugin, you have two options. You can enable drag & drop on any of the default taxonomies. To enable drag & drop sorting on default WordPress taxonomies, you'll want to assign the `tax_position` parameter to the register_post_type call.

The easiest way to do so, is to use the following [snippet](https://gist.github.com/EvanHerman/4e83fda88d2b210dce95).

```php
/*
* Enable drag & drop sorting on default WordPress taxonomies (ie: categories) - (page/post)
*/
add_filter( 'register_taxonomy_args' , 'add_tax_position_support', PHP_INT_MAX, 3 );
function add_tax_position_support( $args, $taxonomy, $object_type ) {
	if( 'category' == $taxonomy ) { // Change the name of the taxonomy you want to enable drag&drop sort on
		$args['tax_position'] = true;
	}
	return $args;
}
```

#### Custom Taxonomies
Alternatively, if you've defined a custom taxonomy that you'd like to allow drag & drop sorting on, you'll want to pass in a `tax_position` parameter to the `$args` array inside of [register_taxonomy](https://codex.wordpress.org/Function_Reference/register_taxonomy). You can place this line directly after `'hierarchical'`.

[Example Snippet](https://gist.github.com/EvanHerman/170e2a46db4cecdeb607)

`'tax_position' => true,`


#### Front End
On the front end of the site, anywhere [get_terms()](https://developer.wordpress.org/reference/functions/get_terms/) is used to query a set of taxonomy terms, they will be returned in the order of their position on the taxonomy list. No additional steps need to be taken on on your end.

Example
=========
![Admin Taxonomy Sorting Usage](https://cldup.com/bFZrQxtCPT.gif)


Frequently Asked Questions
===========

### Can I make default WordPress taxonomies drag and drop sortable?

Indeed, you can! You'll have to assign the `'tax_position'` parameter to the taxonomy. You can do this easily, using the following [sample code snippet](https://gist.github.com/EvanHerman/4e83fda88d2b210dce95).

**You'll notice in the code snippet, the taxonomy we are using is 'category' - but you can change this value to suit your needs.**

### I have a custom post type, but it won't let me drag and drop sort it's taxonomies. How come?

As mentioned above, the taxonomies need to have the parameter	`'tax_position' => true` assigned to it. If the taxonomy is missing this parameter the items won't actually be sortable. For an example of how to implement it, please see the following [code snippet](https://gist.github.com/EvanHerman/170e2a46db4cecdeb607).

### How does the taxonomy know what order to remain in?

With the release of WordPress 4.4 came taxonomy meta data, which gets stored inside of the `wp_termmeta` table in the database. Each taxonomy is assigned an integer value related to it's position on the taxonomy list.

Filters
===========
* `yikes_simple_taxonomy_ordering_capabilities` - Filter to adjust who can access the 'Simple Taxonomy Ordering' settings page.
* `yikes_simple_taxonomy_ordering_excluded_taxonomies` - Filter to add additional taxonomies or remove default taxonomies. Items in this array will **not** be displayed in the dropdown on the settings page, and thus cannot have drag and drop sorting enabled.

Issues
===========
If you're running into any issues, we would love to hear about it. Please head over to the [Simple Taxonomy Ordering Issue Tracker](https://wordpress.org/support/plugin/simple-taxonomy-ordering/) and create a new issue.

_________________

<div align="center" style="font-weight: bold;">Originally built with <span style="color: #F3A4B2;">&hearts;</span> by YIKES Inc. in Philadelphia, PA.<br />Now Maintained by Evan Herman in Lancaster, PA.</div>
