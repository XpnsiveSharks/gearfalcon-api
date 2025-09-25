@echo off
echo 🔐 Generating Docker Secrets...
echo ================================

REM Check if secrets directory exists
if not exist "secrets" (
    echo 📁 Creating secrets directory...
    mkdir secrets
)

echo 🔑 Generating database password...
powershell -Command "[Convert]::ToBase64String([System.Security.Cryptography.RNGCryptoServiceProvider]::new().GetBytes(32)) | Out-File -FilePath secrets/db_password.txt -Encoding UTF8"

echo 🔑 Generating root password...
powershell -Command "[Convert]::ToBase64String([System.Security.Cryptography.RNGCryptoServiceProvider]::new().GetBytes(32)) | Out-File -FilePath secrets/db_root_password.txt -Encoding UTF8"

echo 📝 Setting database name...
echo gearfalcon_db_dev > secrets/db_database.txt

echo 🔒 Setting file permissions...
icacls secrets\* /inheritance:r /grant:r "%username%:F" >nul 2>&1
icacls secrets\* /remove "Users" "Everyone" "Authenticated Users" >nul 2>&1

echo ✅ Secrets generated successfully!
echo 📍 Location: ./secrets/
echo 🔒 Files are secured with owner-only permissions
echo.
echo 🚀 You can now start the development environment:
echo    .\start-dev.bat
echo.
pause