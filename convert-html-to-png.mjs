import fs from 'fs';
import path from 'path';
import puppeteer from 'puppeteer';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const htmlTemplateFolder = path.join(__dirname, 'docs/SequenceDiagram/HTML_TEMP');
const outputFolder = path.join(__dirname, 'docs/SequenceDiagram/PNG_Export');

async function convertHtmlToPng() {
    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const files = fs.readdirSync(htmlTemplateFolder)
        .filter(f => f.endsWith('.html'));

    console.log(`Starting conversion of ${files.length} HTML files to PNG\n`);

    let success = 0;
    let failed = 0;

    for (const file of files) {
        try {
            const htmlPath = path.join(htmlTemplateFolder, file);
            const filename = file.replace('.html', '');
            const outputPath = path.join(outputFolder, `${filename}.png`);

            process.stdout.write(`Converting ${file}... `);

            const page = await browser.newPage();
            await page.goto(`file://${htmlPath}`, { waitUntil: 'networkidle2' });

            // Wait for Mermaid to render
            await page.waitForSelector('.mermaid svg', { timeout: 5000 }).catch(() => {});

            // Get the SVG dimensions and set viewport
            const dimensions = await page.evaluate(() => {
                const svg = document.querySelector('.mermaid svg');
                if (svg) {
                    return {
                        width: svg.clientWidth + 40,
                        height: svg.clientHeight + 40
                    };
                }
                return { width: 1024, height: 768 };
            });

            await page.goto(`file://${htmlPath}`, { waitUntil: 'networkidle0' });

            await page.screenshot({
                path: outputPath,
                type: 'png',
                omitBackground: true,
                fullPage: true
            });

            await page.close();

            console.log('OK');
            success++;

        } catch (err) {
            console.log(`FAILED: ${err.message}`);
            failed++;
        }
    }

    await browser.close();

    console.log(`\nCompleted: ${success} OK, ${failed} Failed`);
    console.log(`Output folder: ${outputFolder}`);
}

convertHtmlToPng().catch(err => {
    console.error('Fatal error:', err);
    process.exit(1);
});
