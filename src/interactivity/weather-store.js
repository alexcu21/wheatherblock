import { store } from '@wordpress/interactivity';

const { state, actions } = store( 'weatherblock', {
	state: {
		isLoading: false,
		weatherData: null,
		error: null,
		lastUpdated: null,
		autoRefresh: false,
		refreshInterval: null,
		cityName: '',
		debounceTimer: null,
	},

	actions: {
		async fetchWeather( cityName ) {
			state.isLoading = true;
			state.error = null;

			if ( ! cityName || ! cityName.trim() ) {
				state.error = 'Please enter a city name';
				state.isLoading = false;
				return;
			}

			try {
				const response = await fetch(
					`/wp-json/weatherblock/v1/weather?city=${ encodeURIComponent(
						cityName
					) }`
				);
				const data = await response.json();

				if ( data.success ) {
					state.weatherData = data.data;
					state.lastUpdated = new Date().toLocaleTimeString();
					state.cityName = cityName;
				} else {
					state.error =
						data.message || 'Failed to fetch weather data';
				}
			} catch ( error ) {
				state.error = 'Network error occurred';
				// Optional: Log to error tracking service instead of console
			} finally {
				state.isLoading = false;
			}
		},

		toggleAutoRefresh() {
			state.autoRefresh = ! state.autoRefresh;

			if ( state.autoRefresh ) {
				// Start auto refresh every 5 minutes
				const refreshInterval = 300000; // 5 minutes
				state.refreshInterval = setInterval( () => {
					if ( state.cityName && ! document.hidden ) {
						actions.fetchWeather( state.cityName );
					}
				}, refreshInterval );

				// Add visibility change listener
				if ( ! document.visibilitychangeListener ) {
					document.visibilitychangeListener = () => {
						if ( document.hidden ) {
							// Pause refresh when tab is hidden
							if ( state.refreshInterval ) {
								clearInterval( state.refreshInterval );
								state.refreshInterval = null;
							}
						} else if ( state.autoRefresh && state.cityName ) {
							// Resume refresh when tab becomes visible
							state.refreshInterval = setInterval( () => {
								if ( ! document.hidden && state.cityName ) {
									actions.fetchWeather( state.cityName );
								}
							}, 300000 );
						}
					};
					document.addEventListener(
						'visibilitychange',
						document.visibilitychangeListener
					);
				}
			} else {
				// Stop auto refresh
				if ( state.refreshInterval ) {
					clearInterval( state.refreshInterval );
					state.refreshInterval = null;
				}

				// Remove visibility change listener
				if ( document.visibilitychangeListener ) {
					document.removeEventListener(
						'visibilitychange',
						document.visibilitychangeListener
					);
					document.visibilitychangeListener = null;
				}
			}
		},

		updateCityName( event ) {
			const cityName = event.target.value.trim();
			state.cityName = cityName;

			// Clear existing debounce timer
			if ( state.debounceTimer ) {
				clearTimeout( state.debounceTimer );
			}

			// Set new debounce timer for auto-fetch
			state.debounceTimer = setTimeout( () => {
				if ( cityName.length > 2 ) {
					actions.fetchWeather( cityName );
				}
			}, 500 ); // 500ms debounce
		},

		handleCitySubmit( event ) {
			event.preventDefault();
			if ( state.cityName ) {
				actions.fetchWeather( state.cityName );
			}
		},

		handleKeyPress( event ) {
			if ( event.key === 'Enter' ) {
				event.preventDefault();
				if ( state.cityName ) {
					actions.fetchWeather( state.cityName );
				}
			}
		},
	},
} );
