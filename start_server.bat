@echo off
echo ========================================
echo  CONA STORE E-COMMERCE STORE
echo  Installation Complete!
echo ========================================
echo.
echo Your e-commerce platform has been successfully created!
echo.
echo NEXT STEPS:
echo -----------
echo 1. Create MySQL database: superkicks_db
echo 2. Run setup: http://localhost:8000/setup.php
echo 3. Configure API credentials in config/config.php
echo 4. Delete setup.php after installation
echo.
echo FEATURES INCLUDED:
echo -----------------
echo [✓] Modern Cona Store-inspired design
echo [✓] Email, Phone, Google OAuth authentication  
echo [✓] Shopping cart and wishlist
echo [✓] Product collections with filters
echo [✓] Card, WhatsApp, COD payment methods
echo [✓] Admin dashboard and management
echo [✓] Order tracking system
echo [✓] Responsive mobile design
echo.
echo DEFAULT CREDENTIALS:
echo -------------------
echo Admin Login: http://localhost:8000/admin/login.php
echo Username: admin
echo Password: admin123
echo.
echo ========================================
echo  Ready to launch your store?
echo  Press any key to start the server...
echo ========================================
pause > nul

echo.
echo Starting PHP server on http://localhost:8000
echo Keep this window open while working
echo Press Ctrl+C to stop
echo.

cd /d "%~dp0"
php -S localhost:8000
