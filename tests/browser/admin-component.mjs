// Optional isolated component smoke. No WordPress, existing browser profiles or network.
// node tests/browser/admin-component.mjs <installed-playwright/index.mjs> <chromium-executable>
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { pathToFileURL } from 'node:url';

const { chromium } = await import( pathToFileURL( process.argv[ 2 ] ).href );
const browser = await chromium.launch( { executablePath: process.argv[ 3 ], headless: true } );
try {
	const context = await browser.newContext( { acceptDownloads: true } );
	const page = await context.newPage();
	await page.route( '**/*', route => route.abort() );
	await page.setContent( `<main class="adbd-wrap" data-unavailable-message="Refresh and try again">
		<form><button data-busy-label="Saving…" type="submit">Save</button></form>
		<input id="alynt-dashboard-expected-origin" value="https://example.com/path">
		<code class="adbd-endpoint-preview" data-empty-label="Enter an origin"></code>
		<div class="adbd-panel"><textarea id="summary">synthetic</textarea>
		<button class="adbd-copy-button" data-copy-target="summary" data-success-message="Copied">Copy</button>
		<button class="adbd-copy-button" hidden data-copy-target="missing">Missing</button>
		<button class="adbd-download-button" data-download-target="summary" data-download-filename="synthetic.json">Download</button>
		<p class="adbd-copy-status" role="status" aria-live="polite"></p></div></main>` );
	// Clipboard operation is stubbed; DOM/focus, input and download are genuine Chromium.
	await page.evaluate( () => { document.execCommand = () => true; } );
	await page.addScriptTag( { content: readFileSync( new URL( '../../assets/dist/admin/index.js', import.meta.url ), 'utf8' ) } );
	await page.locator( 'form' ).evaluate( form => form.addEventListener( 'submit', event => event.preventDefault() ) );
	await page.getByRole( 'button', { name: 'Save', exact: true } ).click();
	await page.waitForFunction( () => ! document.querySelector( 'form button' ).disabled );
	assert.equal( await page.locator( 'form button' ).textContent(), 'Save' );
	assert.equal( await page.locator( 'form' ).getAttribute( 'aria-busy' ), null );
	assert.equal( await page.locator( '.adbd-endpoint-preview' ).textContent(), 'Enter an origin' );
	await page.locator( 'input' ).fill( 'https://example.com:443/' );
	assert.equal( await page.locator( '.adbd-endpoint-preview' ).textContent(), 'https://example.com/wp-json/alynt-drime-backups-uploader/v1/status' );
	assert.equal( await page.getByText( 'Missing', { exact: true } ).isVisible(), false );
	const copy = page.getByRole( 'button', { name: 'Copy', exact: true } );
	await copy.focus();
	await page.keyboard.press( 'Enter' );
	await page.waitForFunction( () => document.querySelector( '[role=status]' ).textContent === 'Copied' );
	assert.equal( await copy.evaluate( element => document.activeElement === element ), true );
	const [ download ] = await Promise.all( [ page.waitForEvent( 'download' ), page.getByRole( 'button', { name: 'Download', exact: true } ).click() ] );
	assert.equal( download.suggestedFilename(), 'synthetic.json' );
	assert.equal( await download.failure(), null );
	assert.equal( await page.locator( 'a[download]' ).count(), 0 );
	await page.locator( 'textarea' ).evaluate( element => element.remove() );
	await copy.click();
	await page.waitForFunction( () => document.querySelector( '[role=status]' ).textContent === 'Refresh and try again' );
	assert.equal( await copy.isEnabled(), true );
	await page.setViewportSize( { width: 390, height: 844 } );
	assert.equal( await copy.isVisible(), true );
	console.log( 'Isolated Chromium: 11 component assertions passed; no WordPress or network; clipboard stubbed.' );
} finally {
	await browser.close();
}
