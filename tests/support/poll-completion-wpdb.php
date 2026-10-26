<?php
/**
 * Stateful, socket-free model of the generated poll-completion SQL only.
 * Tokens preserve prepared values without pretending to implement MySQL escaping.
 * Unknown SQL fails loudly; predicates are evaluated from source-generated SQL.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

class Alynt_Drime_Backups_Dashboard_Test_Poll_Completion_WPDB {

	public $prefix = 'wp_';
	public $row;
	public $last_error = '';
	public $queries = array();
	public $fail_write = false;
	public $fail_read = false;
	public $before_read;
	private $values = array();

	public function prepare( $sql, ...$args ) {
		if ( 1 === count( $args ) && is_array( $args[0] ) ) {
			$args = $args[0];
		}
		return preg_replace_callback( '/%[ds]/', function ( $match ) use ( &$args ) {
			if ( ! count( $args ) ) {
				throw new RuntimeException( 'Missing prepared argument.' );
			}
			$token = '@' . count( $this->values );
			$value = array_shift( $args );
			$this->values[ $token ] = '%d' === $match[0] ? (int) $value : (string) $value;
			return $token;
		}, $sql );
	}

	public function update( $table, $data, $where, $format = null, $where_format = null ) {
		$this->queries[] = array( $table, $data, $where );
		foreach ( $where as $key => $value ) {
			if ( null === $this->row || $this->row[ $key ] !== $value ) {
				return 0;
			}
		}
		$this->row = array_merge( $this->row, $data );
		return 1;
	}

	public function query( $sql ) {
		$this->queries[] = $sql;
		$this->last_error = $this->fail_write ? 'Injected write failure' : '';
		if ( $this->fail_write ) {
			return false;
		}
		if ( ! preg_match( '/^UPDATE wp_alynt_drime_dashboard_sites SET (.+) WHERE (.+)$/', $sql, $match ) ) {
			throw new RuntimeException( 'Unsupported UPDATE.' );
		}
		if ( ! $this->matches( $match[2] ) ) {
			return 0;
		}
		$before = $this->row;
		foreach ( explode( ', ', $match[1] ) as $assignment ) {
			if ( ! preg_match( '/^([a-z_]+) = (NULL|@[0-9]+)$/', $assignment, $field ) ) {
				throw new RuntimeException( 'Unsupported assignment.' );
			}
			$this->row[ $field[1] ] = 'NULL' === $field[2] ? null : $this->values[ $field[2] ];
		}
		return $before === $this->row ? 0 : 1;
	}

	public function get_var( $sql ) {
		$this->queries[] = $sql;
		if ( $this->before_read ) {
			call_user_func( $this->before_read, $this );
		}
		$this->last_error = $this->fail_read ? 'Injected read failure' : '';
		if ( $this->fail_read ) {
			return null;
		}
		if ( ! preg_match( '/^SELECT 1 FROM wp_alynt_drime_dashboard_sites WHERE (.+) LIMIT 1$/', $sql, $match ) ) {
			throw new RuntimeException( 'Unsupported SELECT.' );
		}
		return $this->matches( $match[1] ) ? '1' : null;
	}

	private function matches( $conditions ) {
		$matches = null !== $this->row;
		foreach ( explode( ' AND ', $conditions ) as $condition ) {
			if ( preg_match( '/^(BINARY )?enrollment_status IN \(\'active\', \'awaiting_first_poll\'\)$/', $condition, $field ) ) {
				$state = $matches ? $this->row['enrollment_status'] : '';
				$matches = $matches && in_array( ! empty( $field[1] ) ? $state : strtolower( $state ), array( 'active', 'awaiting_first_poll' ), true );
			} elseif ( preg_match( '/^([a-z_]+) IS NULL$/', $condition, $field ) ) {
				$matches = $matches && null === $this->row[ $field[1] ];
			} elseif ( preg_match( '/^(BINARY )?([a-z_]+) = (@[0-9]+)$/', $condition, $field ) ) {
				$actual   = $matches ? $this->row[ $field[2] ] : null;
				$expected = $this->values[ $field[3] ];
				$equal    = is_int( $expected ) ? (int) $actual === $expected : ( null !== $actual && ( $field[1] ? (string) $actual === $expected : 0 === strcasecmp( (string) $actual, $expected ) ) );
				$matches  = $matches && $equal;
			} else {
				throw new RuntimeException( 'Unsupported predicate: ' . $condition );
			}
		}
		return $matches;
	}
}
