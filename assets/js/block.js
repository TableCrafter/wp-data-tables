(function (blocks, editor, components, serverSideRender, element) {
    const el = element.createElement;
    const { InspectorControls } = editor;
    const { PanelBody, TextControl, ExternalLink } = components;

    blocks.registerBlockType('tablecrafter/data-table', {
        title: 'TableCrafter',
        icon: 'table-viewport',
        category: 'widgets',
        attributes: {
            source: { type: 'string', default: '' },
            root: { type: 'string', default: '' },
            include: { type: 'string', default: '' },
            exclude: { type: 'string', default: '' },
            search: { type: 'boolean', default: false },
            id: { type: 'string', default: '' }
        },

        edit: function (props) {
            const { attributes, setAttributes } = props;
            const { PanelBody, TextControl, ToggleControl, ExternalLink } = components;

            const updateSource = (value) => setAttributes({ source: value });
            const updateRoot = (value) => setAttributes({ root: value });
            const updateInclude = (value) => setAttributes({ include: value });
            const updateExclude = (value) => setAttributes({ exclude: value });
            const updateSearch = (value) => setAttributes({ search: value });

            return [
                el(InspectorControls, { key: 'controls' },
                    el(PanelBody, { title: 'Data Settings', initialOpen: true },
                        el(TextControl, {
                            label: 'JSON Source URL',
                            value: attributes.source,
                            onChange: updateSource,
                            help: 'The URL of your JSON data source.'
                        }),
                        el(TextControl, {
                            label: 'JSON Root Path (Optional)',
                            value: attributes.root,
                            onChange: updateRoot,
                            help: 'Dot-notation path to the data array (e.g., data.items).'
                        }),
                        el(ToggleControl, {
                            label: 'Enable Live Search',
                            checked: attributes.search,
                            onChange: updateSearch,
                            help: 'Adds a real-time search bar above the table.'
                        }),
                        el(TextControl, {
                            label: 'Include Columns (Optional)',
                            value: attributes.include,
                            onChange: updateInclude,
                            help: 'Comma-separated list of keys to show.'
                        }),
                        el(TextControl, {
                            label: 'Exclude Columns (Optional)',
                            value: attributes.exclude,
                            onChange: updateExclude,
                            help: 'Comma-separated list of keys to hide.'
                        }),
                        el('div', { className: 'tc-block-help' },
                            el('p', null, 'Need help? Check the '),
                            el(ExternalLink, { href: 'https://github.com/TableCrafter/wp-data-tables' }, 'Documentation')
                        )
                    )
                ),
                el('div', { className: props.className, key: 'preview' },
                    el(serverSideRender, {
                        block: 'tablecrafter/data-table',
                        attributes: attributes
                    })
                )
            ];
        },

        save: function () {
            // Block is dynamic and rendered via PHP render_callback
            return null;
        },
    });
})(
    window.wp.blocks,
    window.wp.blockEditor || window.wp.editor,
    window.wp.components,
    window.wp.serverSideRender,
    window.wp.element
);
