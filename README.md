# CereMood - Mood Tracker Application

## About This Project

CereMood is a web-based mood tracking application built with Laravel PHP framework. The application allows users to track their daily moods, emotions, and feelings over time, providing a personal dashboard to monitor emotional well-being. The application uses Turbo Laravel for modern web interactions without full page refreshes.

### Key Features

- **User Authentication**: Login via Google OAuth integration
- **Mood Tracking**: Record moods with reasons and action suggestions
- **Mood Categories**: Supports five mood types - senyum (happy), sedih (sad), lelah (tired), marah (angry), and netral (neutral)
- **Personal Dashboard**: View mood history with pagination
- **Mood Quotes**: Random inspirational quotes displayed to users
- **Calendar View**: Visual representation of mood history over time
- **User Verification**: Verification system for new users via Google login
- **Profile Management**: User profile with avatar and personal details

## Installation and Setup

### Prerequisites

- PHP 8.2+
- Composer
- Node.js and npm
- MySQL or compatible database

### Installation Steps

1. Clone the repository
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install Node.js dependencies:
   ```bash
   npm install
   ```
4. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```
5. Generate application key:
   ```bash
   php artisan key:generate
   ```
6. Configure database settings in `.env` file
7. Run database migrations:
   ```bash
   php artisan migrate
   ```
8. Set up Google OAuth credentials in `.env`:
   ```
   GOOGLE_CLIENT_ID=your_google_client_id
   GOOGLE_CLIENT_SECRET=your_google_client_secret
   GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
   ```
9. Build frontend assets:
   ```bash
   npm run build
   ```
   or for development with hot-reloading:
   ```bash
   npm run dev
   ```

## Running the Application

### Development Mode (Testing)
**Catatan Penting**: Untuk menjalankan aplikasi dalam mode development, jalankan kedua perintah berikut di terminal terpisah:

```bash
# Terminal 1: Menjalankan Laravel development server
php artisan serve

# Terminal 2: Menjalankan Vite dev server untuk hot-reload
npm run dev
```

**Penting**: 
- `php artisan serve` - Menjalankan server Laravel di `http://localhost:8000`
- `npm run dev` - Menjalankan Vite dev server untuk hot-reload assets (CSS/JS)
- Kedua perintah harus berjalan bersamaan untuk development yang optimal

Atau gunakan script otomatis:
```bash
./dev-start.bat  # For Windows
```

### Production Build
To build for production:

```bash
npm run build
```

Or use the build script:
```bash
./build.bat    # For Windows
```

## Deployment

### Automated Deployment
For production deployment, use the deploy script:
```bash
./deploy.sh    # For Linux/Mac
```
**Note**: Update the paths and service names in `deploy.sh` according to your production environment.

### Manual Deployment Steps

1. Upload the code to your server
2. Run `composer install --no-dev --optimize-autoloader`
3. Run `npm install --production`
4. Run `npm run build`
5. Run `php artisan key:generate --force`
6. Run `php artisan migrate --force`
7. Run `php artisan config:cache`
8. Run `php artisan route:cache`
9. Run `php artisan view:cache`

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
