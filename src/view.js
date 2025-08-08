/**
 * WordPress dependencies
 */
import { store, getContext } from '@wordpress/interactivity';

// Import the store
import './interactivity/weather-store.js';

// Initialize the weather block when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {
	// Get all weather block elements
	const weatherBlocks = document.querySelectorAll('[data-wp-interactive="weatherblock"]');
	
	weatherBlocks.forEach((block) => {
		// Get context from the block
		const context = block.dataset.wpContext ? JSON.parse(block.dataset.wpContext) : {};
		
		// Initialize with the default city if provided
		if (context.initialCity) {
			const { state, actions } = store('weatherblock');
			state.cityName = context.initialCity;
			
			// Fetch initial weather data
			actions.fetchWeather(context.initialCity);
			
			// Set up auto refresh if enabled
			if (context.autoRefresh) {
				state.autoRefresh = true;
				actions.toggleAutoRefresh();
			}
		}
	});
});

// Clean up intervals when the page is about to be unloaded
window.addEventListener('beforeunload', function() {
	const { state } = store('weatherblock');
	if (state.refreshInterval) {
		clearInterval(state.refreshInterval);
	}
});
