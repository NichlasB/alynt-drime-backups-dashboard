import test from 'node:test';
import assert from 'node:assert/strict';
import { adminFixture } from './support/admin-dom.mjs';

function formFixture() {
	const f = adminFixture();
	f.form = f.element();
	f.button = f.element( { textContent: 'Save', dataset: { busyLabel: 'Saving…' } } );
	f.groups.set( 'form', [ f.form ] );
	f.boot();
	return f;
}

test( 'normal submission stays busy until a persisted page is restored', async () => {
	const f = formFixture();
	await f.form.emit( 'submit', { submitter: f.button } );
	assert.equal( f.button.disabled, true );
	assert.equal( f.form.getAttribute( 'aria-busy' ), 'true' );
	f.flush();
	assert.equal( f.button.textContent, 'Saving…' );
	f.pageShow( false );
	assert.equal( f.button.disabled, true );
	f.pageShow();
	assert.equal( f.button.textContent, 'Save' );
	assert.equal( f.button.disabled, false );
	assert.equal( f.form.getAttribute( 'aria-busy' ), null );
	assert.equal( f.button.getAttribute( 'aria-disabled' ), null );
} );

test( 'cancellation by a later handler restores original owned state', async () => {
	const f = formFixture();
	f.form.setAttribute( 'aria-busy', 'false' );
	f.button.setAttribute( 'aria-disabled', 'false' );
	const event = { submitter: f.button, defaultPrevented: false };
	await f.form.emit( 'submit', event );
	event.defaultPrevented = true;
	f.flush();
	assert.equal( f.button.disabled, false );
	assert.equal( f.button.textContent, 'Save' );
	assert.equal( f.form.getAttribute( 'aria-busy' ), 'false' );
	assert.equal( f.button.getAttribute( 'aria-disabled' ), 'false' );
} );

test( 'already-cancelled, absent-submitter and disabled controls remain untouched', async () => {
	for ( const mode of [ 'cancelled', 'absent', 'disabled' ] ) {
		const f = formFixture();
		f.button.disabled = mode === 'disabled';
		await f.form.emit( 'submit', {
			submitter: mode === 'absent' ? null : f.button, defaultPrevented: mode === 'cancelled',
		} );
		f.pageShow();
		assert.equal( f.button.textContent, 'Save' );
		assert.equal( f.button.disabled, mode === 'disabled' );
		assert.equal( f.form.getAttribute( 'aria-busy' ), null );
	}
} );
