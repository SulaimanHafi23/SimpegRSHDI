import fs from 'fs';
import path from 'path';
import puppeteer from 'puppeteer';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const sourceFolder = path.join(__dirname, 'docs/SequenceDiagram');
const outputFolder = path.join(sourceFolder, 'PNG_Transparent');

const EXCLUDED_DIRS = new Set(['PNG_Export', 'PNG_Transparent', 'PNG', 'HTML_TEMP', '.tmp-mmd']);

const localMermaidBundlePath = path.join(__dirname, 'node_modules', 'mermaid', 'dist', 'mermaid.min.js');
const localMermaidBundle = fs.existsSync(localMermaidBundlePath)
    ? fs.readFileSync(localMermaidBundlePath, 'utf8')
    : null;

// Create output folder
if (!fs.existsSync(outputFolder)) {
    fs.mkdirSync(outputFolder, { recursive: true });
}

function collectMermaidFiles(dir, root = dir) {
    const entries = fs.readdirSync(dir, { withFileTypes: true });
    const files = [];

    for (const entry of entries) {
        if (entry.name.startsWith('.')) {
            continue;
        }

        const absPath = path.join(dir, entry.name);

        if (entry.isDirectory()) {
            if (EXCLUDED_DIRS.has(entry.name)) {
                continue;
            }
            files.push(...collectMermaidFiles(absPath, root));
            continue;
        }

        if (entry.isFile() && entry.name.endsWith('.mermaid')) {
            files.push({
                absPath,
                relPath: path.relative(root, absPath)
            });
        }
    }

    return files;
}

const files = collectMermaidFiles(sourceFolder);

console.log(`Found ${files.length} diagrams to export\n`);

let success = 0;
let failed = 0;

function sanitizeMermaidSource(raw) {
    let source = String(raw || '').replace(/^\uFEFF/, '');

    // Normal frontmatter block: --- ... ---
    source = source.replace(/^---\s*\r?\n[\s\S]*?\r?\n---\s*\r?\n?/, '');

    // Fallback for malformed frontmatter (e.g. "---id: ...")
    const startsLikeFrontmatter = source.trimStart().startsWith('---');
    const hasSequence = /(^|\n)\s*sequenceDiagram\b/m.test(source);
    if (startsLikeFrontmatter && hasSequence) {
        const idx = source.search(/(^|\n)\s*sequenceDiagram\b/m);
        if (idx >= 0) {
            source = source.slice(idx);
        }
    }

    return source.trim();
}

function createHtml(mermaidSource) {
    return `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: transparent;
        }

        body {
            display: inline-block;
        }

        #stage {
            display: inline-block;
            padding: 24px;
            background: transparent;
        }

        #diagram {
            display: inline-block;
        }

        #diagram svg {
            display: block;
            overflow: visible;
            max-width: none !important;
            height: auto;
        }

        text, tspan, foreignObject div {
            font-family: Arial, Helvetica, sans-serif !important;
        }
    </style>
    ${localMermaidBundle
        ? `<script>${localMermaidBundle.replace(/<\/script>/g, '<\\/script>')}</script>`
        : '<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>'}
</head>
<body>
    <div id="stage">
        <div id="diagram" class="mermaid"></div>
    </div>
    <script>
        window.__MERMAID_DONE__ = false;
        window.__MERMAID_ERROR__ = null;

        const source = ${JSON.stringify(mermaidSource)};

        (async () => {
            try {
                mermaid.initialize({
                    startOnLoad: false,
                    securityLevel: 'loose'
                });

                await document.fonts.ready;

                const target = document.getElementById('diagram');

                // Pass 1: render awal
                const first = await mermaid.render('m1_' + Date.now(), source);
                target.innerHTML = first.svg;
                await new Promise((r) => requestAnimationFrame(() => requestAnimationFrame(r)));

                // Pass 2: render ulang untuk menstabilkan alignment text
                const second = await mermaid.render('m2_' + Date.now(), source);
                target.innerHTML = second.svg;

                await document.fonts.ready;
                await new Promise((r) => setTimeout(r, 250));
            } catch (err) {
                window.__MERMAID_ERROR__ = String(err && err.message ? err.message : err);
            } finally {
                window.__MERMAID_DONE__ = true;
            }
        })();
    </script>
</body>
</html>`;
}

async function main() {
    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    for (const file of files) {
        const relNoExt = file.relPath.replace(/\.mermaid$/i, '');
        const outputPath = path.join(outputFolder, `${relNoExt}.png`);
        const outputDir = path.dirname(outputPath);

        if (!fs.existsSync(outputDir)) {
            fs.mkdirSync(outputDir, { recursive: true });
        }

        process.stdout.write(`Converting ${file.relPath}... `);

        let exported = false;
        let lastError = null;

        for (let attempt = 1; attempt <= 2; attempt++) {
            const page = await browser.newPage();
            await page.setViewport({ width: 5000, height: 5000, deviceScaleFactor: 2 });

            try {
                const rawContent = fs.readFileSync(file.absPath, 'utf8');
                const content = sanitizeMermaidSource(rawContent);
                const html = createHtml(content);

                await page.setContent(html, { waitUntil: 'domcontentloaded', timeout: 60000 });
                await page.waitForFunction(() => window.__MERMAID_DONE__ === true, { timeout: 60000 });

                const renderError = await page.evaluate(() => window.__MERMAID_ERROR__);
                if (renderError) {
                    throw new Error(renderError);
                }

                const clip = await page.evaluate(() => {
                    const stage = document.getElementById('stage');
                    const svg = document.querySelector('#diagram svg');

                    if (!stage || !svg) {
                        return null;
                    }

                    const stageRect = stage.getBoundingClientRect();
                    const svgRect = svg.getBoundingClientRect();

                    const pad = 8;
                    const x = Math.max(0, Math.floor(Math.min(stageRect.left, svgRect.left) - pad));
                    const y = Math.max(0, Math.floor(Math.min(stageRect.top, svgRect.top) - pad));
                    const right = Math.max(stageRect.right, svgRect.right) + pad;
                    const bottom = Math.max(stageRect.bottom, svgRect.bottom) + pad;

                    return {
                        x,
                        y,
                        width: Math.ceil(right - x),
                        height: Math.ceil(bottom - y)
                    };
                });

                if (!clip || clip.width <= 0 || clip.height <= 0) {
                    throw new Error('Invalid clip area after rendering');
                }

                await page.screenshot({
                    path: outputPath,
                    type: 'png',
                    omitBackground: true,
                    clip
                });

                exported = true;
                break;
            } catch (err) {
                lastError = err;
            } finally {
                await page.close();
            }
        }

        if (exported) {
            console.log('OK');
            success++;
        } else {
            console.log(`FAILED (${lastError ? lastError.message : 'Unknown error'})`);
            failed++;
        }
    }

    await browser.close();

    console.log(`\nCompleted: ${success} OK, ${failed} Failed`);
    console.log(`Output folder: ${outputFolder}`);
}

main();
