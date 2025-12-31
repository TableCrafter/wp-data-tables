/**
 * TableCrafter Gutenberg Block
 * 
 * Provides a native WordPress editing experience with a live preview 
 * and a comprehensive sidebar for data configuration.
 */
(function (blocks, editor, components, serverSideRender, element) {
    const el = element.createElement;
    const { InspectorControls } = editor;
    const { PanelBody, TextControl, ToggleControl, ExternalLink } = components;

    blocks.registerBlockType('tablecrafter/data-table', {
        title: 'TableCrafter',
        description: 'Create dynamic, SEO-friendly data tables from any JSON source.',
        icon: 'table-viewport',
        category: 'widgets',

        // Define block attributes to persist in database
        attributes: {
            source: { type: 'string', default: '' },
            root: { type: 'string', default: '' },
            include: { type: 'string', default: '' },
            exclude: { type: 'string', default: '' },
            search: { type: 'boolean', default: false },
            per_page: { type: 'number', default: 0 },
            id: { type: 'string', default: '' }
        },

        /**
         * The edit function describes the structure of your block in the context of the editor.
         * This represents what the editor will render when the block is used.
         */
        edit: function (props) {
            const { attributes, setAttributes } = props;

            // Attribute update helpers
            const updateSource = (value) => setAttributes({ source: value });
            const updateRoot = (value) => setAttributes({ root: value });
            const updateInclude = (value) => setAttributes({ include: value });
            const updateExclude = (value) => setAttributes({ exclude: value });
            const updateSearch = (value) => setAttributes({ search: value });
            const updatePerPage = (value) => setAttributes({ per_page: parseInt(value) || 0 });

            return [
                // Sidebar controls (Inspector)
                el(InspectorControls, { key: 'controls' },
                    el(PanelBody, { title: 'Data Settings', initialOpen: true },
                        el(TextControl, {
                            label: 'JSON Source URL',
                            value: attributes.source,
                            onChange: updateSource,
                            help: 'The URL of your JSON data source (must be public).'
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
                            label: 'Rows Per Page',
                            value: attributes.per_page,
                            type: 'number',
                            onChange: updatePerPage,
                            help: 'Number of rows to show per page (0 for all).'
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
                        el('div', { className: 'tc-block-help', style: { marginTop: '20px', borderTop: '1px solid #eee', paddingTop: '15px' } },
                            el('p', null, 'Need help? Check the '),
                            el(ExternalLink, { href: 'https://github.com/TableCrafter/wp-data-tables' }, 'Documentation')
                        )
                    )
                ),
                // Main visual editor view (Live Preview)
                el('div', { className: props.className, key: 'preview' },
                    el(serverSideRender, {
                        block: 'tablecrafter/data-table',
                        attributes: attributes
                    })
                )
            ];
        },

        /**
         * The save function defines the frontend markup.
         * Since this is a dynamic block, we return null and handle rendering in PHP.
         */
        save: function () {
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
