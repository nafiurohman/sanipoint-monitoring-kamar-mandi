@echo off
echo Installing SANIPOINT dependencies...
npm install

echo.
echo Building CSS...
npm run build

echo.
echo Setup complete!
echo Run 'npm run dev' for development mode
echo.
pause