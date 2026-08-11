import './index.css';

const dashboard = document.querySelector( '.adbd-wrap' );

if ( dashboard ) {
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

			try {
				if ( navigator.clipboard && window.isSecureContext ) {
					await navigator.clipboard.writeText( field.value );
					copied = true;
				} else {
					copied = fallbackCopy( field );
				}
			} catch ( error ) {
				copied = fallbackCopy( field );
			}

			announceCopyResult(
				button,
				copied ? button.dataset.successMessage : button.dataset.errorMessage
			);
		} );
	} );

	dashboard.querySelectorAll( 'form' ).forEach( ( form ) => {
		form.addEventListener( 'submit', ( event ) => {
			const button = event.submitter;

			if ( ! button || ! button.dataset.busyLabel ) {
				return;
			}

			form.setAttribute( 'aria-busy', 'true' );
			button.disabled = true;
			button.setAttribute( 'aria-disabled', 'true' );
			button.textContent = button.dataset.busyLabel;
		} );
	} );

	const originField = dashboard.querySelector( '#alynt-dashboard-expected-origin' );
	const endpointPreview = dashboard.querySelector( '.adbd-endpoint-preview' );

	if ( originField && endpointPreview ) {
		const updateEndpointPreview = () => {
			try {
				const url = new URL( originField.value );

				endpointPreview.textContent = url.protocol === 'https:'
					? `${ url.origin }/wp-json/alynt-drime-backups-uploader/v1/status`
					: endpointPreview.dataset.emptyLabel;
			} catch ( error ) {
				endpointPreview.textContent = endpointPreview.dataset.emptyLabel;
			}
		};

		originField.addEventListener( 'input', updateEndpointPreview );
		updateEndpointPreview();
	}
}
