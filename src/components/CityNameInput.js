const CityNameInput = ( props ) => {
	const { label, value, onChange, type = 'text' } = props;

	const onChangeValue = ( event ) => onChange( event.target.value );

	return (
		<>
			<label
				htmlFor={ `city-input-${ Math.random()
					.toString( 36 )
					.substr( 2, 9 ) }` }
			>
				{ label }
			</label>
			<br />
			<input
				id={ `city-input-${ Math.random()
					.toString( 36 )
					.substr( 2, 9 ) }` }
				type={ type }
				value={ value }
				onChange={ onChangeValue }
			/>
		</>
	);
};

export default CityNameInput;
