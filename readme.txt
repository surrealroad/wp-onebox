=== Onebox ===
Tags: links, prettify, hyperlinks, itunes, steam, gog.com, github, opengraph, twittercard, embed
Stable tag: 0.5
Tested up to: 3.8

Onebox: A Fancy Hyperlink Display Plugin for WordPress

== Description ==

What does this plugin do?
--
This plugin lets you use a shortcode `[onebox url="http://example.com"]` in place of a link that will display a lovely Facebook/Twitter-style box with additional information about the link.

WordPress has [built-in support](http://en.support.wordpress.com/twitter/twitter-embeds/) for some links like Google+ or Twitter, where it will generate a nice-looking embed from a link or shortcode, but what about other sites? Onebox aims to let you turn any link into an embedded box, with extra features for some special sites, and with a fully customisable HTML template.

How does the plugin work?
--
Many webpages now have metadata in their header with a desciption and image to help sites like Twitter or Facebook generate embeds. Onebox can read this same data to generate similar embeds. In addition, the plugin makes use of a number of "parsers" to scan for specific sites and pull out even more data to use.

The source is maintained on GitHub: https://github.com/surrealroad/wp-onebox

Requirements
--
Requires the cURL extension for PHP (http://www.php.net/manual/en/book.curl.php)

The following modules are optional, but highly recommended:
* APC extension for PHP
* GeoIP extension for PHP

Currently supported sites
--
* Any web page with Twitter metadata
* Any web page with Facebook (OpenGraph) metadata
* GitHub (github.com)
* Steam (store.steampowered.com)
* iTunes (itunes.com)
* GOG (gog.com)
* Mac Game Store (macgamestore.com)
many more to follow

Note
--
This plugin renders affiliate links for some websites in order to support its development. You are more than welcome to fork or modify the source if you don't want to allow this.


TODO:
* Comply with WordPress security guidelines
* Add more parsers

Additional credits
* Inspired by the "Onebox" module from Discourse (http://www.discourse.org/)
* [Open Graph Protocol helper for PHP](https://github.com/scottmac/opengraph)
* [Amazon ECS PHP Library](https://github.com/Exeu/Amazon-ECS-PHP-Library)
* [forceutf8](https://github.com/neitanod/forceutf8)
* [html5lib - php flavour](https://github.com/html5lib/html5lib-php)
* Default template based on "Facebook Notify Widget" by Pixels Daily and [GitHub-jQuery-Repo-Widget](https://github.com/JoelSutherland/GitHub-jQuery-Repo-Widget)

== Installation ==

1. Install the plugin as you would any other WordPress plugin and enable it
2. Go to Settings > Onebox
3. Change settings as required
4. Add a shortcode in the form `[onebox url="https://anything.com"]`

== Frequently Asked Questions ==

= Can I see a preview of how links will be rendered? =

Sure, you can see an example at the bottom of the settings page for the plugin once installed (although hyperlink colours will be based on your theme and not previewed).
For a live preview, see http://blog.surrealroad.com/archives/2013/introducing-onebox-for-wordpress/

= Will this slow down page loads? =

It *shouldn't*. WordPress will initially render the link as a boring hyperlink, which will then get rendered as a onebox via AJAX after the page has loaded. Actually turning the links into boxes happens asynchronously, and it's highly recommended that you install the APC extension for PHP to reduce the load on your server, as well as the servers you're linking to.

= How can I request extra features for whatever.com =

The best way is to [open a ticket at GitHub](https://github.com/surrealroad/wp-onebox/issues/new). Bonus points if the site in question has a well-documented API.

== Screenshots ==
1. Example Onebox for itunes.com using default style
2. Example Onebox for github.com using dark style
3. Plugin admin options screen
4. Example Onebox for gog.com

== Changelog ==
= 0.x =
* Improvements to iTunes parser
* Fix for caching with detected localisations

= 0.5 =
* First public WordPress release