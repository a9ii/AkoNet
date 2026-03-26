# 🌐 AkoNet Web Monitor

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

**AkoNet Web Monitor** is a professional, full-stack application designed to track the real-time status, latency, and packet loss of internet service providers and corporate infrastructure. Built with a robust PHP backend and a stunning modern frontend, it offers unparalleled visibility into network health.

## ✨ Key Features

- **📊 Real-time Dashboard** — Live tracking of provider status, ping response times, and packet loss.
- **📈 Advanced Analytics** — Detailed provider pages featuring historical charts for downtime and performance metrics.
- **🎨 Premium UI/UX** — A sleek, modern dark glassmorphism theme that is fully responsive across desktop, tablet, and mobile devices.
- **🛡️ Secure Admin Control** — Comprehensive admin panel to manage providers, configure settings, and toggle visibility of sensitive data (like IP/domains).
- **🖼️ Intelligent Logo Management** — Secure upload system for provider logos with seamless fallback to generated initials.
- **⚙️ Automated Engine** — Reliable background cron jobs ensure status checks and data logging happen automatically.

## 🛠️ Technology Stack

- **Backend:** PHP (PDO, cURL, shell_exec)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3 (Custom styling), Bootstrap 5, JavaScript
- **Environment:** Designed for cPanel Shared Hosting or any standard LAMP stack.

## 🚀 Quick Start

For detailed installation instructions, please refer to the [Installation Guide (INSTALL.md)](INSTALL.md).

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled
- Cron job support

### Basic Setup
1. Clone or download this repository.
2. Upload the files to your web server (e.g., `public_html`).
3. Create a MySQL database and import the `database.sql` file.
4. Update `config.php` with your database credentials.
5. Set up a cron job to run `cron/monitor.php` every 5 minutes.
6. Login to the admin panel at `/admin/login.php` (Default: `admin` / `admin123`). **Change this immediately!**

## 📂 Project Structure

```text
AkoNet/
├── admin/          # Admin panel & provider management
├── api/            # Backend API endpoints for frontend data
├── assets/         # CSS, JS, and image assets (including logos)
├── cron/           # Automated monitoring scripts
├── includes/       # Shared PHP components
├── config.php      # Database and system configuration
├── database.sql    # Database schema
├── index.php       # Main public dashboard
└── provider.php    # Detailed provider view
```

## 🔒 Security

- Password hashing using `bcrypt`
- Prepared PDO statements for SQL injection prevention
- Protected admin routes with session management
- Secure file upload validation for images

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.
