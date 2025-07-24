# Weather Block

**Contributors:** Alex Cuadra  
**Tags:** block, weather, api, openweathermap  
**Tested up to:** 6.4  
**Stable tag:** 0.1.0  
**License:** GPL-2.0-or-later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  
**Requires at least:** 5.8  
**Requires PHP:** 7.0  

## Description

Weather Block is a simple and elegant WordPress block that displays real-time weather information for any city. The block fetches weather data from OpenWeatherMap API and presents it in a clean, responsive design.

### Features

- 🌤️ Real-time weather data from OpenWeatherMap API
- 🏙️ Customizable city input
- 🌡️ Temperature display in Fahrenheit
- 💧 Humidity percentage
- 💨 Wind speed in miles per hour
- 🎨 Clean, modern UI design
- 📱 Responsive layout
- 🔒 Secure API integration

### Screenshots

The weather block displays:
- City name and weather description
- Weather icon from OpenWeatherMap
- Current temperature in Fahrenheit
- Humidity percentage with icon
- Wind speed with icon

## Installation

### Method 1: WordPress Admin (Recommended)

1. **Download the Plugin**
   - Download the Weather Block plugin ZIP file
   - Or clone the repository to your local machine

2. **Install via WordPress Admin**
   - Log in to your WordPress admin dashboard
   - Navigate to **Plugins** → **Add New**
   - Click **Upload Plugin** at the top of the page
   - Choose the Weather Block ZIP file
   - Click **Install Now**
   - After installation, click **Activate Plugin**

### Method 2: Manual Installation

1. **Upload Files**
   - Extract the Weather Block ZIP file
   - Upload the `weatherblock` folder to your `/wp-content/plugins/` directory
   - Ensure the folder structure is: `/wp-content/plugins/weatherblock/`

2. **Activate the Plugin**
   - Go to your WordPress admin dashboard
   - Navigate to **Plugins** → **Installed Plugins**
   - Find "Weather Block" in the list
   - Click **Activate**

### Method 3: FTP Upload

1. **Connect via FTP**
   - Use an FTP client to connect to your server
   - Navigate to `/wp-content/plugins/`

2. **Upload Files**
   - Upload the entire `weatherblock` folder
   - Ensure all files are uploaded correctly

3. **Activate**
   - Go to WordPress admin → **Plugins**
   - Activate the Weather Block plugin

## Usage

### Adding the Weather Block

1. **Edit a Post or Page**
   - Create a new post/page or edit an existing one
   - Open the block editor (Gutenberg)

2. **Insert the Block**
   - Click the **+** button to add a new block
   - Search for "Weather Block" or "Weather"
   - Click on the Weather Block to insert it

3. **Configure the Block**
   - In the block settings panel (right sidebar)
   - Enter a city name in the "City Name" field
   - The block will automatically fetch and display weather data

### Block Settings

- **City Name**: Enter any city name (default: Managua)
- The block automatically updates with current weather information
- Weather data includes temperature, humidity, and wind speed

## Frequently Asked Questions

### What weather data is displayed?

The block shows:
- Current temperature in Fahrenheit
- Weather description (e.g., "clear sky", "rain")
- Humidity percentage
- Wind speed in miles per hour
- Weather icon from OpenWeatherMap

### Can I change the default city?

Yes! Simply edit the "City Name" field in the block settings to display weather for any city.

### Is the weather data real-time?

Yes, the block fetches current weather data from OpenWeatherMap API each time the page loads.

### Does this plugin require an API key?

The plugin uses a built-in API key for OpenWeatherMap. No additional setup is required.

## Changelog

### 0.1.0
- Initial release
- Weather block with city input
- Temperature, humidity, and wind speed display
- OpenWeatherMap API integration

## Support

For support, feature requests, or bug reports, please contact the plugin author.

## Credits

- Weather data provided by [OpenWeatherMap](https://openweathermap.org/)
- Icons from OpenWeatherMap API
- Built with WordPress Block Editor (Gutenberg)


