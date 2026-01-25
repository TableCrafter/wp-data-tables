module.exports = {
    env: {
        browser: true,
        es6: true,
        jquery: true
    },
    extends: ['eslint:recommended'],
    parserOptions: {
        ecmaVersion: 2020,
        sourceType: 'module'
    },
    globals: {
        // WordPress globals
        wp: 'readonly',
        jQuery: 'readonly',
        $: 'readonly',
        ajaxurl: 'readonly',
        
        // TableCrafter globals
        tablecrafterAdmin: 'readonly',
        tablecrafterData: 'readonly',
        tablecrafter_admin: 'readonly',
        tablecrafter_frontend: 'readonly',
        TableCrafter: 'readonly',
        
        // Elementor globals
        elementor: 'readonly',
        elementorFrontend: 'readonly',
        
        // Module globals (for UMD pattern)
        module: 'readonly',
        define: 'readonly'
    },
    rules: {
        // Relaxed rules for WordPress compatibility
        'no-undef': 'error',
        'no-unused-vars': 'warn',
        'no-console': 'warn',
        'semi': ['error', 'always'],
        'quotes': ['warn', 'single', { 'allowTemplateLiterals': true }],
        
        // WordPress specific
        'camelcase': 'off', // WordPress uses snake_case
        'no-alert': 'warn',
        'no-case-declarations': 'warn',
        'no-dupe-class-members': 'warn'
    },
    ignorePatterns: [
        'node_modules/',
        'vendor/',
        'tests/',
        '*.min.js',
        'playwright.config.js'
    ]
};