<?php
namespace DataBridge;

use DataBridge\Sources\WooCommerceSource;
use DataBridge\Exporters\JsonExporter;
use DataBridge\Exporters\JsonlExporter;
use DataBridge\Exporters\CsvExporter;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Admin {
	private WooCommerceSource $source;

	public function __construct() {
		$this->source = new WooCommerceSource();
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_databridge_export', array( $this, 'handle_export' ) );
	}

	public function register_menu(): void {
		add_menu_page(
			esc_html__( 'DataBridge', 'databridge' ),
			esc_html__( 'DataBridge', 'databridge' ),
			'manage_woocommerce',
			'databridge',
			array( $this, 'render_page' ),
			'dashicons-database-export',
			56
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'DataBridge', 'databridge' ); ?></h1>
			<p><?php echo esc_html__( 'Export WooCommerce customers, orders, and products into portable data files.', 'databridge' ); ?></p>
			<?php if ( ! $this->source->is_available() ) : ?>
				<div class="notice notice-warning"><p><?php echo esc_html__( 'WooCommerce is not available. Activate WooCommerce to use the exporter.', 'databridge' ); ?></p></div>
			<?php else : ?>
				<div class="card" style="max-width:760px;padding:20px;">
					<h2><?php echo esc_html__( 'Create an export', 'databridge' ); ?></h2>
					<p><?php echo esc_html__( 'Exports can contain personal customer information. Store downloaded files securely and share them only with authorized recipients.', 'databridge' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="databridge_export">
						<?php wp_nonce_field( 'databridge_export_action', 'databridge_export_nonce' ); ?>
						<table class="form-table" role="presentation">
							<tr><th scope="row"><label for="databridge_entity"><?php echo esc_html__( 'Data type', 'databridge' ); ?></label></th><td>
								<select name="entity" id="databridge_entity" required>
									<option value="customers"><?php echo esc_html__( 'Customers', 'databridge' ); ?></option>
									<option value="orders"><?php echo esc_html__( 'Orders', 'databridge' ); ?></option>
									<option value="products"><?php echo esc_html__( 'Products', 'databridge' ); ?></option>
								</select>
							</td></tr>
							<tr><th scope="row"><label for="databridge_format"><?php echo esc_html__( 'File format', 'databridge' ); ?></label></th><td>
								<select name="format" id="databridge_format" required>
									<option value="json">JSON</option><option value="jsonl">JSONL</option><option value="csv">CSV</option>
								</select>
							</td></tr>
						</table>
						<?php submit_button( __( 'Download export', 'databridge' ), 'primary', 'submit', false ); ?>
					</form>
				</div>
				<div class="notice notice-info inline"><p><?php echo esc_html__( 'Version 0.1.0 exports WooCommerce data. Import destinations, field mapping, and resumable background migrations are planned for subsequent releases.', 'databridge' ); ?></p></div>
			<?php endif; ?>
		</div>
		<?php
	}

	public function handle_export(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You are not allowed to export store data.', 'databridge' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'databridge_export_action', 'databridge_export_nonce' );

		$entity = isset( $_POST['entity'] ) ? sanitize_key( wp_unslash( $_POST['entity'] ) ) : '';
		$format = isset( $_POST['format'] ) ? sanitize_key( wp_unslash( $_POST['format'] ) ) : '';
		$allowed_entities = array( 'customers', 'orders', 'products' );
		$exporters = array( 'json' => new JsonExporter(), 'jsonl' => new JsonlExporter(), 'csv' => new CsvExporter() );

		if ( ! in_array( $entity, $allowed_entities, true ) || ! isset( $exporters[ $format ] ) ) {
			wp_die( esc_html__( 'Invalid export selection.', 'databridge' ), '', array( 'response' => 400 ) );
		}

		if ( function_exists( 'set_time_limit' ) ) { @set_time_limit( 0 ); }
		$records = array();
		$page = 1;
		$per_page = 100;
		$max_pages = 1000;
		do {
			$batch = $this->source->fetch_page( $entity, $page, $per_page );
			foreach ( $batch as $record ) { $records[] = $record; }
			$page++;
		} while ( count( $batch ) === $per_page && $page <= $max_pages );

		$exporter = $exporters[ $format ];
		$body = $exporter->render( $records, $entity );
		if ( ! is_string( $body ) ) {
			wp_die( esc_html__( 'The export could not be generated.', 'databridge' ), '', array( 'response' => 500 ) );
		}

		$filename = sprintf( 'databridge-%s-%s.%s', $entity, gmdate( 'Y-m-d-His' ), $exporter->file_extension() );
		nocache_headers();
		header( 'Content-Type: ' . $exporter->content_type() );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
		header( 'X-Content-Type-Options: nosniff' );
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Serialized file download, not HTML output.
		exit;
	}
}
