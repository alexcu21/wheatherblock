import { store } from '@wordpress/interactivity';

const { state, actions } = store('weatherblock', {
	state: {
		isLoading: false,
		weatherData: null,
		error: null,
		lastUpdated: null,
		autoRefresh: false,
		refreshInterval: null,
		cityName: ''
	},
	
	actions: {
		async fetchWeather(cityName) {
			state.isLoading = true;
			state.error = null;
			
			if (!cityName || !cityName.trim()) {
				state.error = 'Please enter a city name';
				state.isLoading = false;
				return;
			}
			
			try {
				const response = await fetch(`/wp-json/weatherblock/v1/weather?city=${encodeURIComponent(cityName)}`);
				const data = await response.json();
				
				if (data.success) {
					state.weatherData = data.data;
					state.lastUpdated = new Date().toLocaleTimeString();
					state.cityName = cityName;
				} else {
					state.error = data.message || 'Failed to fetch weather data';
				}
			} catch (error) {
				state.error = 'Network error occurred';
				console.error('Weather API Error:', error);
			} finally {
				state.isLoading = false;
			}
		},
		
		toggleAutoRefresh() {
			state.autoRefresh = !state.autoRefresh;
			
			if (state.autoRefresh) {
				// Start auto refresh every 5 minutes
				state.refreshInterval = setInterval(() => {
					if (state.cityName) {
						actions.fetchWeather(state.cityName);
					}
				}, 300000); // 5 minutes
			} else {
				if (state.refreshInterval) {
					clearInterval(state.refreshInterval);
					state.refreshInterval = null;
				}
			}
		},
		
		updateCityName(event) {
			const cityName = event.target.value.trim();
			state.cityName = cityName;
		},
		
		handleCitySubmit(event) {
			event.preventDefault();
			if (state.cityName) {
				actions.fetchWeather(state.cityName);
			}
		},
		
		handleKeyPress(event) {
			if (event.key === 'Enter') {
				event.preventDefault();
				if (state.cityName) {
					actions.fetchWeather(state.cityName);
				}
			}
		}
	}
});
