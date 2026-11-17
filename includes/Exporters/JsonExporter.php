<?php
namespace DataBridge\Exporters;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class JsonExporter implements ExporterInterface {
	public function content_type(): string { return 'application/json; charset=utf-8'; }
	public function file_extension(): string { return 'json'; }
	public function render( array $records, string $entity ): string {
		$payload = array( 'format_version' => '1.0', 'source' => 'woocommerce', 'entity' => $entity, 'exported_at' => gmdate( 'c' ), 'count' => count( $records ), 'records' => $records );
		return (string) wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE );
	}
}
