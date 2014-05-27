=== Doofinder for WooCommerce ===
Contributors: doofinder
Tags: search, autocomplete
Requires at least: 3.8
Tested up to: 3.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin integrates the Doofinder search service with your WooCommerce shop.

== Description ==

Doofinder provides fast, accurate results based on your website contents.
Results appear in your search box at an incredible speed as the user types.

Doofinder can be installed in any website with very little configuration (you
give us a data feed and we give you a bit of javascript code).

This extension allows you to easily populate the data feed Doofinder needs to
be able to search your database and to insert the Doofinder layer script into
your WooCommerce site.

With Doofinder you are confident that your visitors are finding what they are
looking for regardless of the number of products in your site.

These are some advantages of using Doofinder in your site:

- Instant, relevant results.
- Tolerant of misspellings.
- Search filters.
- Increases the conversion rates.
- No technical knowledge are required.
- Allows the use of labels and synonyms.
- Installs in minutes.
- Provides statistical information.
- Doofinder brings back the control over the searches in your site to you.

When users start typing in the search box, Doofinder displays the best results
for their search. If users make typos, our algorithms will detect them and will
perform the search as if the term were correctly typed.

Furthermore, Doofinder sorts the results displaying the most relevant first.

More info: <http://www.doofinder.com>

== Installation ==

Install and activate [as any other plugin](https://codex.wordpress.org/Managing_Plugins). WooCommerce MUST be installed before activating the plugin.

= Requirements =

__Important:__ To use this plugin you need to have an account at Doofinder. If you don't have one you can signup [here](http://www.doofinder.com/signup) to get your 30 day free trial period.

The provisional minimum technical equirements are basically the same as the WooCommerce ones:

- PHP 5.3 or later
- WordPress 3.8 or later.
- WooCommerce 2.1 installed.

= Configuration =

If you're installing Doofinder for the first time you will be asked to provide a data feed during the installation process. If you have other search engines in your Doofinder account you can configure the feed URL at [_Configuration > Product Feed_](https://app.doofinder.com/admin/config/feed/).

= The Data Feed =

At this point you should be able to feed Doofinder with your store data through this plugin.

If your WordPress installation has permalinks (URL rewriting) enabled then your feed URL will look like this:

    http://www.your-site-domain.com/doofinder/woocommerce/products

In other case it will look like this:

    http://www.your-site-domain.com/?df-wc-route=products

__Notice:__ If WordPress is not installed at the root of your site then you will have to setup the URL accordingly. In the following example WordPress is installed inside a `/blog` folder:

    http://www.your-site-domain.com/blog/doofinder/woocommerce/products

= The Doofinder Layer =

Navigate to _WooCommerce > Settings_. You'll see a new tab panel called _Doofinder_. Go there.

In that section there are two things to configure:

- __Enable the Layer:__ Checking this option will attach the Doofinder Layer code to the footer of your page.
- __Layer Javascript Code:__ This is the code of the Doofinder Layer. You only have to paste the code of the layer in this text area. You can obtain your layer code from the _Configuration > Installation Scripts > Doofinder Layer_ section in the [Doofinder Control Panel](https://app.doofinder.com/admin/config/scripts/).

__And that's all! Enjoy a new search experience in your store!__

== Frequently Asked Questions ==

= I have a problem with your plugin. What can I do? =

Just send your questions to <mailto:support@doofinder.com> and we will try to answer as fast as possible with a working solution for you.

== Changelog ==

= 0.1 =
First usable version.

== Future improvements ==

- [WooCommerce Multilingual](http://wordpress.org/plugins/woocommerce-multilingual/) integration.
- Add an option to export product custom attributes in the data feed.
- UI Translation.