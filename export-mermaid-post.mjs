import fs from 'fs';
import path from 'path';
import https from 'https';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const sourceFolder = path.join(__dirname, 'docs/SequenceDiagram');
const outputFolder = path.join(sourceFolder, 'PNG_Export');

if (!fs.existsSync(outputFolder)) {
    fs.mkdirSync(outputFolder, { recursive: true });
}

const files = fs.readdirSync(sourceFolder)
    .filter(f => f.endsWith('.mermaid'))
    .slice(0, 5); // Test with first 5 files

console.log(`Found ${files.length} diagrams to test\n`);

let success = 0;
let failed = 0;

async function exportDiagramViaPost(filename) {
    return new Promise((resolve) => {
        try {
            const filepath = path.join(sourceFolder, filename);
            const content = fs.readFileSync(filepath, 'utf8');
            const outputPath = path.join(outputFolder, filename.replace('.mermaid', '.png'));

            // Use POST method with JSON payload
            const postData = JSON.stringify({
                mermaid: content,
                config: {
                    theme: 'default',
                    logLevel: 3
                }
            });

            const options = {
                hostname: 'mermaid.ink',
                path: '/api/render',
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Content-Length': Buffer.byteLength(postData)
                },
                timeout: 15000
            };

            process.stdout.write(`Converting ${filename}... `);

            const req = https.request(options, (response) => {
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
                        console.log('FAILED (file write)');
                        failed++;
                        resolve();
                    });
                } else {
                    console.log(`FAILED (${response.statusCode})`);
                    failed++;
                    response.on('data', () => {}); // Drain the response
                    resolve();
                }
            });

            req.on('error', (err) => {
                console.log(`ERROR: ${err.message}`);
                failed++;
                resolve();
            });

            req.on('timeout', () => {
                req.destroy();
                console.log('TIMEOUT');
                failed++;
                resolve();
            });

            req.write(postData);
            req.end();

        } catch (err) {
            console.log(`ERROR: ${err.message}`);
            failed++;
            resolve();
        }
    });
}

async function main() {
    for (const file of files) {
        await exportDiagramViaPost(file);
        await new Promise(r => setTimeout(r, 1500));
    }

    console.log(`\nTest Results: ${success} OK, ${failed} Failed`);
    console.log(`Output folder: ${outputFolder}`);
}

main();
