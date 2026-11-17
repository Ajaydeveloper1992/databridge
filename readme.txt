=== DataBridge – Data Export & Migration ===
Contributors: YOUR_WORDPRESS_ORG_USERNAME
Tags: woocommerce, export, migration, json, csv
Requires at least: 6.5
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Export WooCommerce customers, orders, and products into portable JSON, JSONL, and CSV files.

== Description ==
DataBridge helps store administrators export WooCommerce data into portable files for backup, analysis, and migration workflows.

Version 0.1.0 supports:
* Customer export.
* Order export, including billing, shipping, payment metadata, line items, and totals.
* Product export, including SKU, prices, stock information, categories, images, and metadata.
* JSON, JSONL, and CSV output.
* Paginated retrieval in batches of up to 100 records.

This initial release is an exporter. Direct imports into external databases, schema mapping, and resumable background migrations are not included in version 0.1.0.

== Installation ==
1. Upload the databridge folder to the /wp-content/plugins/ directory, or install the ZIP through Plugins > Add New > Upload Plugin.
2. Activate WooCommerce.
3. Activate DataBridge.
4. Open DataBridge in the WordPress admin menu.
5. Choose Customers, Orders, or Products and select an export format.
6. Download and store the export securely.

== Frequently Asked Questions ==

= Does DataBridge require WooCommerce? =
Yes. Version 0.1.0 exports WooCommerce customers, orders, and products.

= Does this version import into MongoDB? =
No. This initial release exports portable files. Database destinations and import workflows are planned for future versions.

= Does DataBridge send data to an external server? =
No. Version 0.1.0 generates a download locally and does not send store data to DataBridge or any third-party service.

= Who can export data? =
Users with the WooCommerce manage_woocommerce capability who can access the WordPress admin page.

= Does the export include personal data? =
Customer and order exports can contain names, email addresses, phone numbers, and postal addresses. Handle downloaded files securely and in accordance with applicable privacy requirements.

== Privacy ==
DataBridge 0.1.0 does not contact external services and does not create a DataBridge account. Export files are generated in the current admin request and downloaded by the authorized administrator. The files may contain personal data from the store.

== Changelog ==

= 0.1.0 =
* Initial public-development release.
* Export WooCommerce customers, orders, and products as JSON, JSONL, or CSV.
