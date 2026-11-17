# DataBridge – Data Export & Migration

Open-source WordPress plugin for exporting WooCommerce customers, orders, and products to JSON, JSONL, and CSV.

## Current scope (0.1.0)

This first functional milestone is an exporter, not yet a universal two-way migration platform. It includes WooCommerce source extraction, paginated reads, normalized records, and local file downloads. External database imports, schema mapping, background resumability, and additional source adapters are future milestones.

## Requirements

- WordPress 6.5+
- PHP 8.0+
- WooCommerce active

## Install

Upload the `databridge` directory as a plugin or install the release ZIP in WordPress Admin → Plugins → Add New → Upload Plugin.

## Security and privacy

Exports may contain personal data. Only users with `manage_woocommerce` capability can initiate exports. The export action verifies a WordPress nonce and validates entity/format selections. Data is generated locally; this version does not transmit data to external services.

## Development

- Main bootstrap: `databridge.php`
- WooCommerce source adapter: `includes/Sources/WooCommerceSource.php`
- Exporters: `includes/Exporters/`
- Admin UI and download handler: `includes/Admin.php`

Before public release, run PHP linting, WordPress Coding Standards, Plugin Check, WooCommerce compatibility tests, and a manual review on staging data.

## License

GPL-2.0-or-later.
