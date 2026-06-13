( function() {
    const el = wp.element.createElement;
    const registerBlockType = wp.blocks.registerBlockType;
    const InspectorControls = wp.blockEditor.InspectorControls;
    const PanelBody = wp.components.PanelBody;
    const SelectControl = wp.components.SelectControl;
    const ToggleControl = wp.components.ToggleControl;
    const TextControl = wp.components.TextControl;
    const useEffect = wp.element.useEffect;
    const useState = wp.element.useState;
    const apiFetch = wp.apiFetch;

    const unitOptions = [
        { label: 'ml', value: 'ml' },
        { label: 'oz', value: 'oz' },
        { label: 'Shot', value: 'shot' },
        { label: 'Nip', value: 'nip' },
    ];

    const flavourOptions = [
        { label: 'None', value: 'none' },
        { label: 'Spicy', value: 'spicy' },
        { label: 'Mango', value: 'mango' },
        { label: 'Watermelon', value: 'watermelon' },
        { label: 'Strawberry', value: 'strawberry' },
        { label: 'Coconut', value: 'coconut' },
        { label: 'Virgin', value: 'virgin' },
    ];

    const modeOptions = [
        { label: 'Per drink count', value: 'drinks' },
        { label: 'Pitcher (total ml)', value: 'pitcher' },
        { label: 'Party Planning', value: 'party' },
    ];

    registerBlockType('margarita/measurements', {
        edit: function( props ) {
            const attributes = props.attributes;
            const setAttributes = props.setAttributes;
            const [ presetOptions, setPresetOptions ] = useState( [ { label: 'Classic', value: 'classic' } ] );

            useEffect( function() {
                apiFetch( { path: '/margarita/v1/presets' } ).then( function( presets ) {
                    if ( Array.isArray( presets ) && presets.length ) {
                        setPresetOptions( presets.map( function( preset ) {
                            return { label: preset.label, value: preset.key };
                        } ) );
                    }
                } ).catch( function() {} );
            }, [] );

            return el(
                wp.element.Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: 'Margarita settings', initialOpen: true },
                        el( SelectControl, {
                            label: 'Preset',
                            value: attributes.preset,
                            options: presetOptions,
                            onChange: function( preset ) { setAttributes( { preset: preset } ); },
                        } ),
                        el( SelectControl, {
                            label: 'Unit',
                            value: attributes.unit,
                            options: unitOptions,
                            onChange: function( unit ) { setAttributes( { unit: unit } ); },
                        } ),
                        el( SelectControl, {
                            label: 'Flavour',
                            value: attributes.flavour,
                            options: flavourOptions,
                            onChange: function( flavour ) { setAttributes( { flavour: flavour } ); },
                        } ),
                        el( SelectControl, {
                            label: 'Mode',
                            value: attributes.mode,
                            options: modeOptions,
                            onChange: function( mode ) { setAttributes( { mode: mode } ); },
                        } ),
                        el( ToggleControl, {
                            label: 'Show ABV',
                            checked: attributes.showAbv,
                            onChange: function( showAbv ) { setAttributes( { showAbv: showAbv } ); },
                        } ),
                        el( TextControl, {
                            label: 'Title',
                            value: attributes.title,
                            onChange: function( title ) { setAttributes( { title: title } ); },
                        } )
                    )
                ),
                el('div', { className: 'mm-block-editor' }, attributes.title || 'Margarita Measurements will render on the front-end.')
            );
        },
        save: function() { return null; } // Server-side render
    });
} )();
