=== YS | Yoast Primary Cat ===
Contributors: ysaintlary
Tags: woocommerce, yoast, primary category, csv, import
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Import Yoast WooCommerce primary categories from a CSV file (SKU to category ID).

== Description ==

YS Yoast Primary Cat imports primary product categories for the Yoast SEO plugin from a CSV file.

= CSV format =

Place a file named `primary-categories.csv` in the `data/` folder of the plugin:

`sku,primary_cat_id`
`S2MSSPAT,21`
`S2LPOLSN,23`

= How it works =

1. Upload the plugin and activate it
2. Place your CSV in the `data/` folder
3. Go to WooCommerce > Import Primary Cat
4. Click "Lancer l'import"
5. Deactivate and delete the plugin when done

== Installation ==

1. Upload the `yoast-primary-cat` folder to `/wp-content/plugins/`
2. Activate the plugin through the "Plugins" menu in WordPress
3. Ensure the CSV file is in the `data/` subfolder

== Changelog ==

= 1.0.0 =
* Initial release
