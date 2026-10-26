import './index.css';

const dashboard = document.querySelector( '.adbd-wrap' );

if ( dashboard ) {
	const actionNotice = dashboard.querySelector( '#adbd-action-notice' );

	if ( actionNotice ) {
		actionNotice.setAttribute( 'tabindex', '-1' );
	}

	const firstInvalidField = dashboard.querySelector( '[aria-invalid="true"]' );
	const motionSafe = ! window.matchMedia ||
		! window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	const focusRecoverableTarget = ( element ) => {
		element.focus( { preventScroll: true } );
		element.scrollIntoView( {
			block: 'center',
			behavior: motionSafe ? 'smooth' : 'auto',
		} );
	};

	if ( firstInvalidField ) {
		focusRecoverableTarget( firstInvalidField );
	} else if ( actionNotice && actionNotice.classList.contains( 'notice-error' ) ) {
		focusRecoverableTarget( actionNotice );
	}

	const announceCopyResult = ( button, message ) => {
		const container = button.closest( '.adbd-panel' ) || button.parentElement;
		const status = container ? container.querySelector( '.adbd-copy-status' ) : null;

		if ( status ) {
			status.textContent = '';
			window.setTimeout( () => {
				status.textContent = message;
			}, 10 );
		}
	};

	const fallbackCopy = ( field ) => {
		field.focus();
		field.select();
		field.setSelectionRange( 0, field.value.length );

		return document.execCommand( 'copy' );
	};

	dashboard.querySelectorAll( '.adbd-copy-button' ).forEach( ( button ) => {
		button.hidden = false;
		button.addEventListener( 'click', async () => {
			const field = document.getElementById( button.dataset.copyTarget || '' );

			if ( ! field ) {
				return;
			}

			let copied = false;
			const triggerHadFocus = document.activeElement === button;
			const originalLabel = button.textContent;
			const busyLabel = button.dataset.busyLabel || originalLabel;

			button.disabled = true;
			button.setAttribute( 'aria-disabled', 'true' );
			button.setAttribute( 'aria-busy', 'true' );
			button.textContent = busyLabel;

			try {
				if ( navigator.clipboard && window.isSecureContext ) {
					await navigator.clipboard.writeText( field.value );
					copied = true;
				} else {
					copied = fallbackCopy( field );
				}
			} catch ( error ) {
				try {
					copied = fallbackCopy( field );
				} catch ( fallbackError ) {
					copied = false;
				}
			} finally {
				button.disabled = false;
				button.removeAttribute( 'aria-disabled' );
				button.removeAttribute( 'aria-busy' );
				button.textContent = originalLabel;
				if ( copied && triggerHadFocus &&
					[ button, field, document.body ].includes( document.activeElement ) ) {
					button.focus( { preventScroll: true } );
				}
			}

			announceCopyResult(
				button,
				copied ? button.dataset.successMessage : button.dataset.errorMessage
			);
		} );
	} );

	dashboard.querySelectorAll( '.adbd-download-button' ).forEach( ( button ) => {
		button.hidden = false;
		button.addEventListener( 'click', () => {
			const field = document.getElementById( button.dataset.downloadTarget || '' );

			if ( ! field ) {
				return;
			}

			let url;
			let link;
			try {
				const blob = new Blob( [ field.value ], { type: 'application/json' } );
				url = window.URL.createObjectURL( blob );
				link = document.createElement( 'a' );

				link.href = url;
				link.download = button.dataset.downloadFilename || 'alynt-drime-dashboard-support-summary.json';
				link.hidden = true;
				document.body.appendChild( link );
				link.click();
				announceCopyResult( button, button.dataset.successMessage );
			} catch ( error ) {
				announceCopyResult( button, button.dataset.errorMessage );
			} finally {
				// Allow the browser to consume the download before releasing our URL.
				if ( url ) window.setTimeout( () => window.URL.revokeObjectURL( url ), 1000 );
				if ( link ) link.remove();
			}
		} );
	} );

	const busyForms = new Map();
	const restoreForm = ( form ) => {
		const state = busyForms.get( form );
		if ( ! state ) return;
		state.button.disabled = false;
		state.button.textContent = state.label;
		[ [ form, 'aria-busy', state.busy ], [ state.button, 'aria-disabled', state.disabled ] ]
			.forEach( ( [ element, attribute, value ] ) => {
				if ( value === null ) element.removeAttribute( attribute );
				else element.setAttribute( attribute, value );
			} );
		busyForms.delete( form );
	};
	window.addEventListener( 'pageshow', ( event ) => {
		if ( event.persisted ) busyForms.forEach( ( state, form ) => restoreForm( form ) );
	} );
	dashboard.querySelectorAll( 'form' ).forEach( ( form ) => {
		form.addEventListener( 'submit', ( event ) => {
			const button = event.submitter;

			if ( event.defaultPrevented || ! button || button.disabled || ! button.dataset.busyLabel || busyForms.has( form ) ) {
				return;
			}

			busyForms.set( form, {
				button, label: button.textContent,
				busy: form.getAttribute( 'aria-busy' ), disabled: button.getAttribute( 'aria-disabled' ),
			} );
			form.setAttribute( 'aria-busy', 'true' );
			button.disabled = true;
			button.setAttribute( 'aria-disabled', 'true' );
			button.textContent = button.dataset.busyLabel;
			window.setTimeout( () => {
				if ( event.defaultPrevented ) restoreForm( form );
			}, 0 );
		} );
	} );

	const originField = dashboard.querySelector( '#alynt-dashboard-expected-origin' );
	const endpointPreview = dashboard.querySelector( '.adbd-endpoint-preview' );

	if ( originField && endpointPreview ) {
		const updateEndpointPreview = () => {
			try {
				const origin = originField.value.trim();
				const url = new URL( origin );
				const host = url.hostname.replace( /\.+$/, '' );
				// Display hint only: server validation and DNS safety remain authoritative.
				const originShape = /^https:\/\/[a-z0-9.-]+(?::443)?\/?$/i.test( origin );
				const hostShape = /^[a-z0-9][a-z0-9.-]*[a-z0-9]$/.test( host ) &&
					host.length <= 253 && host.split( '.' ).every( ( label ) => label.length > 0 && label.length <= 63 ) &&
					! /^(?:\d+\.){3}\d+$/.test( host ) && host !== 'localhost' && ! /(^|\.)local$/.test( host );

				endpointPreview.textContent = originShape && hostShape
					? `https://${ host }/wp-json/alynt-drime-backups-uploader/v1/status`
					: endpointPreview.dataset.emptyLabel;
			} catch ( error ) {
				endpointPreview.textContent = endpointPreview.dataset.emptyLabel;
			}
		};

		originField.addEventListener( 'input', updateEndpointPreview );
		updateEndpointPreview();
	}
}
