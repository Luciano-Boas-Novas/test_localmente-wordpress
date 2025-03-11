=== RD Station ===
Contributors: Filipe Mateus do Nascimento
Donate link: -
Tags: integrations, forms, contact form, rd station, resultados digitais
Requires at least: 4.7
Tested up to: 6.7.1
Stable tag: 5.5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate your contact forms with RD Station Marketing

== Description ==

Hello! This is the most practical way to integrate RD Station Marketing with your WordPress site.

It automatically activates the RD Station Marketing tracking code in your Wordpress pages, enabling features such as Lead Tracking and Pop ups. It also integrates the contact forms that capture Leads that convert in your website forms directly to RD Station Marketing.

More info about the version 5.0.0: [https://ajuda.rdstation.com.br/hc/pt-br/articles/360054981272](https://ajuda.rdstation.com.br/hc/pt-br/articles/360054981272)

Compatible with the following forms:

* Contact Form 7
* Gravity Forms
* WooCommerce (only available for the checkout form)

**Note:** The custom scripts feature, which allowed adding integration scripts to pages or posts, was removed in version **5.5.0** to enhance security and prevent potential **XSS attacks**.

Features:

* Forms Integrations
* Tracking code
* As many integrations as you want
* RD Station Marketing popups

== Installation ==

More info about the plugin setup: [https://github.com/ResultadosDigitais/rdstation-wp](https://github.com/ResultadosDigitais/rdstation-wp)

More info about the version 5.0.0: [https://ajuda.rdstation.com.br/hc/pt-br/articles/360054981272](https://ajuda.rdstation.com.br/hc/pt-br/articles/360054981272)

== Changelog ==

= 5.5.0 =
* Removed feature that allowed adding custom scripts to pages and posts to improve security and prevent potential XSS attacks
* Fixed a fatal error caused by a typo in the function name esc_html_ee(), replacing it with the correct esc_html_e()
* Fixed deprecation warnings for dynamic properties in PHP 8.2+. Improved compatibility with newer PHP versions
* Tested with WordPress version 6.7.1

= 5.4.0 =
* Fixing security vulnerability
* Add input sanitization and output escaping
* Testing plugin with 6.6.1 Wordpress version

= 5.3.2 =
* Testing plugin with 6.3.1 Wordpress version

= 5.1.3 =
* Fixing a bug in sending legal bases using Gravity Forms

= 5.1.2 =
* Update constant names

= 5.1.1 =
* Removing refresh token error from Log Screen and adding clear log button
* Fix error in the integration with advanced fields Gravity Forms
* More info about the version 5.1.1: [https://ajuda.rdstation.com.br/hc/pt-br/articles/360054981272](https://ajuda.rdstation.com.br/hc/pt-br/articles/360054981272)

= 5.1.0 =
* Log Screen

= 5.0.5 =
* Adding products to WooCommerce mapping fields list

= 5.0.4 =
* Sending drop-down menu from Contact Forms 7 as single text when Allow multiple selections aren't checked
* Fixing invalid data type errors

= 5.0.3 =
* Sending drop-down menu from Contact Forms 7 as single text when Allow multiple selections aren't checked
* Fixing refresh token error

= 5.0.2 =
* Adding company fields to the field mapping list

= 5.0.1 =
* Fixing problem with corrupted files in version 5.0.0

= 5.0 =
* Mapping fields RD Station
* API v2 RD Station

= 4.0 =
* One-click RD Station integration
* Add tracking code, popups and new forms integrations

= 3.2 =
* Add new translation keys

= 3.0 =
* WooCommerce integration

= 2.4 =
* Add custom scripts support

= 2.3.1 =
* Ignore captcha fields

= 2.3 =
* Update RD Station endpoint
* Improve field mapping of Contact Form 7

= 2.2 =
* Check post meta before saving the post

= 2.0 =
* Gravity Forms field mapping

= 1.1 =
* Add form_origem field

= 1.0 =
* Gravity Forms and Contact Form 7 support.
