<?php
namespace DataBridge\Contracts;

if ( ! defined( 'ABSPATH' ) ) { exit; }

interface SourceAdapterInterface {
	/** Return whether the source platform is available. */
	public function is_available(): bool;

	/** Return a page of normalized records for an entity. */
	public function fetch_page( string $entity, int $page, int $per_page ): array;
}
