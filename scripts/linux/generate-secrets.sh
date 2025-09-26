#!/bin/bash

echo "🔐 Generating Docker Secrets..."
echo "==============================="

# Check if secrets directory exists
if [ ! -d "secrets" ]; then
    echo "📁 Creating secrets directory..."
    mkdir -p secrets
fi

echo "🔑 Generating database password..."
openssl rand -base64 32 > secrets/db_password.txt

echo "🔑 Generating root password..."
openssl rand -base64 32 > secrets/db_root_password.txt

echo "📝 Setting database name..."
echo "gearfalcon_db_dev" > secrets/db_database.txt

echo "🔒 Setting file permissions..."
chmod 600 secrets/*

echo "✅ Secrets generated successfully!"
echo "📍 Location: ./secrets/"
echo "🔒 Files are secured with owner-only permissions"
echo ""
echo "🚀 You can now start the development environment:"
echo "    ./start-dev.sh"
echo ""
read -p "Press Enter to continue..."