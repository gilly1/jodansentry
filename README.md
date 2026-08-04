# JodanPay - M-Pesa Bulk Payment System

A modern Laravel application for managing bulk M-Pesa B2C payments with role-based access control, approval workflows, and comprehensive audit logging.

## Features

- **Bulk Payments**: Upload Excel/CSV files, validate entries, approve and schedule payment execution
- **Approval Workflow**: Maker-approver separation with optional self-approval
- **Role-Based Access**: Create custom roles with granular permissions
- **Dashboard**: Overview of payment activity with M-Pesa balance query
- **Transaction Status**: Query M-Pesa transaction status by receipt number
- **Audit Logging**: Complete activity trail with IP tracking
- **Queue Processing**: Background payment dispatch with retry logic

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+
- SQLite (development) / MySQL (production)

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed
npm run build
php artisan serve --port=8084
```

## Default Login

- **Email**: admin@JodanPay.test
- **Password**: password

## Queue Processing

```bash
php artisan queue:work --queue=payments
php artisan schedule:work
```
