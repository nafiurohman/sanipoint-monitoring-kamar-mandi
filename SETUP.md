# SANIPOINT - Setup Instructions

## Quick Start

1. **Install Dependencies**
```bash
npm install
```

2. **Build CSS**
```bash
npm run build
```

3. **Development Mode**
```bash
npm run dev
```

## Available Commands

- `npm run dev` - Watch mode for development (auto-rebuild on changes)
- `npm run build` - Production build (minified with autoprefixer)
- `npm run watch` - Watch mode without minification

## Features

✅ PostCSS with Autoprefixer
✅ TailwindCSS v3
✅ Dark/Light/System theme mode
✅ Cross-platform compatibility (Windows/Mac/Linux)
✅ Production optimization with cssnano

## Theme System

The theme system supports 3 modes:
- **Light Mode** - Force light theme
- **Dark Mode** - Force dark theme  
- **System Theme** - Follow OS preference

Toggle theme using the button in sidebar.

## Browser Support

- Chrome/Edge 60+
- Firefox 60+
- Safari 12+
- Modern browsers (last 2 versions)

## Troubleshooting

If CSS doesn't update:
1. Run `npm run build`
2. Clear browser cache
3. Hard refresh (Ctrl+F5)

If dark mode doesn't work:
1. Check browser console for errors
2. Verify `theme.js` is loaded
3. Clear localStorage and try again