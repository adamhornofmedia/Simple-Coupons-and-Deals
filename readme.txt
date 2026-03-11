=== Simple Coupons & Deals ===
Contributors: adamhornof
Tags: coupons, deals, promo codes, discount codes, shortcode
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.2
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage coupons and deals with code copying, logo, and link support.

== Description ==

Simple Coupons & Deals adds a custom post type for managing coupon codes and promotional deals on your WordPress site.

**Features:**

* Custom post type for offers (coupons and deals)
* One-click coupon code copying
* Store logo support
* Link to the store or deal page
* Automatic deactivation by expiry date
* Filter offers by store in the frontend
* Optional ad shortcode inserted every 4 offers
* Settings page for ad shortcode configuration
* Fully translatable (`.pot` file included)

**Shortcodes:**

* `[nabidky]` — display all active offers
* `[nabidky type="kupon"]` — display coupons only
* `[nabidky type="akce"]` — display deals only
* `[nabidky count="6"]` — limit the number of offers shown
* `[nabidky obchod="Amazon"]` — filter by store name
* `[nabidka id="123"]` — display a single offer by ID

== Installation ==

1. Upload the `kupon` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Coupons & Deals** in the admin menu to add your offers.
4. Use the `[nabidky]` shortcode on any page to display your offers.
5. Optionally configure an ad shortcode under **Settings → Coupons & Deals**.

== Frequently Asked Questions ==

= How do I display all active offers? =

Add `[nabidky]` to any page or post.

= Can I show only coupons or only deals? =

Yes: `[nabidky type="kupon"]` or `[nabidky type="akce"]`.

= How do I display a single offer? =

Use `[nabidka id="123"]` where `123` is the offer's post ID. You can copy the shortcode directly from the offer list in the admin.

= How do offers expire automatically? =

Set a **Valid until** date in the offer details. The plugin checks this date on each page load and deactivates past offers automatically.

== Screenshots ==

1. Offer list in the admin panel.
2. Offer detail metabox.
3. Frontend offer card with copy-code button.

== Changelog ==

= 1.2 =
* Plugin converted to English with full i18n/l10n support.
* Added Czech (`cs_CZ`) translation.
* Added settings page for ad shortcode configuration.
* Added `wp_unslash()` before sanitization of `$_POST`/`$_GET` data.
* Switched `$wpdb->get_col()` to use `$wpdb->prepare()`.
* Replaced `date()` with `date_i18n()` for locale-aware date output.
* Removed duplicate nonce field in side metabox.
* Added version parameter to enqueued styles and scripts.
* Added `Requires at least`, `Requires PHP` headers.

= 1.1 =
* Initial public release.

== Upgrade Notice ==

= 1.2 =
Recommended update. Adds full i18n support and improves security handling.
