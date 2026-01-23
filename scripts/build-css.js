/**
 * CSS Build Script using clean-css
 *
 * Minifies CSS files for production use.
 * Run with: npm run build:css
 * Watch mode: npm run build:css -- --watch
 */

const CleanCSS = require('clean-css');
const path = require('path');
const fs = require('fs');

const isWatch = process.argv.includes('--watch');

// Define files to build
const cssFiles = [
    {
        input: 'assets/css/tablecrafter.css',
        output: 'assets/css/tablecrafter.min.css'
    }
];

// Clean-CSS options
const cleanCSSOptions = {
    level: {
        1: {
            all: true,
            specialComments: 0
        },
        2: {
            mergeAdjacentRules: true,
            mergeIntoShorthands: true,
            mergeMedia: true,
            mergeNonAdjacentRules: true,
            mergeSemantically: false,
            overrideProperties: true,
            removeEmpty: true,
            reduceNonAdjacentRules: true,
            removeDuplicateFontRules: true,
            removeDuplicateMediaBlocks: true,
            removeDuplicateRules: true,
            removeUnusedAtRules: false
        }
    },
    sourceMap: true,
    sourceMapInlineSources: true
};

function buildFile(file) {
    const inputPath = path.resolve(process.cwd(), file.input);
    const outputPath = path.resolve(process.cwd(), file.output);

    // Check if input file exists
    if (!fs.existsSync(inputPath)) {
        console.warn(`Warning: ${file.input} not found, skipping...`);
        return false;
    }

    try {
        const input = fs.readFileSync(inputPath, 'utf8');
        const output = new CleanCSS(cleanCSSOptions).minify(input);

        if (output.errors && output.errors.length > 0) {
            console.error(`✗ Errors in ${file.input}:`);
            output.errors.forEach(err => console.error(`  ${err}`));
            return false;
        }

        if (output.warnings && output.warnings.length > 0) {
            console.warn(`Warnings in ${file.input}:`);
            output.warnings.forEach(warn => console.warn(`  ${warn}`));
        }

        // Write minified CSS
        fs.writeFileSync(outputPath, output.styles);

        // Write source map
        if (output.sourceMap) {
            fs.writeFileSync(outputPath + '.map', output.sourceMap.toString());
        }

        const inputSize = Buffer.byteLength(input, 'utf8');
        const outputSize = Buffer.byteLength(output.styles, 'utf8');
        const savings = ((1 - outputSize / inputSize) * 100).toFixed(1);

        console.log(`✓ ${file.input}`);
        console.log(`  → ${file.output} (${(outputSize / 1024).toFixed(1)}KB, ${savings}% smaller)`);

        return true;
    } catch (error) {
        console.error(`✗ Error building ${file.input}:`, error.message);
        return false;
    }
}

function build() {
    console.log('Building CSS files...\n');

    let success = 0;
    let failed = 0;

    for (const file of cssFiles) {
        if (buildFile(file)) {
            success++;
        } else {
            failed++;
        }
    }

    console.log(`\nBuild complete! ${success} succeeded, ${failed} failed.`);
}

function watch() {
    console.log('Watching CSS files for changes...\n');

    // Initial build
    build();

    // Watch for changes
    for (const file of cssFiles) {
        const inputPath = path.resolve(process.cwd(), file.input);

        if (!fs.existsSync(inputPath)) {
            continue;
        }

        console.log(`Watching: ${file.input}`);

        fs.watch(inputPath, (eventType) => {
            if (eventType === 'change') {
                console.log(`\n[${new Date().toLocaleTimeString()}] ${file.input} changed`);
                buildFile(file);
            }
        });
    }

    console.log('\nPress Ctrl+C to stop watching...');
}

// Run
if (isWatch) {
    watch();
} else {
    build();
}
