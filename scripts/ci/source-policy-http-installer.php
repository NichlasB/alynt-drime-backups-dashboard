<?php
/**
 * Dormant fixed containment callbacks; explicit API doubles only in this stage.
 * No core bootstrap, HTTP/SQL/file IO, environment reader or production caller.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Pure fixture DATA definitions only; never a core loader. */
require_once __DIR__ . '/source-policy-http-installer-data.php';

/** Private proof helper; its supplied identity and class names are not a grant. */
final class Adbh_Installer_Containment {
	/** Fixed context DATA.
	 *
	 * @var array
	 */
	private $context;
	/** Bounded in-process observations, never raw secrets.
	 *
	 * @var array
	 */
	private $state = array(
		'phase'      => 'bootstrap',
		'step'       => 0,
		'http'       => 0,
		'hard'       => 0,
		'mail'       => 0,
		'bad'        => false,
		'registered' => false,
	);
	/** Two transient private URLs only; erased on seal/failure.
	 *
	 * @var array
	 */
	private $urls = array();

	/**
	 * Fixed source-owned caller DATA; no request-selected purpose or core loader.
	 *
	 * @param mixed  $identity Private identity DATA, verified separately by future owner.
	 * @param string $purpose Fixed setup or request enum.
	 */
	public function __construct( $identity, $purpose ) {
		if ( ! adbh_installer_identity( $identity ) || ! in_array( $purpose, array( 'setup', 'request' ), true ) ) {
			$this->abort();
		}
		$captured = array();
		foreach ( array( 'run', 'head', 'image', 'root' ) as $key ) {
			$captured[ $key ] = (string) $identity[ $key ];
		}
		$this->context = array(
			'id'      => $captured,
			'purpose' => $purpose,
			'core'    => $captured['root'] . '/adb-http-' . $captured['run'] . '/core',
		);
	}

	/** Fixed registration map; never accepts a caller callback.
	 *
	 * @return array
	 */
	private function hooks() {
		return array(
			'pre_http_request'         => array( 'http', 3 ),
			'flush_rewrite_rules_hard' => array( 'hard', 1 ),
			'block_local_requests'     => array( 'local', 1 ),
			'pre_wp_mail'              => array( 'mail', 2 ),
		);
	}

	/** Mark refusal without retaining URL/args/error payloads. */
	private function poison() {
		$this->state['bad'] = true;
		$this->urls         = array();
	}

	/** Immediate abort before a later foreign callback can undo a denial.
	 *
	 * @throws RuntimeException Fixed safe reason only.
	 */
	private function abort() {
		$this->poison();
		throw new RuntimeException( 'HTTP installer containment refused' );
	}

	/** Definition import never calls this; future private owner registers explicitly. */
	public function register() {
		global $wp_filter;
		if ( $this->state['registered'] || $this->state['bad'] || ! class_exists( 'WP_Hook', false ) || ! function_exists( 'add_filter' ) || ( isset( $wp_filter ) && ! is_array( $wp_filter ) ) ) {
			$this->abort();
		}
		foreach ( $this->hooks() as $hook => $spec ) {
			if ( isset( $wp_filter[ $hook ] ) && ( ! is_object( $wp_filter[ $hook ] ) || 'WP_Hook' !== get_class( $wp_filter[ $hook ] ) || array() !== $wp_filter[ $hook ]->callbacks ) ) {
				$this->abort();
			}
		}
		foreach ( $this->hooks() as $hook => $spec ) {
			if ( true !== add_filter( $hook, array( $this, $spec[0] ), 10, $spec[1] ) ) {
				$this->abort();
			}
		}
		$this->state['registered'] = true;
		$this->ready();
	}

	/** Entire fixed registry before returning, not just current callback presence. */
	private function ready() {
		global $wp_filter;
		if ( ! $this->state['registered'] || ! is_array( $wp_filter ) ) {
			$this->abort();
		}
		foreach ( $this->hooks() as $hook => $spec ) {
			$registry = $wp_filter[ $hook ] ?? null;
			$callback = array( $this, $spec[0] );
			if ( ! is_object( $registry ) || 'WP_Hook' !== get_class( $registry ) || ! is_array( $registry->callbacks ) || 1 !== count( $registry->callbacks ) || array( 10 ) !== array_keys( $registry->callbacks )
				|| ! is_array( $registry->callbacks[10] ) || 1 !== count( $registry->callbacks[10] ) ) {
				$this->abort();
			}
			$entries = $registry->callbacks[10];
			$entry   = reset( $entries );
			if ( ! adbh_installer_shape( $entry, array( 'function', 'accepted_args' ) ) || $entry['function'] !== $callback || $entry['accepted_args'] !== $spec[1]
				|| ( new ReflectionMethod( $this, $spec[0] ) )->getFileName() !== __FILE__ ) {
				$this->abort();
			}
		}
	}

	/** Only explicit private setup owner may enter its single installation. */
	public function begin_install() {
		$this->ready();
		if ( 'setup' !== $this->context['purpose'] || 'bootstrap' !== $this->state['phase'] || $this->state['bad'] ) {
			$this->abort();
		}
		$this->state['phase'] = 'install';
	}

