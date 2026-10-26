import test from 'node:test';
import assert from 'node:assert/strict';
import { adminFixture } from './support/admin-dom.mjs';

test( 'copy/download controls with missing or unusable targets remain hidden', () => {
	for ( const action of [ 'copy', 'download' ] ) {
		for ( const target of [ null, { textContent: 'not a field' } ] ) {
			const f = adminFixture();
			const button = f.element( { hidden: true, dataset: { [ `${ action }Target` ]: 'missing' } } );
			f.groups.set( `.adbd-${ action }-button`, [ button ] );
			if ( target ) f.fields.set( 'missing', target );
			f.boot();
			assert.equal( button.hidden, true, action );
		}
	}
} );

test( 'valid targets are enhanced without granting initially disabled controls', async () => {
	for ( const action of [ 'copy', 'download' ] ) {
		const f = adminFixture();
		const button = f.element( { hidden: true, disabled: true, dataset: { [ `${ action }Target` ]: 'summary' } } );
		f.groups.set( `.adbd-${ action }-button`, [ button ] );
		f.fields.set( 'summary', f.element( { value: '{}' } ) );
		f.boot();
		assert.equal( button.hidden, true );
		await button.emit( 'click' );
		assert.equal( button.disabled, true );
		assert.equal( f.copied.length, 0 );
		assert.equal( f.links.length, 0 );
	}
} );

test( 'target disappearing after enhancement produces feedback rather than a silent no-op', async () => {
	for ( const action of [ 'copy', 'download' ] ) {
		const f = adminFixture();
		const button = f.element( { hidden: true, dataset: {
			[ `${ action }Target` ]: 'summary', errorMessage: 'Refresh and try again',
		} } );
		f.groups.set( `.adbd-${ action }-button`, [ button ] );
		f.fields.set( 'summary', f.element( { value: '{}' } ) );
		f.boot();
		assert.equal( button.hidden, false );
		f.fields.delete( 'summary' );
		await button.emit( 'click' );
		f.flush();
		assert.equal( f.status.textContent, 'Refresh and try again' );
		assert.equal( f.copied.length + f.links.length, 0 );
	}
} );

test( 'a second copy event while busy cannot duplicate clipboard work', async () => {
	const f = adminFixture();
	const button = f.element( { dataset: { copyTarget: 'summary' } } );
	f.fields.set( 'summary', f.element( { value: '{}' } ) );
	f.groups.set( '.adbd-copy-button', [ button ] );
	let calls = 0;
	let finish;
	f.navigator.clipboard.writeText = () => { calls++; return new Promise( resolve => { finish = resolve; } ); };
	f.boot();
	const first = button.emit( 'click' );
	await button.emit( 'click' );
	assert.equal( calls, 1 );
	finish();
	await first;
	assert.equal( button.disabled, false );
} );
