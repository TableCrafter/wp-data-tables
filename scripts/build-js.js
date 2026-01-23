/**
 * JavaScript Build Script using esbuild
 *
 * Minifies JavaScript files for production use.
 * Run with: npm run build:js
 * Watch mode: npm run build:js -- --watch
 */

const esbuild = require('esbuild');
const path = require('path');
const fs = require('fs');

const isWatch = process.argv.includes('--watch');

// Define files to build
const jsFiles = [
    {
        input: 'assets/js/tablecrafter.js',
        output: 'assets/js/tablecrafter.min.js'
    },
    {
        input: 'assets/js/admin.js',
        output: 'assets/js/admin.min.js'
    },
    {
        input: 'assets/js/block.js',
        output: 'assets/js/block.min.js'
    },
    {
        input: 'assets/js/frontend.js',
        output: 'assets/js/frontend.min.js'
    },
    {
        input: 'assets/js/performance-optimizer.js',
        output: 'assets/js/performance-optimizer.min.js'
    },
    {
        input: 'assets/js/elementor-preview.js',
        output: 'assets/js/elementor-preview.min.js'
    }
];

// Build configuration
const buildConfig = {
    bundle: false, // Don't bundle, just minify
    minify: true,
    sourcemap: true,
    target: ['es2018'],
    format: 'iife',
    legalComments: 'none',
    drop: ['debugger'],
    pure: ['console.log', 'console.debug'], // Remove debug statements
};

async function build() {
    console.log('Building JavaScript files...\n');

    for (const file of jsFiles) {
        const inputPath = path.resolve(process.cwd(), file.input);
        const outputPath = path.resolve(process.cwd(), file.output);

        // Check if input file exists
        if (!fs.existsSync(inputPath)) {
            console.warn(`Warning: ${file.input} not found, skipping...`);
            continue;
        }

        try {
            const result = await esbuild.build({
                ...buildConfig,
                entryPoints: [inputPath],
                outfile: outputPath,
            });

            const inputSize = fs.statSync(inputPath).size;
            const outputSize = fs.existsSync(outputPath) ? fs.statSync(outputPath).size : 0;
            const savings = ((1 - outputSize / inputSize) * 100).toFixed(1);

            console.log(`✓ ${file.input}`);
            console.log(`  → ${file.output} (${(outputSize / 1024).toFixed(1)}KB, ${savings}% smaller)\n`);
        } catch (error) {
            console.error(`✗ Error building ${file.input}:`, error.message);
        }
    }

    console.log('Build complete!');
}

async function watch() {
    console.log('Watching JavaScript files for changes...\n');

    for (const file of jsFiles) {
        const inputPath = path.resolve(process.cwd(), file.input);
        const outputPath = path.resolve(process.cwd(), file.output);

        if (!fs.existsSync(inputPath)) {
            continue;
        }

        try {
            const ctx = await esbuild.context({
                ...buildConfig,
                entryPoints: [inputPath],
                outfile: outputPath,
            });

            await ctx.watch();
            console.log(`Watching: ${file.input}`);
        } catch (error) {
            console.error(`Error setting up watch for ${file.input}:`, error.message);
        }
    }

    console.log('\nPress Ctrl+C to stop watching...');
}

// Run
if (isWatch) {
    watch();
} else {
    build();
}