	/**
	 * Fixed source order; failures stay poisoned even though network/file denial holds.
	 *
	 * @param string $event Fixed event enum.
	 * @return bool
	 */
	private function event( $event ) {
		$order = array( 'hard', 'http', 'hard', 'http', 'hard', 'hard' );
		if ( $this->state['bad'] || 'install' !== $this->state['phase'] || ( $order[ $this->state['step'] ] ?? null ) !== $event ) {
			$this->poison();
			return false;
		}
		++$this->state['step'];
		++$this->state[ $event ];
		return true;
	}

	/**
	 * Before transport, always real WP_Error or immediate safe abort.
	 *
	 * @param mixed $pre Incoming short-circuit value.
	 * @param mixed $args Parsed arguments.
	 * @param mixed $url Private URL, never exported.
	 * @return WP_Error
	 */
	public function http( $pre, $args, $url ) {
		$this->ready();
		if ( ! class_exists( 'WP_Error', false ) ) {
			$this->abort();
		}
		$expected = adbh_installer_args( $this->context['core'] );
		$valid    = false === $pre && adbh_installer_shape( $args, array_keys( $expected ) ) && $args === $expected
			&& is_string( $url ) && strlen( $url ) <= 256 && 1 === preg_match( '#\Ahttp://127\.0\.0\.1:18777/(?:index\.php/)?[0-9]{4}/[0-9]{2}/[0-9]{2}/hello-world/\z#', $url );
		$index    = count( $this->urls );
		if ( ! $valid || ( 0 === $index ? false !== strpos( $url, '/index.php/' ) : ( 1 !== $index || 'http://127.0.0.1:18777/index.php' . substr( $this->urls[0], strlen( 'http://127.0.0.1:18777' ) ) !== $url ) ) ) {
			$this->poison();
		} elseif ( $this->event( 'http' ) ) {
			$this->urls[] = $url;
		}
		return new WP_Error( 'adbh_http_blocked' );
	}

	/**
	 * Soft core option effects remain; no hard file writer is authorized.
	 *
	 * @param mixed $hard Incoming hard flag.
	 * @return false
	 */
	public function hard( $hard ) {
		$this->ready();
		if ( true !== $hard ) {
			$this->poison();
		} else {
			$this->event( 'hard' );
		}
		return false;
	}

	/**
	 * Defense in depth; primary-denied requests should never reach this callback.
	 *
	 * @param mixed $block Incoming local flag.
	 * @return true
	 */
	public function local( $block ) {
		unset( $block ); // Incoming value cannot weaken the fixed denial.
		$this->ready();
		$this->poison();
		return true;
	}

	/**
	 * Count only; no inspection/retention of recipients/messages/passwords.
	 *
	 * @param mixed $pre Incoming mail preemption.
	 * @param mixed $atts Private mail DATA, never retained or invoked.
	 * @return false
	 */
	public function mail( $pre, $atts ) {
		$this->ready();
		if ( null !== $pre || ! adbh_installer_shape( $atts, array( 'to', 'subject', 'message', 'headers', 'attachments', 'embeds' ) )
			|| 'install' !== $this->state['phase'] || 6 !== $this->state['step'] || 0 !== $this->state['mail'] ) {
			$this->poison();
		} else {
			++$this->state['mail'];
		}
		return false;
	}

	/**
	 * Supplied readback DATA must come from separate physical observers in future.
	 *
	 * @param mixed $post Independently read post fields; null for request.
	 * @param mixed $facts Final source/file/options/installing DATA, not attestation.
	 * @return array Bounded sidecar DATA only, no runtime grant.
	 */
	public function seal( $post, $facts ) {
		$this->ready();
		$setup = 'setup' === $this->context['purpose'];
		if ( $this->state['bad'] || 'closed' === $this->state['phase'] || ! adbh_installer_shape( $facts, array( 'permalinks', 'files_equal', 'installing', 'source_matches' ) )
			|| array(
				'permalinks'     => '',
				'files_equal'    => true,
				'installing'     => false,
				'source_matches' => true,
			) !== $facts
			|| ( $setup ? ( 'install' !== $this->state['phase'] || 6 !== $this->state['step'] || 2 !== $this->state['http'] || 4 !== $this->state['hard'] || adbh_installer_urls( $post ) !== $this->urls ) : ( 'bootstrap' !== $this->state['phase'] || null !== $post ) ) ) {
			$this->abort();
		}
		$this->urls           = array();
		$this->state['phase'] = 'closed';
		return $this->summary();
	}

	/** Current DATA can turn incomplete after a late callback; never publish early.
	 *
	 * @return array No raw URLs, source paths or mail payloads.
	 */
	public function summary() {
		return array_merge(
			array( 'model' => 'http-installer-containment-v1' ),
			array_intersect_key( $this->context['id'], array_flip( array( 'run', 'head', 'image' ) ) ),
			array(
				'owner'            => hash( 'sha256', implode( ':', $this->context['id'] ) ),
				'stage'            => $this->context['purpose'],
				'http'             => $this->state['http'],
				'hard'             => $this->state['hard'],
				'mail'             => $this->state['mail'],
				'violation'        => $this->state['bad'],
				'completed'        => 'closed' === $this->state['phase'] && ! $this->state['bad'],
				'runtime_grant'    => false,
				'production_ready' => false,
			)
		);
	}
}
