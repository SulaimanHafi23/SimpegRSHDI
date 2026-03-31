#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const https = require('https');

const sourceFolder = 'C:/laragon/www/SimpegRSHDI/docs/SequenceDiagram';
const outputFolder = path.join(sourceFolder, 'PNG_Export');

// Create output folder
if (!fs.existsSync(outputFolder)) {
    fs.mkdirSync(outputFolder, { recursive: true });
    console.log(`Created: ${outputFolder}`);
}

// Get all .mermaid files
const files = fs.readdirSync(sourceFolder)
    .filter(f => f.endsWith('.mermaid'));

console.log(`Found ${files.length} diagrams to export\n`);

let success = 0;
let failed = 0;

async function exportDiagram(filename) {
    return new Promise((resolve) => {
        try {
            const filepath = path.join(sourceFolder, filename);
            const content = fs.readFileSync(filepath, 'utf8');
            const outputPath = path.join(outputFolder, filename.replace('.mermaid', '.png'));

            // URL encode for Mermaid API
            const encoded = encodeURIComponent(content);
            const url = `https://mermaid.ink/img/${encoded}`;

            process.stdout.write(`Converting ${filename}... `);

            https.get(url, (response) => {
                if (response.statusCode === 200) {
                    const file = fs.createWriteStream(outputPath);
                    response.pipe(file);
                    file.on('finish', () => {
                        file.close();
                        console.log('OK');
                        success++;
                        resolve();
                    });
                    file.on('error', () => {
                        console.log('FAILED');
                        failed++;
                        resolve();
                    });
                } else {
                    console.log(`FAILED (${response.statusCode})`);
                    failed++;
                    resolve();
                }
            }).on('error', (err) => {
                console.log('ERROR');
                failed++;
                resolve();
            });
        } catch (err) {
            console.log('ERROR');
            failed++;
            resolve();
        }
    });
}

async function main() {
    for (const file of files) {
        await exportDiagram(file);
        // Add small delay between requests
        await new Promise(r => setTimeout(r, 500));
    }

    console.log(`\nCompleted: ${success} OK, ${failed} Failed`);
    console.log(`Output: ${outputFolder}`);
}

main();
