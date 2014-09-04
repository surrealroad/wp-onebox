=== Onebox ===
Tags: links, prettify, hyperlinks, itunes, steam, gog.com, github, opengraph, twittercard, embed, mac game store, green man gaming, origin, lynda.com, wikipedia, google play, kickstarter, xivdb, final fantasy xiv a realm reborn database, ebay
Stable tag: 1.0
Tested up to: 4.0

Onebox: A Fancy Hyperlink Display Plugin for WordPress

== Description ==

[Plugin home page](http://onebox.surrealroad.com)

What does this plugin do?
--
This plugin lets you use a shortcode `[onebox url="http://example.com" title="optional title" description="optional description"]` in place of a link that will display a lovely Facebook/Twitter-style box with additional information about the link.

WordPress has [built-in support](http://en.support.wordpress.com/twitter/twitter-embeds/) for some links like Google+ or Twitter, where it will generate a nice-looking embed from a link or shortcode, but what about other sites? Onebox aims to extend this by letting you turn any link into an embedded box, with extra features for some special sites, and with a fully customisable HTML template.

How does the plugin work?
--
Many webpages now have metadata in their header with a description and image to help sites like Twitter or Facebook generate embeds. Onebox can read this same data to generate similar embeds. In addition, the plugin makes use of a number of "parsers" to scan for specific sites and pull out even more data to use.

Where can I see it in action?
--
A demo site is set up at http://onebox.surrealroad.com

The source is maintained on GitHub: https://github.com/surrealroad/wp-onebox

Requirements
--
One of the following PHP modules are required:
* [file_get_contents](http://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen)
* [cURL](http://www.php.net/manual/en/book.curl.php)

The following modules are optional, but highly recommended:
* APC extension for PHP
* GeoIP extension for PHP

Currently supported sites
--
* Any web page with Twitter metadata
* Any web page with Facebook (OpenGraph) metadata
* GitHub (github.com)
* Steam (store.steampowered.com)
* iTunes (itunes.com)*
* GOG (gog.com)*
* Mac Game Store (macgamestore.com)*
* Green Man Gaming (greenmangaming.com)*
* Origin (origin.com)*
* Lynda (lynda.com)*
* Wikipedia (wikipedia.org)
* Google Play Store (play.google.com)
* Kickstarter (kickstarter.com)
* Final Fantasy XIV: A Realm Reborn (FFXIV ARR) Database (xivdb.com)
* Ebay (ebay.com)*

others to follow

(* site uses affiliate links)

Note
--
This plugin renders affiliate links for some websites in order to support its development. You are more than welcome to fork or modify the source if you don't want to allow this.


TODO:
* Add more parsers

Additional credits
* Inspired by the "Onebox" ruby gem (https://github.com/dysania/onebox)
* [Open Graph Protocol helper for PHP](https://github.com/scottmac/opengraph)
* [Amazon ECS PHP Library](https://github.com/Exeu/Amazon-ECS-PHP-Library)
* [forceutf8](https://github.com/neitanod/forceutf8)
* Vanilla template based on "Facebook Notify Widget" by Pixels Daily and [GitHub-jQuery-Repo-Widget](https://github.com/JoelSutherland/GitHub-jQuery-Repo-Widget)
* Flat template based on "FlatPad" by [Repix Design](http://store.repixdesign.com/)
* [PHPQuery](https://code.google.com/p/phpquery/)
* Icons by [Symbly](http://symb.ly)
* Italian translation by [Mte90](http://www.mte90.net/)

== Installation ==

1. Install the plugin as you would any other WordPress plugin and enable it
2. Go to Settings > Onebox
3. Change settings as required
4. Click the Onebox button in the WordPress editor toolbar

== Frequently Asked Questions ==

= Can I see a preview of how links will be rendered? =

Sure, you can see an example at the bottom of the settings page for the plugin once installed (although hyperlink colours will be based on your theme and not previewed), and a preview will also be shown in the visual WordPress editor (requires WordPress 4.0+).

For live examples, see http://onebox.surrealroad.com

= Will this slow down page loads? =

It *shouldn't*. WordPress will initially render the link as a boring hyperlink, which will then get rendered as a onebox via AJAX after the page has loaded. Actually turning the links into boxes happens asynchronously, and it's highly recommended that you install the APC extension for PHP to reduce the load on your server, as well as the servers you're linking to.

= How can I request extra features for whatever.com =

The best way is to [open a ticket at GitHub](https://github.com/surrealroad/wp-onebox/issues/new). Bonus points if the site in question has a well-documented API.

= What tags can I use when customising the template? =

You can use any or all of the following:
* {url}
* {class}
* {favicon}
* {sitename}
* {image}
* {title}
* {description}
* {additional}
* {footer}
* {title-button}
* {footer-button}


== Screenshots ==
1. Example Onebox for github.com using (default) flat style
2. Example Onebox for github.com using classic style
3. Example Onebox for github.com using dark style
4. Example Onebox for github.com using dark-flat style
5. Plugin admin options screen
6. Example Onebox in visual editor

== Changelog ==
= 1.0 =
* Live, editable previews are now shown in the visual WordPress editor (requires WordPress 4.0+)
* Oneboxes can be added via a button in the visual WordPress editor
* Added Italian localisation (thanks Mte90!)
* Fixed an issue where Oneboxes might get cropped in some themes with the default CSS
* Updated Gog.com parser (and added support for Gog.com movies)
* Updated Origin favicon
* Improved OpenGraph parsing
* Fallback to using cURL if file_get_contents is disabled (and generate a warning if so)
* Display an error is no suitable extensions can be found for fetching remote data
* Removed need for HTML5-lib

= 0.8 =
* Added eBay parser
* Removed cURL dependency
* Fix for title display in Twenty Fourteen theme
* Cosmetic improvements
* Green Man Gaming links now show format if available
* Dates are displayed in accordance with Wordpress settings where possible
* Improvements to the Kickstarter parser
* Improvements to the Google Play parser
* Improvements to the Wikipedia parser
* Added a new flat theme to better fit with new Wordpress installs, and which is now the default
* Added a dark version of the flat theme

= 0.7.2 =
* Added Final Fantasy XIV: A Realm Reborn (FFXIV ARR) Database parser
* Cosmetic tweaks for dark theme
* Fix for "undefined index" error when retrieving favicons
* Fix missing URL on Google Play price button
* Improved URL detection for some parsers

= 0.7.1 =
* Added Kickstarter parser
* Replaced some icons for uniformity

= 0.7 =
* Invalidate cache when title or description is manually changed
* Added link to settings from plugins page
* Fixed GitHub commit counts being capped at 30
* Changed the way affiliate links are processed internally for those who want to disable them
* Added Wikipedia parser
* Changed several parser to make use of PHPQuery to make maintenance easier
* Added genres and release date to Green Man Gaming parser

= 0.6.4 =
* Providing a title in the shortcode forces it to be used by the onebox (not just the text link)
* Added an optional "description" parameter to the shortcode
* Improvements to favicon detection and display

= 0.6.3 =
* Added lynda.com parser
* Security improvements
* Improvements to iTunes localisation
* Improvements to country detection

= 0.6.2 =
* Better handling of sites with broken HTML or no useful data

= 0.6.1 =
* Made a submodule self-enclosed

= 0.6 =
* Added optional "title" parameter to shortcode
* Just output plain links in RSS feeds
* Added ability to choose jQuery selector in settings
* Security improvements
* Use minified JS/CSS
* Don't try to guess WordPress location
* Added parsers for Origin and Green Man Gaming

= 0.5.1 =
* Improvements to iTunes parser
* Fix for caching with detected localisations
* Added Mac Game Store parser

= 0.5 =
* First public WordPress release