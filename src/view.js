/**
 * WordPress dependencies
 */
import { store, getContext } from '@wordpress/interactivity';

// Import the store
import './interactivity/weather-store.js';

// Initialize the weather block when the DOM is ready
document.addEventListener( 'DOMContentLoaded', function () {
	// Get all weather block elements
	const weatherBlocks = document.querySelectorAll(
		'[data-wp-interactive="weatherblock"]'
	);

	weatherBlocks.forEach( ( block ) => {
		// Get context from the block
		const context = block.dataset.wpContext
			? JSON.parse( block.dataset.wpContext )
			: {};

		// Initialize with the default city if provided
		if ( context.initialCity ) {
			const { state, actions } = store( 'weatherblock' );
			state.cityName = context.initialCity;

			// Fetch initial weather data
			actions.fetchWeather( context.initialCity );

			// Set up auto refresh if enabled
			if ( context.autoRefresh ) {
				state.autoRefresh = true;
				actions.toggleAutoRefresh();
			}
		}
	} );
} );

// Cleanup function to prevent memory leaks
function weatherBlockCleanup() {
	const { state } = store( 'weatherblock' );

	// Clear debounce timer
	if ( state.debounceTimer ) {
		clearTimeout( state.debounceTimer );
		state.debounceTimer = null;
	}

	// Clear refresh interval
	if ( state.refreshInterval ) {
		clearInterval( state.refreshInterval );
		state.refreshInterval = null;
	}
}

// Clean up intervals when the page is about to be unloaded
window.addEventListener( 'beforeunload', weatherBlockCleanup );

// Clean up when page visibility changes (for SPA scenarios)
document.addEventListener( 'visibilitychange', weatherBlockCleanup );

// Clean up when navigating away (for WordPress admin)
document.addEventListener( 'DOMContentLoaded', function () {
	// Add cleanup for when blocks are removed from DOM
	const observer = new MutationObserver( function ( mutations ) {
		mutations.forEach( function ( mutation ) {
			mutation.removedNodes.forEach( function ( node ) {
				if (
					node.nodeType === 1 &&
					node.hasAttribute &&
					node.hasAttribute( 'data-wp-interactive' )
				) {
					if (
						node.getAttribute( 'data-wp-interactive' ) ===
						'weatherblock'
					) {
						weatherBlockCleanup();
					}
				}
			} );
		} );
	} );

	observer.observe( document.body, {
		childList: true,
		subtree: true,
	} );
} );
