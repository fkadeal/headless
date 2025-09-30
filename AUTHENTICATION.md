# Authentication Setup Documentation

This document describes the authentication architecture implemented in the headless CMS with Filament admin panel.

## Problem Statement

The original issue was: `MethodNotAllowedHttpException: The GET method is not supported for route login. Supported methods: POST`

This occurred because:
1. The application was configured for headless API authentication
2. But also had Filament admin panel which requires web authentication
3. When accessing the admin panel, users were redirected to `/login` instead of `/admin/login`
4. The `/login` route only supported POST requests for API authentication, causing the error

## Solution Implemented

### 1. Filament Panel Configuration

Updated `app/Providers/Filament/AdminPanelProvider.php` to include the `->login()` method:

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->path('admin')
        ->login() // This enables Filament's own login route
        ->colors([
            'primary' => Color::Amber,
        ])
        // ... other configuration
}
```

This creates the `/admin/login` route specifically for the admin panel.

### 2. Authentication Controller

Updated `app/Http/Controllers/Auth/AuthenticatedSessionController.php` to handle both API and web authentication:

- For API requests (`/api/login`): Creates Sanctum tokens and bypasses sessions
- For web requests (`/admin/login`): Uses traditional session-based authentication
- Implements proper rate limiting for both request types
- Handles token revocation on API logout

### 3. Route Architecture

**API Routes** (`routes/api.php`):
- `POST /api/login` - API authentication
- `POST /api/logout` - API logout
- `POST /api/register` - API registration (if needed)
- These routes bypass CSRF middleware

**Web Routes** (`routes/web.php` and `routes/auth.php`):
- Include the admin panel routes like `/admin/login`
- Maintain CSRF protection for web security

### 4. Middleware Configuration

Created custom HTTP kernel and CSRF middleware with proper exclusions:
- API routes bypass CSRF verification
- Web routes maintain CSRF protection
- Login/logout routes are properly configured for each context

## Functionality

### API Authentication
- `POST /api/login`: Accepts JSON credentials, returns Sanctum token
- `POST /api/logout`: Revokes current token with Bearer authorization
- Proper rate limiting
- Session-free authentication

### Admin Panel Authentication
- `GET /admin/login`: Web-based login form
- Session-based authentication
- Integration with Filament's requirements
- Proper CSRF protection

## Testing the Setup

- **API Login**: `curl -X POST http://localhost:8000/api/login -H "Content-Type: application/json" -d '{"email":"test@example.com", "password":"password"}'`
- **API Logout**: `curl -X POST http://localhost:8000/api/logout -H "Authorization: Bearer {token}" -H "Content-Type: application/json"`
- **Admin Panel**: Navigate to `http://localhost:8000/admin` - should redirect to `/admin/login`

## Key Benefits

1. Proper separation of API and web authentication
2. No more MethodNotAllowed errors when accessing admin panel
3. Secure token-based authentication for headless CMS
4. Full Filament admin panel functionality
5. Proper security practices for both authentication methods