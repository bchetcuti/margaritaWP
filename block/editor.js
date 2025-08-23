( function() {
    const el = wp.element.createElement;
    const registerBlockType = wp.blocks.registerBlockType;

    registerBlockType('margarita/measurements', {
        edit: function() {
            return el('div', { className: 'mm-block-editor' }, 'Margarita Measurements will render on the front-end.');
        },
        save: function() { return null; } // Server-side render
    });
} )();
