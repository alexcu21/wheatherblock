// This file contains the interactive weather display component
// It uses WordPress Interactivity API directives in the HTML markup

// The actual interactive markup will be rendered in the save.js file
// This file serves as a reference for the interactive functionality

export const weatherDisplayDirectives = {
	// Weather data binding directives
	cityName: 'data-wp-text="state.weatherData?.city || \'Unknown City\'"',
	description: 'data-wp-text="state.weatherData?.description || \'No description\'"',
	temperature: 'data-wp-text="state.weatherData?.temperature || \'N/A\'"',
	humidity: 'data-wp-text="(state.weatherData?.humidity || \'N/A\') + \' %\'"',
	windSpeed: 'data-wp-text="(state.weatherData?.wind_speed || \'N/A\') + \' mi/h\'"',
	iconUrl: 'data-wp-bind--src="state.weatherData?.icon_url || \'\'"',
	iconAlt: 'data-wp-bind--alt="state.weatherData?.description || \'Weather icon\'"',
	
	// Visibility and state directives
	showWeatherData: 'data-wp-bind--hidden="!state.weatherData"',
	showLoading: 'data-wp-bind--hidden="!state.isLoading"',
	showError: 'data-wp-bind--hidden="!state.error"',
	
	// Auto refresh button directives
	autoRefreshToggle: 'data-wp-on--click="actions.toggleAutoRefresh"',
	autoRefreshClass: 'data-wp-bind--class="state.autoRefresh ? \'active\' : \'\'"',
	autoRefreshText: 'data-wp-text="state.autoRefresh ? \'Stop Auto Refresh\' : \'Start Auto Refresh\'"',
	
	// Last updated directive
	lastUpdated: 'data-wp-text="state.lastUpdated || \'Never\'"',
	
	// Error message directive
	errorMessage: 'data-wp-text="state.error"'
};
