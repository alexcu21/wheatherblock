// This file contains the interactive city input component
// It uses WordPress Interactivity API directives in the HTML markup

// The actual interactive markup will be rendered in the save.js file
// This file serves as a reference for the interactive functionality

export const cityInputDirectives = {
	// Input field directives
	cityInput: 'data-wp-on--input="actions.updateCityName"',
	cityKeyPress: 'data-wp-on--keypress="actions.handleKeyPress"',
	cityValue: 'data-wp-bind--value="state.cityName"',
	
	// Form submission directives
	formSubmit: 'data-wp-on--submit="actions.handleCitySubmit"',
	
	// Button click directive
	buttonClick: 'data-wp-on--click="actions.handleCitySubmit"',
	
	// Loading state for button
	buttonDisabled: 'data-wp-bind--disabled="state.isLoading"',
	buttonText: 'data-wp-text="state.isLoading ? \'Loading...\' : \'Get Weather\'"'
};
