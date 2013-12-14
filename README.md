wp-onebox
==

A Fancy Hyperlink Display Plugin for WordPress

What does this plugin do?
--
This plugin lets you use a shortcode `[onebox url="http://example.com"]` in place of a link that will display a lovely Facebook/Twitter-style box with additional information about the link

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

Note
--
This plugin renders affiliate links for some websites in order to support its development. You are more than welcome to fork or modify the source if you don't want to allow this.


TODO:

Additional credits
- Inspired by the Onebox module from Discourse (http://www.discourse.org/)