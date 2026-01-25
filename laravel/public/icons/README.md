# PWA Icons

This directory contains icons for the Progressive Web App (PWA).

## Required Icons

Generate icons in the following sizes:
- 72x72 px (icon-72x72.png)
- 96x96 px (icon-96x96.png)
- 128x128 px (icon-128x128.png)
- 144x144 px (icon-144x144.png)
- 152x152 px (icon-152x152.png)
- 192x192 px (icon-192x192.png)
- 384x384 px (icon-384x384.png)
- 512x512 px (icon-512x512.png)

## How to Generate Icons

1. Create a 512x512 px source image with the FIT AI logo (🍽️ emoji or custom design)
2. Use an online tool like [PWA Asset Generator](https://www.pwabuilder.com/imageGenerator) or [RealFaviconGenerator](https://realfavicongenerator.net/)
3. Or use ImageMagick:

```bash
# Starting from a 512x512 source image
convert source.png -resize 72x72 icon-72x72.png
convert source.png -resize 96x96 icon-96x96.png
convert source.png -resize 128x128 icon-128x128.png
convert source.png -resize 144x144 icon-144x144.png
convert source.png -resize 152x152 icon-152x152.png
convert source.png -resize 192x192 icon-192x192.png
convert source.png -resize 384x384 icon-384x384.png
convert source.png -resize 512x512 icon-512x512.png
```

## Design Guidelines

- Use the emerald green theme color (#10b981)
- Include the 🍽️ emoji or a plate icon
- Keep the design simple and recognizable
- Use white or light background
- Ensure the icon works at all sizes
