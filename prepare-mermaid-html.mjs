import fs from 'fs';
import path from 'path';
import { spawn } from 'child_process';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const sourceFolder = path.join(__dirname, 'docs/SequenceDiagram');
const outputFolder = path.join(sourceFolder, 'PNG_Export');
const htmlTemplateFolder = path.join(sourceFolder, 'HTML_TEMP');

if (!fs.existsSync(outputFolder)) {
    fs.mkdirSync(outputFolder, { recursive: true });
}

if (!fs.existsSync(htmlTemplateFolder)) {
    fs.mkdirSync(htmlTemplateFolder, { recursive: true });
}

console.log('Creating HTML files for Mermaid rendering...\n');

const files = fs.readdirSync(sourceFolder)
    .filter(f => f.endsWith('.mermaid'));

// Create HTML files that will be rendered
files.forEach(filename => {
    try {
        const filepath = path.join(sourceFolder, filename);
        const content = fs.readFileSync(filepath, 'utf8');
        const baseName = filename.replace('.mermaid', '');

        const html = `<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <script src='https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js'></script>
    <style>
        body { margin: 0; padding: 0; background: transparent; }
        .mermaid { display: flex; justify-content: center; align-items: center; }
        svg { max-width: 100%; height: auto; }
    </style>
</head>
<body>
    <div class='mermaid'>
${content}
    </div>
    <script>
        mermaid.initialize({ startOnLoad: true, theme: 'default' });
        mermaid.mermaid.contentLoaded();
    </script>
</body>
</html>`;

        const htmlPath = path.join(htmlTemplateFolder, `${baseName}.html`);
        fs.writeFileSync(htmlPath, html);

    } catch (err) {
        console.log(`Error creating HTML for ${filename}: ${err.message}`);
    }
});

console.log(`Created ${files.length} HTML files\n`);
console.log('For PNG conversion, you can use one of these methods:\n');
console.log('1. Browser-based: Open each HTML file in a browser and use "Save as PNG"');
console.log('2. Puppeteer: npm install puppeteer, then use screenshot function');
console.log('3. Playwright: npm install @playwright/browser, then use browser.newPage()');
console.log('4. Web conversion: https://kritzl.github.io/mermaid-to-image/\n');
console.log(`HTML files created in: ${htmlTemplateFolder}`);
console.log(`Output PNG folder: ${outputFolder}`);
