# CereMood - Mood Tracker Application

## Project Overview

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

### Technologies Used

- **Backend**: Laravel 12.x (PHP)
- **Frontend**: HTML, CSS, JavaScript with Bootstrap 5 and Tailwind CSS
- **Turbo Framework**: Hotwire Turbo for modern UX without full page reloads
- **Database**: MySQL (default configuration)
- **Authentication**: Laravel Socialite with Google OAuth
- **Build Tools**: Vite for asset building
- **Styling**: Tailwind CSS for utility-first CSS framework

## Project Structure

```
mood-tracker/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php
│   │   │   ├── MoodController.php
│   │   │   ├── GoogleLoginController.php
│   │   │   ├── VerificationController.php
│   │   │   ├── CalendarController.php
│   │   ├── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── MoodRecord.php
│   │   └── MoodQuote.php
│   └── Providers/
├── config/
├── database/
├── public/
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   └── app.js
│   └── views/
├── routes/
│   └── web.php
├── storage/
├── tests/
├── composer.json
├── package.json
└── vite.config.js
```

## Key Components

### Models

- **User**: Extends Authenticatable with relationships to MoodRecord
- **MoodRecord**: Stores mood entries with mood type, reason, and action suggestion
- **MoodQuote**: Manages inspirational quotes with cache invalidation on CRUD operations

### Controllers

- **MoodController**: Handles mood recording modal, quote retrieval, and mood saving with Turbo Streams
- **HomeController**: Manages the main dashboard and pagination
- **GoogleLoginController**: Handles Google OAuth authentication
- **VerificationController**: Manages user verification process
- **CalendarController**: Provides calendar view functionality

### Views & Templates

The application uses Blade templates with a component-based structure in `resources/views/components/`. The UI leverages Turbo Frames and Streams for dynamic content updates without full page refreshes.

## Development Setup

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

### Running the Application

#### Development Mode
To run the application in development mode with auto-reload for both backend and frontend:

```bash
# Terminal 1: Run Laravel development server
php artisan serve

# Terminal 2: Run Vite dev server
npm run dev

# Or alternatively, use the composer script that runs both:
composer run dev
```

#### Production Build
To build for production:

```bash
npm run build
```

#### Testing
Run the application tests:

```bash
composer run test
# or
php artisan test
```

## Development Conventions

### Code Style
- Follow PSR-12 coding standards for PHP
- Use Laravel conventions for naming and structure
- Use Pint for PHP code formatting: `php artisan pint`

### Frontend
- CSS styling uses Tailwind CSS utility classes
- JavaScript interactions leverage Turbo for modern UX
- Bootstrap 5 for UI components

### Database
- Use Laravel migrations for schema changes
- Follow Laravel naming conventions for tables and columns
- Use Eloquent ORM for database interactions

### Authentication
- Google OAuth is the primary authentication method
- User verification is required after first login
- Password authentication is available as a fallback

## Environment Configuration

Key environment variables:

- `APP_NAME`: Application name (default: Laravel)
- `APP_ENV`: Environment (local, staging, production)
- `APP_KEY`: Laravel application key (generated with `artisan key:generate`)
- `DB_DATABASE`: Database name (default: CereMood)
- `DB_USERNAME`: Database username
- `DB_PASSWORD`: Database password
- `GOOGLE_CLIENT_ID`: Google OAuth client ID
- `GOOGLE_CLIENT_SECRET`: Google OAuth client secret
- `GOOGLE_REDIRECT_URI`: Google OAuth redirect URI

## Key Routes

- `/` - Welcome page
- `/auth/google/redirect` - Google login redirect
- `/auth/google/callback` - Google login callback
- `/home` - Main dashboard (requires authentication)
- `/calendar` - Calendar view of moods (requires authentication)
- `/auth/verify` - User verification page
- `/mood/modal` - Mood selection modal
- `/mood/save` - Save mood entry (POST)
- `/mood/quote` - Get random inspirational quote

## Special Features

### Turbo Integration
The application leverages Turbo Laravel for modern web interactions:
- Mood records are saved and displayed without full page refreshes
- Pagination works via Turbo Streams
- Modal interactions use Turbo Frames

### Caching
- Mood quotes are cached per user with version-based invalidation
- Quote cache is invalidated when quotes are added, updated, or deleted

### Multi-Gender Avatars
The application serves different avatars based on user gender selection for various mood states.