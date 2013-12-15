wp-onebox
==

Onebox: A Fancy Hyperlink Display Plugin for WordPress

What does this plugin do?
--
This plugin lets you use a shortcode `[onebox url="http://example.com"]` in place of a link that will display a lovely Facebook/Twitter-style box with additional information about the link

The source is maintained on GitHub: https://github.com/surrealroad/wp-onebox

![Example Onebox for itunes.com using default style](screenshot-1.png)
![Example Onebox for github.com using dark style](screenshot-2.png)
![Plugin admin options screen](screenshot-3.png)
![Example Onebox for gog.com](screenshot-4.png)

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
