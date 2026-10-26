import test from 'node:test';
import assert from 'node:assert/strict';
import { adminFixture } from './support/admin-dom.mjs';

function previewFixture( value ) {
	const f = adminFixture();
	f.origin = f.element( { value } );
	f.preview = f.element( { dataset: { emptyLabel: 'Enter an HTTPS origin' } } );
	f.selected.set( '#alynt-dashboard-expected-origin', f.origin );
	f.selected.set( '.adbd-endpoint-preview', f.preview );
	f.boot();
	return f;
}

test( 'preview does not silently strip rejected URL components', () => {
	for ( const value of [ 'https://user:pass@example.com', 'https://example.com/path',
		'https://example.com/?q=x', 'https://example.com/#hash', 'https://example.com:8443',
		'https://example.com?', 'https://example.com#', 'https://example.com/a/..',
		'https://example.com\\path', 'http://example.com', 'not a URL',
		'https://localhost', 'https://site.local', 'https://127.0.0.1', 'https://a..example.com' ] ) {
		const f = previewFixture( value );
		assert.equal( f.preview.textContent, f.preview.dataset.emptyLabel, value );
		assert.equal( f.origin.value, value, 'preview must not rewrite input' );
	}
} );

test( 'accepted origin shapes preview canonical endpoint and recover on input', async () => {
	const f = previewFixture( ' HTTPS://Example.com.:443/ ' );
	assert.equal( f.preview.textContent, 'https://example.com/wp-json/alynt-drime-backups-uploader/v1/status' );
	f.origin.value = 'https://example.com/path';
	await f.origin.emit( 'input' );
	assert.equal( f.preview.textContent, f.preview.dataset.emptyLabel );
	f.origin.value = 'https://next.example.com';
	await f.origin.emit( 'input' );
	assert.equal( f.preview.textContent, 'https://next.example.com/wp-json/alynt-drime-backups-uploader/v1/status' );
} );
