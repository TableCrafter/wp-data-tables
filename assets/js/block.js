/**
 * TableCrafter Gutenberg Block
 * 
 * Provides a native WordPress editing experience with a live preview 
 * and a comprehensive sidebar for data configuration.
 */
(function (blocks, editor, components, serverSideRender, element) {
    const el = element.createElement;
    const { InspectorControls } = editor;
    const { PanelBody, TextControl, ToggleControl, ExternalLink, SelectControl } = components;

    blocks.registerBlockType('tablecrafter/data-table', {
        title: 'TableCrafter',
        description: 'Create dynamic, SEO-friendly data tables from any JSON source.',
        icon: el('svg', { width: 24, height: 24, viewBox: '0 0 24 24', fill: 'currentColor' },
            el('path', { d: 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z' }),
            el('circle', { cx: 18.5, cy: 5.5, r: 2.5, fill: '#0073aa' }),
            el('path', { d: 'M18.5 7c.8 0 1.5-.7 1.5-1.5S19.3 4 18.5 4 17 4.7 17 5.5 17.7 7 18.5 7z', fill: 'white' })
        ),
        category: 'widgets',

        // Define block attributes to persist in database
        attributes: {
            source: { type: 'string', default: '' },
            root: { type: 'string', default: '' },
            include: { type: 'string', default: '' },
            exclude: { type: 'string', default: '' },
            search: { type: 'boolean', default: false },
            filters: { type: 'boolean', default: true },
            export: { type: 'boolean', default: false },
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
                        el(SelectControl, {
                            label: 'Quick Demo',
                            value: attributes.source,
                            options: window.tablecrafterData ? window.tablecrafterData.demoUrls : [],
                            onChange: updateSource,
                            help: 'Select a demo dataset to quickly see how TableCrafter works.'
                        }),
                        el(TextControl, {
                            label: 'JSON Root Path (Optional)',
                            value: attributes.root,
                            onChange: updateRoot,
                            help: 'Dot-notation path to the data array (e.g., data.items).'
                        }),
                        el(ToggleControl, {
                            label: 'Enable Search',
                            checked: attributes.search,
                            onChange: (val) => setAttributes({ search: val }),
                            help: 'Adds a real-time search bar above the table.'
                        }),
                        el(ToggleControl, {
                            label: 'Enable Filters',
                            checked: attributes.filters !== false,
                            onChange: (val) => setAttributes({ filters: val }),
                            help: 'Adds specific column filters (multiselect, ranges, etc.)'
                        }),
                        el(ToggleControl, {
                            label: 'Enable Export Tools',
                            checked: attributes.export,
                            onChange: (val) => setAttributes({ export: val }),
                            help: 'Adds CSV export and Copy to Clipboard buttons.'
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

    /**
     * Editor Preview Initialization
     * 
     * Since the block preview is rendered on the server (SSR), we need to 
     * initialize the TableCrafter tools (search, export, etc.) client-side 
     * once the HTML is injected into the editor.
     */
    const initPreview = (container) => {
        if (!container) return;

        const source = container.getAttribute('data-source');
        const search = container.getAttribute('data-search') === 'true';
        const filters = container.getAttribute('data-filters') !== 'false';
        const exportable = container.getAttribute('data-export') === 'true';
        const perPage = parseInt(container.getAttribute('data-per-page')) || 0;

        console.log('TableCrafter Block: Checking container', {
            id: container.id,
            source,
            search,
            filters,
            exportable,
            perPage,
            initialized: container.dataset.tcInitialized,
            tcSearch: container.dataset.tcSearch,
            tcExport: container.dataset.tcExport,
            ssr: container.dataset.ssr,
            hasSearchUI: !!container.querySelector('.tc-global-search'),
            hasFiltersUI: !!container.querySelector('.tc-filters'),
            hasControlsUI: !!container.querySelector('.tc-controls')
        });

        // Aggressive re-initialization check
        const libLoaded = typeof window.TableCrafter !== 'undefined' || (container.ownerDocument.defaultView && container.ownerDocument.defaultView.TableCrafter);

        if (source && libLoaded) {
            const TC = window.TableCrafter || container.ownerDocument.defaultView.TableCrafter;

            // Force re-init if settings changed
            if (container.dataset.tcSearch !== search.toString() || 
                container.dataset.tcFilters !== filters.toString() || 
                container.dataset.tcExport !== exportable.toString() ||
                container.dataset.tcPerPage !== perPage.toString() ||
                container.dataset.tcInitialized !== 'true') {
                console.log('TableCrafter Block: (Re)Initializing instance', {
                    oldSearch: container.dataset.tcSearch,
                    newSearch: search.toString(),
                    oldExport: container.dataset.tcExport,
                    newExport: exportable.toString(),
                    wasInitialized: container.dataset.tcInitialized
                });

                // Clear all TableCrafter data attributes and content to force clean re-init
                container.removeAttribute('data-tc-initialized');
                container.removeAttribute('data-tc-loaded');
                container.dataset.ssr = "false"; // Disable SSR mode for re-init
                
                // Clear any existing TableCrafter UI elements
                const existingUI = container.querySelectorAll('.tc-controls, .tc-filters, .tc-global-search, .tc-pagination');
                console.log('TableCrafter Block: Removing existing UI elements', existingUI.length);
                existingUI.forEach(el => el.remove());

                const config = {
                    data: source,
                    responsive: true,
                    pagination: perPage > 0,
                    pageSize: perPage > 0 ? perPage : 25,
                    globalSearch: search,
                    filterable: filters,
                    exportable: exportable,
                    api: {
                        proxy: {
                            url: (window.tablecrafterData && window.tablecrafterData.ajaxUrl) ? window.tablecrafterData.ajaxUrl : undefined,
                            nonce: (window.tablecrafterData && window.tablecrafterData.nonce) ? window.tablecrafterData.nonce : undefined
                        }
                    },
                    // Force a fresh render
                    forceRender: true
                };
                
                console.log('TableCrafter Block: Creating new instance with config', config);
                const tcInstance = new TC(container, config);
                
                console.log('TableCrafter Block: Instance created', {
                    instance: tcInstance,
                    hasSearchAfter: !!container.querySelector('.tc-global-search'),
                    hasFiltersAfter: !!container.querySelector('.tc-filters'),
                    hasControlsAfter: !!container.querySelector('.tc-controls')
                });

                container.dataset.tcInitialized = 'true';
                container.dataset.tcSearch = search.toString();
                container.dataset.tcFilters = filters.toString();
                container.dataset.tcExport = exportable.toString();
                container.dataset.tcPerPage = perPage.toString();
                
                // Double check after a short delay
                setTimeout(() => {
                    console.log('TableCrafter Block: Post-init check', {
                        id: container.id,
                        search,
                        hasSearchUI: !!container.querySelector('.tc-global-search'),
                        hasFiltersUI: !!container.querySelector('.tc-filters'),
                        hasControlsUI: !!container.querySelector('.tc-controls'),
                        containerHTML: container.innerHTML.substring(0, 500) + '...'
                    });
                }, 100);
            }
        } else if (source) {
            console.warn('TableCrafter Block: Library not found, retrying in 1s...');
            setTimeout(() => initPreview(container), 1000);
        }
    };

    // Initial scan for blocks already in the DOM (including iframes)
    const scanForBlocks = (root = document) => {
        // Find in current document
        const containers = root.querySelectorAll('.tablecrafter-container');
        containers.forEach(initPreview);

        // Find in iframes (Gutenberg often uses iframes for the editor content)
        const iframes = root.querySelectorAll('iframe');
        iframes.forEach(iframe => {
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                if (iframeDoc) {
                    scanForBlocks(iframeDoc);
                }
            } catch (e) {
                // Ignore cross-origin iframe errors
            }
        });
    };

    // Track observed documents to avoid duplicates
    const observedDocs = new Set();

    // Use MutationObserver to detect when SSR content is added, replaced, or changed
    const createObserver = () => {
        return new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) { // Element
                            if (node.classList.contains('tablecrafter-container')) {
                                initPreview(node);
                            } else if (node.tagName === 'IFRAME') {
                                watchDocument(node.contentDocument);
                            } else {
                                const containers = node.querySelectorAll('.tablecrafter-container');
                                containers.forEach(initPreview);

                                // Also scan for nested iframes
                                const iframes = node.querySelectorAll('iframe');
                                iframes.forEach(iframe => watchDocument(iframe.contentDocument));
                            }
                        }
                    });
                } else if (mutation.type === 'attributes') {
                    const node = mutation.target;
                    if (node.classList.contains('tablecrafter-container')) {
                        initPreview(node);
                    }
                }
            });
        });
    };

    const watchDocument = (doc) => {
        if (!doc || observedDocs.has(doc)) return;

        try {
            const observer = createObserver();
            observer.observe(doc.body || doc, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['data-search', 'data-source', 'data-export', 'data-per-page']
            });
            observedDocs.add(doc);

            // Initial scan of this document
            const containers = doc.querySelectorAll('.tablecrafter-container');
            containers.forEach(initPreview);

            // Also search for existing iframes in this document
            const iframes = doc.querySelectorAll('iframe');
            iframes.forEach(iframe => {
                if (iframe.contentDocument) {
                    watchDocument(iframe.contentDocument);
                } else {
                    iframe.addEventListener('load', () => watchDocument(iframe.contentDocument));
                }
            });
        } catch (e) {
            // Ignore cross-origin errors
        }
    };

    // Start watching the main document
    watchDocument(document);

    // Periodic safety scan (Gutenberg can be tricky)
    setInterval(() => {
        const doc = document.querySelector('iframe.edit-site-visual-editor__editor-canvas')?.contentDocument || document;
        const containers = doc.querySelectorAll('.tablecrafter-container');
        containers.forEach(initPreview);
    }, 3000);

})(
    window.wp.blocks,
    window.wp.blockEditor || window.wp.editor,
    window.wp.components,
    window.wp.serverSideRender,
    window.wp.element
);
