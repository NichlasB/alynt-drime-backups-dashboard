<?php
/**
 * Test support for admin polling-state history rendering methods.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Exposes polling-state history rendering helpers.
 */
trait Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_History_Methods {
	/**
	 * Exposes attention/recovery history markup.
	 *
	 * @param array<int,array<string,mixed>> $history Snapshot history rows.
	 * @return string
	 */
	public function attention_recovery_history_html( array $history ) {
		ob_start();
		$this->render_attention_recovery_history( $history );
		return (string) ob_get_clean();
	}
}
