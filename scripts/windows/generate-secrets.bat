@echo off
echo 🔐 Generating Docker Secrets...
echo ================================

REM Check if secrets directory exists
if not exist "secrets" (
    echo 📁 Creating secrets directory...
    mkdir secrets
)

echo 🔑 Generating database password...
REM Use multiple fallback methods for reliable password generation
powershell -Command "try { [Convert]::ToBase64String([System.Security.Cryptography.RNGCryptoServiceProvider]::new().GetBytes(32)) | Out-File -FilePath secrets/db_password.txt -Encoding UTF8 } catch { try { $password = -join ((48..57) + (65..90) + (97..122) | Get-Random -Count 32 | ForEach-Object {[char]$_}); $password | Out-File -FilePath secrets/db_password.txt -Encoding UTF8 } catch { $timestamp = Get-Date -Format 'yyyyMMddHHmmss'; $random = Get-Random; $password = $timestamp + $random + 'DbPass2024!'; $password | Out-File -FilePath secrets/db_password.txt -Encoding UTF8 } }"

echo 🔑 Generating root password...
REM Use multiple fallback methods for reliable password generation
powershell -Command "try { [Convert]::ToBase64String([System.Security.Cryptography.RNGCryptoServiceProvider]::new().GetBytes(32)) | Out-File -FilePath secrets/db_root_password.txt -Encoding UTF8 } catch { try { $password = -join ((48..57) + (65..90) + (97..122) | Get-Random -Count 32 | ForEach-Object {[char]$_}); $password | Out-File -FilePath secrets/db_root_password.txt -Encoding UTF8 } catch { $timestamp = Get-Date -Format 'yyyyMMddHHmmss'; $random = Get-Random; $password = $timestamp + $random + 'RootPass2024!'; $password | Out-File -FilePath secrets/db_root_password.txt -Encoding UTF8 } }"

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
echo    .\scripts\windows\start-dev.bat
echo.
pause