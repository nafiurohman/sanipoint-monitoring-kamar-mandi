@echo off
echo Installing SANIPOINT dependencies...

echo.
echo [1/3] Installing PHP dependencies (TCPDF)...
composer install --no-dev --optimize-autoloader

echo.
echo [2/3] Installing Node.js dependencies...
npm install

echo.
echo [3/3] Building CSS...
npm run build

echo.
echo ========================================
echo SANIPOINT Setup Complete!
echo ========================================
echo.
echo Available commands:
echo   npm run dev      - Development mode (watch)
echo   npm run build    - Production build
echo   composer install - Install PHP dependencies
echo.
echo Features installed:
echo   ✓ PostCSS with Autoprefixer
echo   ✓ TailwindCSS v3
echo   ✓ Dark/Light/System theme
echo   ✓ TCPDF for PDF reports
echo   ✓ Chart.js for analytics
echo.
pause