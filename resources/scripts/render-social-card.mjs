import fs from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';

const [, , inputPath, outputPath] = process.argv;

if (!inputPath || !outputPath) {
    throw new Error('Uso: node render-social-card.mjs <input.json> <output.jpg>');
}

const escapeXml = (value = '') => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

const wrapText = (value, maxCharsPerLine, maxLines = 2) => {
    const words = String(value || '').split(/\s+/).filter(Boolean);
    const lines = [];
    let current = '';

    for (const word of words) {
        const candidate = current ? `${current} ${word}` : word;

        if (candidate.length <= maxCharsPerLine) {
            current = candidate;
            continue;
        }

        if (current) {
            lines.push(current);
        }

        current = word;

        if (lines.length === maxLines - 1) {
            break;
        }
    }

    if (current && lines.length < maxLines) {
        lines.push(current);
    }

    if (lines.length === maxLines && words.join(' ').length > lines.join(' ').length) {
        lines[maxLines - 1] = `${lines[maxLines - 1].slice(0, Math.max(0, maxCharsPerLine - 1)).trimEnd()}…`;
    }

    return lines;
};

const renderLines = ({ lines, x, y, size, fill, weight = 600, lineHeight = 1.3 }) => lines
    .map((line, index) => `<text x="${x}" y="${y + (index * size * lineHeight)}" fill="${fill}" font-size="${size}" font-weight="${weight}" font-family="'Segoe UI', Arial, sans-serif">${escapeXml(line)}</text>`)
    .join('');

const input = JSON.parse(await fs.readFile(inputPath, 'utf8'));
const { dimensions, station, meta, prices, logo, appName } = input;
const { width, height } = dimensions;
const isSquare = width === height;
const padding = isSquare ? 64 : 56;
const panelRadius = 34;
const cardGap = 18;
const columns = isSquare ? 2 : 3;
const rows = Math.ceil(prices.length / columns);
const cardWidth = ((width - (padding * 2)) - (cardGap * (columns - 1))) / columns;
const cardHeight = isSquare ? 176 : 158;
const gridTop = isSquare ? 468 : 318;
const brandLines = wrapText(station.brand, isSquare ? 18 : 22, 2);
const localityLine = [station.municipality, station.province].filter(Boolean).join(' · ');
const addressLine = [station.address, station.postal_code].filter(Boolean).join(' · ');
const subtitleLines = wrapText(localityLine, isSquare ? 30 : 40, 2);
const addressLines = wrapText(addressLine, isSquare ? 36 : 52, 2);
const footerLabel = `Actualizado ${meta.updated_at || 'sin fecha'} · ${appName}`;

const background = `
<svg width="${width}" height="${height}" viewBox="0 0 ${width} ${height}" fill="none" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="bgGradient" x1="0" y1="0" x2="${width}" y2="${height}" gradientUnits="userSpaceOnUse">
            <stop stop-color="#07111F" />
            <stop offset="0.58" stop-color="#0F223D" />
            <stop offset="1" stop-color="#03212F" />
        </linearGradient>
        <linearGradient id="accentGradient" x1="0" y1="0" x2="1" y2="1">
            <stop stop-color="#4FD1FF" />
            <stop offset="1" stop-color="#8BFFB0" />
        </linearGradient>
        <filter id="panelShadow" x="0" y="0" width="${width}" height="${height}" filterUnits="userSpaceOnUse">
            <feDropShadow dx="0" dy="18" stdDeviation="28" flood-color="#020617" flood-opacity="0.3" />
        </filter>
    </defs>
    <rect width="${width}" height="${height}" rx="36" fill="url(#bgGradient)" />
    <circle cx="${Math.round(width * 0.15)}" cy="${Math.round(height * 0.12)}" r="${Math.round(width * 0.18)}" fill="#4FD1FF" opacity="0.10" />
    <circle cx="${Math.round(width * 0.86)}" cy="${Math.round(height * 0.16)}" r="${Math.round(width * 0.16)}" fill="#8BFFB0" opacity="0.08" />
    <rect x="${padding}" y="${padding}" width="${width - (padding * 2)}" height="${height - (padding * 2)}" rx="${panelRadius}" fill="rgba(255,255,255,0.06)" stroke="rgba(255,255,255,0.12)" filter="url(#panelShadow)" />
    <rect x="${padding + 28}" y="${padding + 28}" width="${isSquare ? 168 : 148}" height="${isSquare ? 168 : 148}" rx="28" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.10)" />
    <rect x="${padding + (isSquare ? 224 : 208)}" y="${padding + 34}" width="${isSquare ? 360 : 300}" height="46" rx="23" fill="rgba(79,209,255,0.12)" />
    <text x="${padding + (isSquare ? 248 : 232)}" y="${padding + 65}" fill="#4FD1FF" font-size="22" font-weight="700" font-family="'Segoe UI', Arial, sans-serif">${escapeXml(meta.social)} · ${escapeXml(meta.og_type)} · ${escapeXml(meta.size_label)}</text>
    ${renderLines({ lines: brandLines, x: padding + (isSquare ? 224 : 208), y: padding + 136, size: isSquare ? 58 : 52, fill: '#F8FAFC', weight: 800, lineHeight: 1.08 })}
    ${renderLines({ lines: subtitleLines, x: padding + (isSquare ? 224 : 208), y: padding + 244, size: isSquare ? 28 : 25, fill: '#E2E8F0', weight: 700, lineHeight: 1.2 })}
    ${renderLines({ lines: addressLines, x: padding + 28, y: isSquare ? 388 : 284, size: isSquare ? 24 : 22, fill: '#B8C3D9', weight: 500, lineHeight: 1.3 })}
    <text x="${padding + 28}" y="${height - padding - 30}" fill="#93C5FD" font-size="22" font-weight="600" font-family="'Segoe UI', Arial, sans-serif">${escapeXml(footerLabel)}</text>
</svg>`;

