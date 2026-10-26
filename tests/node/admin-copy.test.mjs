import test from 'node:test';
import assert from 'node:assert/strict';
import { adminFixture } from './support/admin-dom.mjs';

function copyFixture() {
	const f = adminFixture();
	f.button = f.element( { textContent: 'Copy', dataset: {
		copyTarget: 'summary', busyLabel: 'Copying…', successMessage: 'Copied', errorMessage: 'Select manually',
	} } );
	f.field = f.element( { value: '{"counts":0}' } );
	f.fields.set( 'summary', f.field );
	f.groups.set( '.adbd-copy-button', [ f.button ] );
	return f;
}

test( 'successful fallback copy restores keyboard trigger focus', async () => {
	const f = copyFixture();
	f.window.isSecureContext = false;
	f.boot();
	f.button.focus();
	await f.button.emit( 'click' );
	f.flush();
	assert.equal( f.document.activeElement, f.button );
	assert.equal( f.status.textContent, 'Copied' );
	assert.equal( f.button.disabled, false );
	assert.equal( f.button.textContent, 'Copy' );
} );

test( 'failed native and fallback copy retains selected manual recovery', async () => {
	const f = copyFixture();
	f.navigator.clipboard.writeText = async () => { throw new Error( 'denied' ); };
	f.document.execCommand = () => false;
	f.boot();
	f.button.focus();
	await f.button.emit( 'click' );
	f.flush();
	assert.equal( f.document.activeElement, f.field );
	assert.equal( f.field.selected, true );
	assert.equal( f.status.textContent, 'Select manually' );
	assert.equal( f.button.getAttribute( 'aria-busy' ), null );
} );

test( 'asynchronous clipboard completion does not steal focus from another control', async () => {
	const f = copyFixture();
	let finish;
	f.navigator.clipboard.writeText = () => new Promise( resolve => { finish = resolve; } );
	f.boot();
	f.button.focus();
	const pending = f.button.emit( 'click' );
	const other = f.element();
	other.focus();
	finish();
	await pending;
	assert.equal( f.document.activeElement, other );
} );

test( 'native clipboard receives the unchanged textarea value', async () => {
	const f = copyFixture();
	f.boot();
	await f.button.emit( 'click' );
	assert.deepEqual( f.copied, [ f.field.value ] );
} );
