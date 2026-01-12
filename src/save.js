/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save
 *
 * @return {WPElement} Element to render.
 */
export default function save({ attributes }) {
	const { cityName, background_color, autoRefresh } = attributes;
	const blockProps = useBlockProps.save();

	return (
		<div 
			{...blockProps}
			data-wp-interactive="weatherblock"
			data-wp-context={JSON.stringify({ 
				initialCity: cityName || 'managua',
				autoRefresh: autoRefresh || false 
			})}
			style={{ backgroundColor: background_color }}
		>
			<div className="weatherblock-container">
				{/* City Input Section */}
				<div className="city-input-section">
					<form data-wp-on--submit="actions.handleCitySubmit">
						<label htmlFor="city-input">
							{__('Enter City Name:', 'weatherblock')}
						</label>
						<div className="input-group">
							<input 
								id="city-input"
								type="text"
								placeholder={__('Enter city name...', 'weatherblock')}
								data-wp-on--input="actions.updateCityName"
								data-wp-on--keypress="actions.handleKeyPress"
								data-wp-bind--value="state.cityName"
							/>
							<button 
								type="submit"
								data-wp-bind--disabled="state.isLoading"
							>
								<span data-wp-text="state.isLoading ? 'Loading...' : 'Get Weather'"></span>
							</button>
						</div>
					</form>
				</div>

				{/* Loading State */}
				<div 
					className="loading-state"
					data-wp-bind--hidden="!state.isLoading"
				>
					<div className="loading-spinner"></div>
					<p>{__('Loading weather data...', 'weatherblock')}</p>
				</div>

				{/* Error State */}
				<div 
					className="error-state"
					data-wp-bind--hidden="!state.error"
				>
					<p data-wp-text="state.error"></p>
				</div>

				{/* Weather Data Display */}
				<div 
					className="weather-display"
					data-wp-bind--hidden="!state.weatherData"
				>
					<div className="weather-card">
						<div className="main-weather">
							<div className="city-info">
								<h3 data-wp-text="state.weatherData?.city || 'Unknown City'"></h3>
								<p data-wp-text="state.weatherData?.description || 'No description'"></p>
							</div>
							<div className="weather-icon">
								<img 
									data-wp-bind--src="state.weatherData?.icon_url || ''"
									data-wp-bind--alt="state.weatherData?.description || 'Weather icon'"
									width="64"
									height="64"
								/>
							</div>
						</div>
						
						<div className="weather-details">
							<div className="temperature">
								<span className="temp-value" data-wp-text="state.weatherData?.temperature || 'N/A'"></span>
								<span className="temp-unit">°F</span>
							</div>
							
							<div className="weather-stats">
								<div className="stat-item">
									<img 
										src="/wp-content/plugins/wheatherblock/assets/humidity.png" 
										alt={__('Humidity', 'weatherblock')}
										width="24" 
										height="24"
									/>
									<span data-wp-text="(state.weatherData?.humidity || 'N/A') + ' %'"></span>
								</div>
								<div className="stat-item">
									<img 
										src="/wp-content/plugins/wheatherblock/assets/wind.png" 
										alt={__('Wind Speed', 'weatherblock')}
										width="24" 
										height="24"
									/>
									<span data-wp-text="(state.weatherData?.wind_speed || 'N/A') + ' mi/h'"></span>
								</div>
							</div>
						</div>
					</div>
					
					{/* Weather Controls */}
					<div className="weather-controls">
						<button 
							className="auto-refresh-btn"
							data-wp-on--click="actions.toggleAutoRefresh"
							data-wp-bind--class="state.autoRefresh ? 'active' : ''"
						>
							<span data-wp-text="state.autoRefresh ? 'Stop Auto Refresh' : 'Start Auto Refresh'"></span>
						</button>
						
						<div className="last-updated">
							<small>
								{__('Last updated: ', 'weatherblock')}
								<span data-wp-text="state.lastUpdated || 'Never'"></span>
							</small>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}