const cardsSvg = `
<svg width="${width}" height="${height}" viewBox="0 0 ${width} ${height}" fill="none" xmlns="http://www.w3.org/2000/svg">
    ${prices.map((price, index) => {
        const column = index % columns;
        const row = Math.floor(index / columns);
        const x = padding + (column * (cardWidth + cardGap));
        const y = gridTop + (row * (cardHeight + cardGap));
        const cardValue = price.formatted || 'Sin dato';
        const valueLines = wrapText(cardValue, isSquare ? 14 : 16, 2);

        return `
            <g>
                <rect x="${x}" y="${y}" width="${cardWidth}" height="${cardHeight}" rx="24" fill="rgba(255,255,255,0.07)" stroke="rgba(255,255,255,0.12)" />
                <text x="${x + 26}" y="${y + 44}" fill="#93C5FD" font-size="20" font-weight="700" font-family="'Segoe UI', Arial, sans-serif">${escapeXml(price.label)}</text>
                ${renderLines({ lines: valueLines, x: x + 26, y: y + 98, size: isSquare ? 32 : 30, fill: '#F8FAFC', weight: 800, lineHeight: 1.1 })}
            </g>`;
    }).join('')}
</svg>`;

const baseImage = sharp(Buffer.from(background))
    .composite([
        {
            input: Buffer.from(cardsSvg),
            top: 0,
            left: 0,
        },
    ]);

const logoBox = {
    left: padding + 28,
    top: padding + 28,
    width: isSquare ? 168 : 148,
    height: isSquare ? 168 : 148,
};

if (logo?.has_logo && logo?.path) {
    baseImage.composite([
        {
            input: await sharp(logo.path)
                .resize({
                    width: logoBox.width - 24,
                    height: logoBox.height - 24,
                    fit: 'contain',
                    background: { r: 0, g: 0, b: 0, alpha: 0 },
                })
                .toBuffer(),
            left: logoBox.left + 12,
            top: logoBox.top + 12,
        },
    ]);
} else {
    const fallbackSvg = `
    <svg width="${logoBox.width}" height="${logoBox.height}" viewBox="0 0 ${logoBox.width} ${logoBox.height}" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="${logoBox.width}" height="${logoBox.height}" rx="28" fill="rgba(79,209,255,0.16)" />
        <rect x="8" y="8" width="${logoBox.width - 16}" height="${logoBox.height - 16}" rx="22" stroke="rgba(255,255,255,0.14)" />
        <text x="50%" y="54%" fill="#F8FAFC" font-size="${isSquare ? 54 : 48}" font-weight="800" text-anchor="middle" font-family="'Segoe UI', Arial, sans-serif">${escapeXml(logo.initials || 'GS')}</text>
    </svg>`;

    baseImage.composite([
        {
            input: Buffer.from(fallbackSvg),
            left: logoBox.left,
            top: logoBox.top,
        },
    ]);
}

await fs.mkdir(path.dirname(outputPath), { recursive: true });
await baseImage.jpeg({ quality: 88, mozjpeg: true }).toFile(outputPath);
