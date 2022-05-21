/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import './editor.scss';
import { TextControl } from '@wordpress/components';

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

	const blockProps = useBlockProps()

	return (
		<div {...blockProps}>
			<h3>The Weather at:</h3>
			<TextControl
				label="insert your city name"
				value={cityName}
				onChange={(newCityName) => setAttributes({ cityName: newCityName})}
			/>	

		</div>
	);
}