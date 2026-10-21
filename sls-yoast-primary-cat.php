<?php
/**
 * Plugin Name:       YS | Yoast Primary Cat
 * Plugin URI:        https://github.com/ysaintlary/sls-yoast-primary-cat
 * Description:       Importe les primary categories Yoast WooCommerce depuis un CSV (SKU → category ID).
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            Yves Saint-Lary
 * Author URI:        https://ysaintlary.com
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       sls-yoast-primary-cat
 * Domain Path:       /languages
 *
 * WC requires at least: 8.0
 * WC tested up to:      9.8
 * Requires Plugins:     woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'YS_YOAST_PRIMARY_CAT_VERSION', '1.0.0' );

/* ─── Compatibilité HPOS ─── */

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__ );
	}
} );

/* ─── Menu admin ─── */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'woocommerce',
		'Import Primary Categories',
		'Import Primary Cat',
		'manage_woocommerce',
		'sls-primary-cat-import',
		'ys_primary_cat_render_page'
	);
} );

/* ─── Page d'import ─── */

function ys_primary_cat_render_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( 'Accès refusé.' );
	}

	echo '<div class="wrap">';
	echo '<h1>Import Primary Categories Yoast</h1>';

	if ( isset( $_POST['sls_run_import'] ) && check_admin_referer( 'sls_primary_cat_import' ) ) {
		ys_primary_cat_process();
	} else {
		$csv_path = plugin_dir_path( __FILE__ ) . 'data/primary-categories.csv';
		$exists   = file_exists( $csv_path );
		$count    = 0;

		if ( $exists ) {
			$handle = fopen( $csv_path, 'r' );
			fgetcsv( $handle );
			while ( fgetcsv( $handle ) !== false ) {
				$count++;
			}
			fclose( $handle );
		}

		if ( ! $exists ) {
			echo '<div class="notice notice-error"><p>Fichier <code>data/primary-categories.csv</code> introuvable dans le dossier du plugin.</p></div>';
		} else {
			echo '<div class="notice notice-info"><p>Fichier CSV trouvé : <strong>' . esc_html( $count ) . ' produits</strong> à traiter.</p></div>';
			echo '<p>Ce plugin met à jour le champ <code>_yoast_wpseo_primary_product_cat</code> pour chaque produit identifié par son SKU.</p>';
			echo '<form method="post">';
			wp_nonce_field( 'sls_primary_cat_import' );
			echo '<input type="hidden" name="sls_run_import" value="1">';
			submit_button( 'Lancer l\'import', 'primary', 'submit', true );
			echo '</form>';
		}
	}

	echo '</div>';
}

/* ─── Traitement CSV ─── */

function ys_primary_cat_process() {
	$csv_path = plugin_dir_path( __FILE__ ) . 'data/primary-categories.csv';

	if ( ! file_exists( $csv_path ) ) {
		echo '<div class="notice notice-error"><p>Fichier CSV introuvable.</p></div>';
		return;
	}

	$handle = fopen( $csv_path, 'r' );
	if ( ! $handle ) {
		echo '<div class="notice notice-error"><p>Impossible d\'ouvrir le fichier CSV.</p></div>';
		return;
	}

	fgetcsv( $handle );

	$updated   = 0;
	$not_found = 0;
	$errors    = [];

	while ( ( $row = fgetcsv( $handle ) ) !== false ) {
		if ( count( $row ) < 2 ) {
			continue;
		}

		$sku            = sanitize_text_field( trim( $row[0] ) );
		$primary_cat_id = absint( trim( $row[1] ) );

		if ( empty( $sku ) || $primary_cat_id === 0 ) {
			continue;
		}

		$product_id = wc_get_product_id_by_sku( $sku );

		if ( ! $product_id ) {
			$not_found++;
			$errors[] = $sku;
			continue;
		}

		update_post_meta( $product_id, '_yoast_wpseo_primary_product_cat', (string) $primary_cat_id );
		$updated++;
	}

	fclose( $handle );

	echo '<div class="notice notice-success"><p><strong>' . esc_html( $updated ) . ' produits</strong> mis à jour.</p></div>';

	if ( $not_found > 0 ) {
		echo '<div class="notice notice-warning"><p>' . esc_html( $not_found ) . ' SKUs non trouvés :</p><ul>';
		foreach ( $errors as $sku ) {
			echo '<li><code>' . esc_html( $sku ) . '</code></li>';
		}
		echo '</ul></div>';
	}

	echo '<p>Import terminé. Vous pouvez désactiver et supprimer ce plugin.</p>';
}
