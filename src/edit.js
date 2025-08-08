/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import './editor.scss';
import { TextControl, ColorPalette } from '@wordpress/components';
import CityNameInput from './components/CityNameInput'

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */


export default function Edit(props) {

	const { attributes, setAttributes } = props

	const { cityName } = attributes

	const onChangeBackColor = (color) => {
		setAttributes({background_color: color})
	}

	const blockProps = useBlockProps()

	return (
		<div {...blockProps}>
			<InspectorControls>
				<fieldset>
					<legend>
						Color de fondo
					</legend>
				<ColorPalette
					 onChange={ onChangeBackColor }
				/>
				</fieldset>
			</InspectorControls>
			<div className='wrapper' style={{backgroundColor: attributes.bg_color}} >
				<p>Type the city’s name below, and get the wheather information.</p>
				<CityNameInput
					label="City's Name"
					value={cityName}
					onChange={(newCityName) => setAttributes({ cityName: newCityName})}
				/>
			</div>
		</div>
	);
}