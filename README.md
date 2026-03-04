# Headless CMS

A Laravel-based headless CMS with Filament admin panel. This project provides both API authentication for external clients and a web-based admin interface for content management.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-orange?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-v3+-slateblue?style=for-the-badge&logo=laravel)](https://filamentphp.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

## Features

- **API-First Architecture**: Full REST API for managing content
- **Admin Panel**: Intuitive Filament-based content management
- **Content Types**: Posts, Categories, Tags, and Custom Post Types with relationships
- **Authentication**: Token-based API authentication with Sanctum
- **Flexible**: Easy to extend and customize for your needs

## Quick Start

### Prerequisites

- PHP 8.2+
- Composer
- Database (SQLite, MySQL, PostgreSQL)

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/fkadeal/headless.git
   cd headless
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Set up environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure your database in `.env` file

5. Run migrations:
   ```bash
   php artisan migrate
   ```

6. Start the development server:
   ```bash
   php artisan serve
   ```

## API Documentation

The CMS provides a comprehensive API for managing content:

### Authentication

- `POST /api/login` - Get API token
- `POST /api/logout` - Revoke token

### Content Management

- `GET /api/posts` - List posts with advanced filtering and pagination
- `POST /api/posts` - Create post (auth required)
- `GET /api/posts/{id}` - Get single post
- `PUT/PATCH /api/posts/{id}` - Update post (auth required)
- `DELETE /api/posts/{id}` - Delete post (auth required)

Similar endpoints are available for categories and tags.

### Custom Post Types

- `GET /api/post-types` - List custom post types with advanced filtering and pagination
  - Support query params: `search`, `category`, `enabled`, `per_page`
- `GET /api/post-types/{slug_or_id}` - Get single post type details

## Admin Panel

Access the admin panel at `/admin` to manage your content through an intuitive interface.

## Architecture

### Models

- **Post**: Content with title, slug, content, excerpt, category, tags, publishing status
- **Category**: Hierarchical categories for organizing content
- **Tag**: For content tagging and filtering
- **CustomPostType**: Dynamic post types with category associations

### Relationships

- Posts belong to Categories
- Posts have many Tags (many-to-many)
- Categories can be hierarchical (parent-child)
- CustomPostTypes have many Categories (many-to-many)

## Documentation

- [CMS Features](CMS_FEATURES.md) - Complete API and functionality documentation
- [Authentication Setup](AUTHENTICATION.md) - Authentication architecture details

## Contributing

Fork the repository, create a feature branch, and submit a pull request.

## Author

**Fkadeal Matiwos** - [GitHub Profile](https://github.com/fkadeal)

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For support, please open an issue on the [GitHub repository](https://github.com/fkadeal/headless).