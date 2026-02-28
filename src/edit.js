/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import './editor.scss';
import {
	TextControl,
	ColorPalette,
	ToggleControl,
	RangeControl,
	PanelBody,
} from '@wordpress/components';
import CityNameInput from './components/CityNameInput';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */

export default function Edit( props ) {
	const { attributes, setAttributes } = props;
	const { cityName, background_color, autoRefresh, refreshInterval } =
		attributes;

	const onChangeBackColor = ( color ) => {
		setAttributes( { background_color: color } );
	};

	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Background Settings', 'weatherblock' ) }
					initialOpen={ true }
				>
					<fieldset>
						<legend>
							{ __( 'Background Color', 'weatherblock' ) }
						</legend>
						<ColorPalette
							onChange={ onChangeBackColor }
							value={ background_color }
						/>
					</fieldset>
				</PanelBody>

				<PanelBody
					title={ __( 'Auto Refresh Settings', 'weatherblock' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Enable Auto Refresh', 'weatherblock' ) }
						help={ __(
							'Automatically refresh weather data at set intervals',
							'weatherblock'
						) }
						checked={ autoRefresh }
						onChange={ ( value ) =>
							setAttributes( { autoRefresh: value } )
						}
					/>
					{ autoRefresh && (
						<RangeControl
							label={ __(
								'Refresh Interval (seconds)',
								'weatherblock'
							) }
							value={ refreshInterval }
							onChange={ ( value ) =>
								setAttributes( { refreshInterval: value } )
							}
							min={ 60 }
							max={ 1800 }
							step={ 30 }
							help={ __(
								'How often to refresh the weather data (60-1800 seconds)',
								'weatherblock'
							) }
						/>
					) }
				</PanelBody>
			</InspectorControls>

			<div
				className="wrapper"
				style={ { backgroundColor: background_color } }
			>
				<h3>{ __( 'Interactive Weather Block', 'weatherblock' ) }</h3>
				<p>
					{ __(
						'This block will display real-time weather data with interactive features on the frontend.',
						'weatherblock'
					) }
				</p>

				<div className="editor-preview">
					<CityNameInput
						label={ __( 'Default City Name', 'weatherblock' ) }
						value={ cityName }
						onChange={ ( newCityName ) =>
							setAttributes( { cityName: newCityName } )
						}
					/>
				</div>
			</div>
		</div>
	);
}
