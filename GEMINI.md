# Gemini Added Memories

This file serves as a comprehensive guide for the Gemini AI, providing essential context about the project, its structure, and operational procedures.

## Project Overview

This is a Laravel-based headless CMS (Content Management System) that utilizes the Filament admin panel for content management. It offers a robust API for external clients and a user-friendly web interface for administration.

*   **Framework:** Laravel 10.x (PHP)
*   **Admin Panel:** Filament v3+
*   **Database:** Configurable (SQLite, MySQL, PostgreSQL)
*   **Authentication:** Token-based API authentication with Laravel Sanctum
*   **Key Features:** API-First Architecture, Intuitive Admin Panel, Content Types (Posts, Categories, Tags), Extendable and Customizable.

## Architecture

### Models
- **Post**: Content with title, slug, content, excerpt, category, tags, publishing status
- **Category**: Hierarchical categories for organizing content
- **Tag**: For content tagging and filtering
- **CustomPostType**: Dynamic post types
- **PostMeta**: Meta information for posts
- **Settings**: Application settings
- **User**: User authentication and authorization
- **Voting**: Voting functionality

### Relationships
- Posts belong to Categories
- Posts have many Tags (many-to-many)
- Categories can be hierarchical (parent-child)

## Building and Running

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & npm
- Database (SQLite, MySQL, PostgreSQL)

### Installation Steps

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/fkadeal/headless.git
    cd headless
    ```

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Install JavaScript dependencies:**
    ```bash
    npm install
    ```

4.  **Set up environment variables:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5.  **Configure your database:**
    Edit the `.env` file to configure your database connection.

6.  **Run database migrations:**
    ```bash
    php artisan migrate
    ```

7.  **Seed the database (Optional, but recommended for development):**
    ```bash
    php artisan db:seed
    ```

8.  **Start the development server:**
    ```bash
    php artisan serve
    ```

    The application will be accessible at `http://localhost:8000`.

### Admin Panel Access
Access the Filament admin panel at `/admin`. You might need to create an admin user first using `php artisan make:filament-user` or through a seeder.

### Running Tests
The project includes PHPUnit for backend testing and potentially Pest for unit tests if configured.

*   **Run PHPUnit tests:**
    ```bash
    php artisan test
    ```

## API Documentation

The CMS provides a comprehensive API for managing content. Detailed API documentation is available in `CMS_API_Postman_Collection.json` and `CMS_FEATURES.md`.

### Example Endpoints:
-   `POST /api/login` - Get API token
-   `POST /api/logout` - Revoke token
-   `GET /api/posts` - List posts
-   `POST /api/posts` - Create post (authentication required)
-   `GET /api/posts/{id}` - Get single post
-   `PUT/PATCH /api/posts/{id}` - Update post (authentication required)
-   `DELETE /api/posts/{id}` - Delete post (authentication required)

Similar endpoints are available for `/api/categories` and `/api/tags`.

## Development Conventions

*   **Coding Style:** Follows Laravel's established coding standards.
*   **Testing:** PHPUnit is used for testing. Refer to the `tests/` directory for existing test examples.
*   **Project Structure:** Standard Laravel directory structure with Filament-specific additions in `app/Filament/`.
*   **Configuration:** Key configurations are located in the `config/` directory.
*   **Database:** Migrations are used for schema management (`database/migrations/`).
*   **Environment:** Uses `.env` for environment-specific configurations.

## Important Files and Directories

*   `app/`: Contains the application's core code, including models, controllers, services, and Filament resources.
*   `config/`: All application configuration files.
*   `database/`: Database migrations, seeders, and factories.
*   `public/`: The web server's document root.
*   `routes/`: Defines all application routes (web, api, console, auth).
*   `resources/`: Frontend assets (CSS, JS) and views.
*   `tests/`: Unit and Feature tests.
*   `.env.example`: Example environment configuration.
*   `composer.json`: PHP project dependencies and scripts.
*   `package.json`: JavaScript project dependencies and scripts.
*   `artisan`: Laravel's command-line interface.

## Contributing

Refer to the `CONTRIBUTING.md` (if it existed, it would be here) or `README.md` for contribution guidelines. Generally, it involves forking the repository, creating a feature branch, and submitting a pull request.

## Support

For support, open an issue on the GitHub repository.

## Other Documentation
- [CMS Features](CMS_FEATURES.md) - Complete API and functionality documentation
- [Authentication Setup](AUTHENTICATION.md) - Authentication architecture details
- [Deployment](DEPLOYMENT.md) - Deployment specific documentation
- [QWEN](QWEN.md) - Additional documentation (content unknown without reading)

---

### Gemini Added Memories
- I have the complete project structure from PROJECT_TREE.md and can now analyze it to understand the project's conventions and key components.
