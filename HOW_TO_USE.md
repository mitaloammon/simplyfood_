# How to Use SimplyFood

This document explains how to install and run SimplyFood locally on your machine.

## Overview

SimplyFood is a web application for managing restaurants and food businesses. It includes features for:
- customer and address management
- order management
- menu and product catalog
- financial and cash flow management
- WhatsApp Business integration
- delivery logistics and geolocation

## Requirements

Before installing, make sure your machine has:
- Docker and Docker Compose
- PHP 8.3 or higher
- Node.js 20 or higher
- Composer
- A browser such as Chrome, Edge, or Firefox

## Installation Steps

### 1. Clone the repository
```bash
git clone <repository-url>
cd simplyfood
```

### 2. Start the Docker containers
```bash
docker-compose up -d
```

This will start the application, web server, database, and Redis services.

### 3. Configure the backend
```bash
cd backend
copy .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
```

If you are using PowerShell on Windows, use:
```powershell
Copy-Item .env.example .env
```

### 4. Configure the frontend
```bash
cd ../frontend
npm install
npm run build
```

## Run the Application

### Option A: Access through Docker
After the containers are running, open your browser at:
- http://localhost

The API will be available at:
- http://localhost/api

### Option B: Run the frontend in development mode
If you want to work on the frontend while developing:
```bash
cd frontend
npm run dev
```

Then open the local URL shown in the terminal, usually:
- http://localhost:5173

## Troubleshooting

### Docker containers are not starting
Check whether Docker Desktop is running and try:
```bash
docker-compose down
docker-compose up -d
```

### Composer dependencies fail to install
Make sure PHP and Composer are installed correctly and that your system meets the minimum version requirements.

### Frontend does not load
Ensure the frontend dependencies were installed successfully:
```bash
cd frontend
npm install
```

## Notes

- The application uses a Laravel backend and a Vue 3 frontend.
- The local environment is designed to run with Docker, but frontend development can also be done locally.
- If you are developing new features, it is recommended to keep the backend and frontend services running while testing in the browser.
