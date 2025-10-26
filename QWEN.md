# Mood Tracker Application - QWEN Context File

## Project Overview
This is a Laravel PHP web application called "CereMood" - a mood tracking application that allows users to log their emotions with reasons and suggestions for improvement. The application features a modern UI with Bootstrap, uses Hotwire/Turbo for enhanced user experience, and includes authentication via Google OAuth.

## Key Technologies
- **Backend**: Laravel 12.x (PHP 8.2+)
- **Frontend**: JavaScript, Bootstrap 5, Tailwind CSS, Hotwire/Turbo
- **Build Tools**: Vite, npm
- **Authentication**: Laravel Socialite with Google OAuth
- **Database**: MySQL (default) with Eloquent ORM
- **UI Framework**: Bootstrap 5 with custom CSS

## Application Features
1. **Google OAuth Authentication**: Users authenticate via Google, with an additional verification step requiring company code
2. **Mood Tracking**: Users can log different moods (senyum/happy, sedih/sad, lelah/tired, marah/angry, netral/neutral)
3. **Dynamic Modal Interface**: Mood logging happens through an interactive modal with personalized content
4. **Quote System**: Displays random motivational quotes with caching
5. **Responsive Design**: Mobile-first approach with bottom navigation
6. **Turbo Integration**: Uses Hotwire/Turbo for faster page transitions

## Project Structure
- `app/` - Contains models, controllers, and application logic
- `resources/` - Frontend assets (CSS, JS, views)
- `routes/web.php` - Defines all web routes
- `database/` - Migrations, factories, and seeds
- `public/` - Web-accessible files and assets

## Key Files & Components
- `app/Http/Controllers/MoodController.php` - Handles mood-related operations
- `app/Http/Controllers/GoogleLoginController.php` - Manages Google OAuth flow
- `app/Http/Controllers/VerificationController.php` - Handles user verification after OAuth
- `resources/views/home-page/index.blade.php` - Main page with mood tracking interface
- `resources/js/app.js` - Main JavaScript file with Turbo integration
- `resources/css/app.css` - Main CSS file with custom styles

## Authentication Flow
1. User initiates Google login
2. After Google authentication, user is redirected to verification page
3. User enters additional details and company verification code
4. User is marked as verified and logged in
5. User can access mood tracking features

## Mood Tracking Process
1. User selects a mood from the emoticons on the home page
2. A modal opens with personalized questions based on the selected mood
3. User enters reasons and potential actions
4. Data is submitted via Turbo frame to the server
5. Record is saved to the database and modal closes

## Database Models
- `User` - Stores user information including Google ID, avatar, division, role, gender
- `MoodRecord` - Stores mood entries with mood type, reason, and suggested action
- `MoodQuote` - Stores motivational quotes with cache invalidation on changes

## Building and Running

### Prerequisites
- PHP 8.2+
- Composer
- Node.js and npm
- MySQL or compatible database

### Setup Instructions
1. Install PHP dependencies:
   ```bash
   composer install
   ```
2. Install Node.js dependencies:
   ```bash
   npm install
   ```
3. Create environment file:
   ```bash
   cp .env.example .env
   ```
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Set up database (create database and update .env with credentials)
6. Run database migrations:
   ```bash
   php artisan migrate
   ```
7. Build assets:
   ```bash
   npm run build
   ```
8. Start the development server:
   ```bash
   php artisan serve
   ```

### Development Commands
- **Run development server with hot reload**: `npm run dev` (in one terminal) and `php artisan serve` (in another)
- **Run tests**: `composer test` or `php artisan test`
- **Database seeding**: `php artisan db:seed`
- **Clear caches**: `php artisan cache:clear`

### Environment Configuration
The application requires Google OAuth credentials in the `.env` file:
- `GOOGLE_CLIENT_ID` - Google OAuth client ID
- `GOOGLE_CLIENT_SECRET` - Google OAuth client secret
- `GOOGLE_REDIRECT_URI` - Redirect URI for Google OAuth
- `COMPANY_VERIFICATION_CODE` - Code required for user verification after OAuth

## Important Notes
1. The application uses a company verification system where users must enter a company code after Google authentication
2. The UI is designed with mobile-first approach, featuring a bottom navigation bar
3. Caching is implemented for mood quotes to improve performance
4. The application uses Hotwire/Turbo for enhanced user experience without full page reloads
5. Avatar images are gender-specific, showing different emoticons based on user's gender selection

## Views and Components
- `resources/views/layouts/` - Layout templates (app, internal)
- `resources/views/components/` - Reusable UI components (mood modal, emoticons, etc.)
- `resources/views/home-page/` - Main home page with mood tracking interface
- `resources/views/auth/` - Authentication-related views (verify)

## Custom Styling
The application uses Bootstrap 5 as the base CSS framework with custom styles for:
- Mood input containers
- Bottom navigation bar with animated active state
- Splash screen animations
- Mood modal with personalized content

## API Endpoints
- `/` - Welcome page
- `/home` - Main mood tracking page (requires authentication)
- `/auth/google/redirect` - Initiates Google OAuth
- `/auth/google/callback` - Handles Google OAuth callback
- `/auth/verify` - User verification page
- `/mood/modal` - Loads mood modal content
- `/mood/save` - Saves mood entry
- `/mood/quote` - Returns random motivational quote