<?php
namespace DataBridge\Exporters;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class JsonlExporter implements ExporterInterface {
	public function content_type(): string { return 'application/x-ndjson; charset=utf-8'; }
	public function file_extension(): string { return 'jsonl'; }
	public function render( array $records, string $entity ): string {
		$lines = array();
		foreach ( $records as $record ) {
			$lines[] = (string) wp_json_encode( array( 'entity' => $entity, 'data' => $record ), JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE );
		}
		return implode( "\n", $lines ) . ( $lines ? "\n" : '' );
	}
}
