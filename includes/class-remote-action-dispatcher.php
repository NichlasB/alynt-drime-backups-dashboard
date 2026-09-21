<?php
/**
 * Remote action dispatcher.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.15
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates, signs, dispatches, and records V2.1 remote action intents.
 *
 * @since 0.1.15
 */
class Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Actions;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Schedule_Actions;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Schedule_Rollback_Preview;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Intent;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Transport;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Utils;

	const ACTION_ROUTE       = '/wp-json/alynt-drime-backups-uploader/v2/action-intents';
	const DEFAULT_TIMEOUT    = 15;
	const MAX_RESPONSE_BYTES = 32768;
	const INTENT_TTL_SECONDS = 300;

	/**
	 * Site repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Site_Repository
	 */
	private $sites;

	/**
	 * Snapshot repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Snapshot_Repository
	 */
	private $snapshots;

	/**
	 * Action repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Repository
	 */
	private $actions;

	/**
	 * Origin validator.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Origin_Validator
	 */
	private $origins;

	/**
	 * Credential vault.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Credential_Vault
	 */
	private $vault;

	/**
	 * Signer.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Signer
	 */
	private $signer;

	/**
	 * Capabilities.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities
	 */
	private $capabilities;

	/**
	 * Optional HTTP client override.
	 *
	 * @var callable|null
	 */
	private $http_client;

	/**
	 * Optional DNS resolver override.
	 *
	 * @var callable|null
	 */
	private $resolver;

	/**
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Site_Repository|null            $sites Site repository.
	 * @param Alynt_Drime_Backups_Dashboard_Snapshot_Repository|null        $snapshots Snapshot repository.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Repository|null   $actions Action repository.
	 * @param Alynt_Drime_Backups_Dashboard_Origin_Validator|null           $origins Origin validator.
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault|null           $vault Credential vault.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Signer|null       $signer Signer.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities|null $capabilities Capabilities.
	 * @param callable|null                                                 $http_client HTTP client.
	 * @param callable|null                                                 $resolver DNS resolver.
	 */
	public function __construct( $sites = null, $snapshots = null, $actions = null, $origins = null, $vault = null, $signer = null, $capabilities = null, $http_client = null, $resolver = null ) {
		$this->sites        = $sites instanceof Alynt_Drime_Backups_Dashboard_Site_Repository ? $sites : new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$this->snapshots    = $snapshots instanceof Alynt_Drime_Backups_Dashboard_Snapshot_Repository ? $snapshots : new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
		$this->actions      = $actions instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Repository ? $actions : new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
		$this->origins      = $origins instanceof Alynt_Drime_Backups_Dashboard_Origin_Validator ? $origins : new Alynt_Drime_Backups_Dashboard_Origin_Validator();
		$this->vault        = $vault instanceof Alynt_Drime_Backups_Dashboard_Credential_Vault ? $vault : new Alynt_Drime_Backups_Dashboard_Credential_Vault();
		$this->signer       = $signer instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Signer ? $signer : new Alynt_Drime_Backups_Dashboard_Remote_Action_Signer();
		$this->capabilities = $capabilities instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities ? $capabilities : new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
		$this->http_client  = is_callable( $http_client ) ? $http_client : null;
		$this->resolver     = is_callable( $resolver ) ? $resolver : null;
	}
}
