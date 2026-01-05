import React from 'react'


const CityNameInput = (props) => {

    const {
		label,
		value,
		onChange,
		type = 'text',
	} = props;

    const onChangeValue = ( event ) => onChange( event.target.value );
    
    return (
        <>
            <label>{label}</label><br/>
            <input
                type={ type }
                value={ value }
                onChange={ onChangeValue }
            />
        </>
    )
}

export default CityNameInput;
