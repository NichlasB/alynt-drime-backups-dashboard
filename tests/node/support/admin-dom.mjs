import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

// Executes the actual source against narrow DOM/API doubles; not WordPress proof.
const source = readFileSync( new URL( '../../../assets/src/admin/index.js', import.meta.url ), 'utf8' )
	.replace( "import './index.css';", '' );

export function adminFixture() {
	const timers = [];
	const fields = new Map();
	const groups = new Map();
	const selected = new Map();
	const events = new Map();
	const links = [];
	const revoked = [];
	const copied = [];
	const document = {
		activeElement: null,
		querySelector: () => dashboard,
		getElementById: ( id ) => fields.get( id ) || null,
		execCommand: () => true,
		body: { appendChild: ( link ) => { link.attached = true; } },
		createElement: () => {
			const link = element();
			link.click = () => { if ( fixture.clickError ) throw new Error( 'click failed' ); };
			link.remove = () => { link.attached = false; };
			links.push( link );
			return link;
		},
	};
	function element( properties = {} ) {
		const attributes = new Map();
		const handlers = new Map();
		return {
			textContent: '', value: '', dataset: {}, hidden: false, disabled: false,
			setAttribute: ( key, value ) => attributes.set( key, value ),
			getAttribute: ( key ) => attributes.has( key ) ? attributes.get( key ) : null,
			removeAttribute: ( key ) => attributes.delete( key ),
			addEventListener: ( key, handler ) => handlers.set( key, handler ),
			emit: async ( key, event = {} ) => handlers.get( key )?.( event ),
			focus() { document.activeElement = this; },
			select() { this.selected = true; },
			setSelectionRange() {}, scrollIntoView() {},
			closest: () => panel, parentElement: panel,
			...properties,
		};
	}
	// These objects are initialized before any element is constructed.
	const panel = { querySelector: () => status };
	const status = { textContent: '' };
	const dashboard = {
		querySelector: ( selector ) => selected.get( selector ) || null,
		querySelectorAll: ( selector ) => groups.get( selector ) || [],
	};
	const window = {
		isSecureContext: true,
		setTimeout: ( callback, delay ) => { timers.push( { callback, delay } ); },
		addEventListener: ( key, handler ) => events.set( key, handler ),
		URL: { createObjectURL: () => 'blob:owned-test', revokeObjectURL: ( url ) => revoked.push( url ) },
	};
	const navigator = { clipboard: { writeText: async ( value ) => { copied.push( value ); } } };
	const fixture = {
		document, window, navigator, fields, groups, selected, element, links, revoked, copied, status,
		boot: () => runInNewContext( source, { document, window, navigator, URL, Blob } ),
		flush: () => { while ( timers.length ) timers.shift().callback(); },
		pageShow: ( persisted = true ) => events.get( 'pageshow' )?.( { persisted } ),
	};
	return fixture;
}
