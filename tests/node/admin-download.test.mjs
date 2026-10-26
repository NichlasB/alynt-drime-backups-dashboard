import test from 'node:test';
import assert from 'node:assert/strict';
import { adminFixture } from './support/admin-dom.mjs';

function downloadFixture() {
	const f = adminFixture();
	f.button = f.element( { dataset: {
		downloadTarget: 'summary', downloadFilename: 'summary.json', successMessage: 'Started', errorMessage: 'Try copy',
	} } );
	f.fields.set( 'summary', f.element( { value: '{"redacted":true}' } ) );
	f.groups.set( '.adbd-download-button', [ f.button ] );
	f.boot();
	return f;
}

test( 'successful download defers URL release, removes link and preserves content', async () => {
	const f = downloadFixture();
	let blob;
	f.window.URL.createObjectURL = value => { blob = value; return 'blob:owned-test'; };
	await f.button.emit( 'click' );
	assert.equal( await blob.text(), '{"redacted":true}' );
	assert.equal( blob.type, 'application/json' );
	assert.equal( f.links[ 0 ].download, 'summary.json' );
	assert.equal( f.links[ 0 ].attached, false );
	assert.deepEqual( f.revoked, [] );
	f.flush();
	assert.deepEqual( f.revoked, [ 'blob:owned-test' ] );
	assert.equal( f.status.textContent, 'Started' );
} );

test( 'click failure removes only owned link and releases its URL', async () => {
	const f = downloadFixture();
	f.clickError = true;
	await f.button.emit( 'click' );
	assert.equal( f.links[ 0 ].attached, false );
	f.flush();
	assert.deepEqual( f.revoked, [ 'blob:owned-test' ] );
	assert.equal( f.status.textContent, 'Try copy' );
} );

test( 'link construction failure after URL creation still releases the URL', async () => {
	const f = downloadFixture();
	f.document.createElement = () => { throw new Error( 'unavailable' ); };
	await f.button.emit( 'click' );
	f.flush();
	assert.deepEqual( f.revoked, [ 'blob:owned-test' ] );
	assert.equal( f.status.textContent, 'Try copy' );
} );
