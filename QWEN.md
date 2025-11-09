# QWEN.md - CereMood Mood Tracker Application

## Project Overview

CereMood is a web-based mood tracking application built with the Laravel PHP framework. The application allows users to track their daily moods, emotions, and feelings over time, providing a personal dashboard to monitor emotional well-being. The application uses Turbo Laravel for modern web interactions without full page refreshes.

### Key Features
- User Authentication: Login via Google OAuth integration
- Mood Tracking: Record moods with reasons and action suggestions
- Mood Categories: Supports five mood types - senyum (happy), sedih (sad), lelah (tired), marah (angry), and netral (neutral)
- Personal Dashboard: View mood history with pagination
- Mood Quotes: Random inspirational quotes displayed to users
- Calendar View: Visual representation of mood history over time
- User Verification: Verification system for new users via Google login
- Profile Management: User profile with avatar and personal details
- Admin Panel: Administrative interface for monitoring user mood data

### Tech Stack
- **Backend**: Laravel PHP framework (v12)
- **Frontend**: Tailwind CSS, Bootstrap 5, Turbo Laravel
- **Database**: MySQL (with Eloquent ORM)
- **Authentication**: Google OAuth via Laravel Socialite
- **Build Tools**: Vite, NPM
- **Caching**: Laravel caching system

## Project Structure

### Main Directories
- `app/` - Main application logic (Controllers, Models, Services, Helpers)
- `routes/` - Application routing configuration
- `resources/` - Frontend assets, views, and components
- `config/` - Laravel configuration files
- `database/` - Migrations, seeds, and factories
- `public/` - Web root directory
- `storage/` - Compiled views, file uploads, and logs

### Key Application Components

#### Models
- `User.php` - User authentication and profile management
- `MoodRecord.php` - Mood tracking records with user relationship
- `MoodQuote.php` - Inspirational quotes management with caching

#### Controllers
- `HomeController.php` - Main dashboard and home page
- `MoodController.php` - Mood recording functionality
- `GoogleLoginController.php` - Google OAuth authentication
- `VerificationController.php` - User verification process
- `ProfileController.php` - User profile management
- `CalendarController.php` - Calendar view for mood history
- `Admin/DashboardController.php` - Administrative dashboard and mood monitoring
- `Mood/MoodRecordController.php` - Mood record management with Turbo Stream integration

#### Views
- `layouts/` - Main layout templates
- `home-page/` - Main dashboard and mood tracking interface
- `auth/` - Authentication and verification views
- `profile/` - User profile management
- `calendar/` - Calendar visualization of mood data
- `admin/` - Administrative interface

## Building and Running

### Prerequisites
- PHP 8.2+
- Composer
- Node.js and npm
- MySQL or compatible database

### Installation Steps
1. Install PHP dependencies:
   ```bash
   composer install
   ```
2. Install Node.js dependencies:
   ```bash
   npm install
   ```
3. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Configure database settings in `.env` file
6. Run database migrations:
   ```bash
   php artisan migrate
   ```
7. Set up Google OAuth credentials in `.env`:
   ```
   GOOGLE_CLIENT_ID=your_google_client_id
   GOOGLE_CLIENT_SECRET=your_google_client_secret
   GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
   ```
8. Build frontend assets:
   ```bash
   npm run build
   ```
   or for development with hot-reloading:
   ```bash
   npm run dev
   ```

### Running the Application
- Development Mode:
  ```bash
  # Terminal 1: Run Laravel development server
  php artisan serve

  # Terminal 2: Run Vite dev server
  npm run dev
  ```

- Production Build:
  ```bash
  npm run build
  ```

## Development Conventions

### Code Structure
- Controllers are organized in subdirectories based on feature (Mood, Admin, Auth)
- Models use Eloquent ORM with proper relationships and scopes
- Services contain business logic separate from controllers
- Views use Blade templates with proper component structure

### Turbo Laravel Integration
- Uses Turbo Streams for dynamic content updates without page refresh
- Custom `TurboStreamHelper` class for building stream responses
- Pagination and content updates handled via Turbo Streams

### Authentication Flow
- Google OAuth integration for user authentication
- Verification system for new users
- Session-based authentication with proper middleware

### Security Considerations
- Input validation in services and controllers
- Proper password hashing with Laravel's built-in functionality
- CSRF protection through Laravel's built-in middleware
- Session management with secure configuration

## Important Files and Configuration

### Key Configuration Files
- `composer.json` - PHP dependencies and scripts
- `package.json` - Node.js dependencies and build scripts
- `vite.config.js` - Frontend build configuration
- `routes/web.php` - Main application routing
- `.env.example` - Environment variable configuration

### Database Structure
- Users table with Google OAuth integration
- Mood records with user relationships
- Mood quotes for inspirational messages
- Verification system tables

## Common Tasks

### Adding a New Mood Type
1. Update the mood validation array in `MoodService::validateMoodData()`
2. Add the new mood to the mood labels in `MoodRecord` and `HomeController`
3. Update the UI components to include the new mood option

### Modifying the Admin Panel
- Admin authentication is handled by session with credentials defined in `.env`
- Admin routes are protected by the `admin.auth` middleware
- Admin dashboard controller is in `app/Http/Controllers/Admin/DashboardController.php`

### Extending User Profile Functionality
- User model contains fields for name, email, avatar, division, role, gender, and verification status
- ProfileController handles profile updates
- Profile views are in `resources/views/profile/`

This project is well-structured with separation of concerns between models, services, controllers, and views, making it easy to maintain and extend.