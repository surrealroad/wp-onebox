=== Onebox ===
Tags: links, prettifying, hyperlinks
Stable tag: 0
Tested up to: 3.7.1

A Fancy Hyperlink Display Plugin for WordPress

== Description ==

What does this plugin do?
--
This plugin lets you use a shortcode `[onebox url="http://example.com"]` in place of a link that will display a lovely Facebook/Twitter-style box with additional information about the link

The source is maintained on GitHub: https://github.com/surrealroad/wp-onebox

Currently supported sites
--
* Any web page with Twitter metadata
* Any web page with Facebook (OpenGraph) metadata

Note
--
This plugin renders affiliate links for some websites in order to support its development. You are more than welcome to fork or modify the source if you don't want to allow this.


TODO:

Additional credits
- Inspired by the Onebox module from Discourse (http://www.discourse.org/)
- OpenGraph
- AmazonECS Class
- Encoding
- html5lib

== Installation ==

1. Install the plugin as you would any other WordPress plugin and enable it
2. Go to Settings > Onebox
3. Change settings as required
4. Add a shortcode in the form `[onebox url="https://anything.com"]`

== Frequently Asked Questions ==

= Can I see a preview of how links are rendered? =

Sure, you can see an example at the bottom of the settings page for the plugin

= Will this slow down page loads? =

It *shouldn't*. WordPress will initially render the link as a boring hyperlink, which will then get rendered as a onebox via AJAX after the page has loaded.

== Changelog ==
= 0.5 =
* First WordPress release