<?php
namespace DataBridge\Exporters;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class CsvExporter implements ExporterInterface {
	public function content_type(): string { return 'text/csv; charset=utf-8'; }
	public function file_extension(): string { return 'csv'; }
	public function render( array $records, string $entity ): string {
		$stream = fopen( 'php://temp', 'r+' );
		fputcsv( $stream, array( 'entity', 'source_id', 'data_json' ) );
		foreach ( $records as $record ) {
			fputcsv( $stream, array( $entity, $record['source_id'] ?? '', wp_json_encode( $record, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE ) ) );
		}
		rewind( $stream );
		$output = stream_get_contents( $stream );
		fclose( $stream );
		return (string) $output;
	}
}
