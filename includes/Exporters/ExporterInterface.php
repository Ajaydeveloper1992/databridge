<?php
namespace DataBridge\Exporters;

if ( ! defined( 'ABSPATH' ) ) { exit; }

interface ExporterInterface {
	public function content_type(): string;
	public function file_extension(): string;
	public function render( array $records, string $entity ): string;
}
