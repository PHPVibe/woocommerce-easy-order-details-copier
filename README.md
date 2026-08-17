# woocommerce-easy-order-details-copier
Copy WooCommerce order details faster. Cleaner fields, custom labels, smart value cleaning, and one-click copy actions for real admin workflows.


=== PHPVibe Order Details Copier ===

Plugins details and url: [PHPVibe -> Easy Order Details Copier](https://phpvibe.com/download/order-details-copier/)

Contributors: woouseful, phpvibe

Tags: woocommerce, orders, admin, clipboard, customer details, hpos

Requires at least: 6.2

Tested up to: 6.9

Requires PHP: 7.4

Stable tag: 1.1.1

License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Copy clean WooCommerce customer, address, courier, invoice, support, and order details from the order screen or orders list in one click.

== Description ==

PHPVibe Order Details Copier is a free WooCommerce admin utility for store owners and order teams who constantly copy customer details from orders.

Instead of selecting messy billing or shipping blocks by hand, the plugin adds an Easy Copy workflow with individual copy buttons, ready-made copy blocks, order-list copy tools, and custom templates.

Useful for:

* Courier handoff and manual courier entry.
* WhatsApp/customer contact workflows.
* Manual invoicing and accounting support.
* Support tickets and internal order notes.
* Spreadsheet-friendly order preparation.
* Auction, wholesale, local delivery, and manual-processing stores.

Main features:

* One-click copy button for every individual detail.
* Quick Copy bar on the order screen.
* Copy blocks for courier, contact, billing, shipping, invoice, order summary, prep list, and spreadsheet rows.
* Orders-list copy dropdowns without opening each order.
* Bulk copy selected orders from WooCommerce > Orders.
* Open WhatsApp action when a customer phone is available.
* Smart badges for guest order, first order, missing phone, company order, customer note, and different billing/shipping details.
* Custom copy templates with live preview.
* Click-to-insert template variables.
* Dynamic custom meta variables such as `{meta:_billing_cui}`.
* Configurable custom order meta rows for invoice, tax, ERP, auction, courier, or checkout-field plugin data.
* Configurable panel title, location, density, field labels, enabled groups, enabled fields, and field order.
* Browser-side cleanup for copied text.
* HPOS compatibility declaration.
* Translation-ready strings for Loco Translate or similar tools.

== Privacy ==

This plugin only reads order data inside wp-admin and copies it to the current admin user clipboard. It does not send customer data to PHPVibe, does not call external APIs, and does not create logs.

The optional WhatsApp button opens the WhatsApp URL in the admin's browser only when clicked.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install the ZIP from WordPress admin.
2. Activate PHPVibe Order Details Copier.
3. Go to WooCommerce > Order Details Copier to configure fields, layout, templates, custom meta fields, and workflow tools.

== Frequently Asked Questions ==

= Does this support WooCommerce HPOS? =

Yes. The plugin declares compatibility with WooCommerce custom order tables / HPOS.

= Does this send customer data anywhere? =

No. The plugin works inside wp-admin and copies data to the local browser clipboard. There are no remote requests to PHPVibe.

= Can I copy custom checkout or invoice plugin fields? =

Yes. Add the order meta key under WooCommerce > Order Details Copier > Custom order meta fields, or use a template variable like `{meta:_billing_cui}`.

= Can I translate it? =

Yes. All admin strings are translation-ready using the `woouseful-order-details-copier` text domain.

== Changelog ==

= 1.1.1 =
* Added Enable all and Disable all controls for copyable details.
* Reworked regex extractors into a prominent Derived copy fields builder.
* Added a one-click Street and Street Number example preset.

= 1.1.0 =
* Added per-field visibility and alias controls.
* Added per-field trim, prefix removal, and regex replacement cleaners.
* Added configurable regex extractors that create new copy boxes from capture groups.
* Added validation for administrator-entered regular expressions.

= 1.0.0 =
* Polished as a free commercial PHPVibe release.
* Added premium settings-page hero and privacy/safety note.
* Added configurable custom order meta fields for invoice, tax, ERP, auction, courier, and checkout-field data.
* Added dynamic template meta variables such as `{meta:_billing_cui}`.
* Added Prep list and Spreadsheet quick-copy formats.
* Added Packing/prep block and Spreadsheet row copy fields.
* Added extra settings UI polish and responsive styling.
* Updated plugin metadata and readme for release use.

= 0.3.1 =
* Hardened WooCommerce HPOS single order screen detection so the plugin CSS and JavaScript load reliably on the order edit page.
* Prevented orders-list bulk copy controls from loading on HPOS single order edit screens.
* Added extra scoped CSS safeguards for the Quick Copy bar and Easy Copy panel.

= 0.3.0 =
* Added Quick Copy bar on the order screen.
* Added smart order badges.
* Added copy dropdowns to the WooCommerce orders list.
* Added bulk copy tools for selected orders.
* Added Open WhatsApp actions.
* Added live template preview in settings.
* Added click-to-insert variable chips.
* Added order status, order total, and order items fields.
* Improved copied text cleanup.

= 0.2.0 =
* Added configurable field labels.
* Added drag-and-drop field ordering.
* Added custom copy templates.
* Added configurable panel title and density.

= 0.1.0 =
* Initial Easy Copy panel with individual copy buttons.

