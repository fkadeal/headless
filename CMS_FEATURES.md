# CMS Features Documentation

This document outlines the CMS features implemented in the headless CMS application with Filament admin panel.

## Database Schema

### Posts Table
- `id`: Primary key
- `title`: Post title (required, max 255 chars)
- `slug`: URL-friendly identifier (required, unique, max 255 chars)
- `excerpt`: Short summary of the post (optional)
- `content`: Full post content (required, long text)
- `category_id`: Foreign key to categories table (nullable)
- `created_by`: Foreign key to users table (required, constrained)
- `is_published`: Boolean indicating if post is published (default: false)
- `published_at`: Timestamp for when post was published (nullable)
- `featured_image`: Path to featured image (optional)
- `meta_data`: JSON field for additional metadata (optional)
- `timestamps`: created_at and updated_at

### Categories Table
- `id`: Primary key
- `name`: Category name (required, max 255 chars)
- `slug`: URL-friendly identifier (required, unique, max 255 chars)
- `description`: Category description (optional)
- `parent_id`: Self-referencing foreign key for nested categories (nullable)
- `created_by`: Foreign key to users table (required, constrained)
- `is_active`: Boolean indicating if category is active (default: true)
- `timestamps`: created_at and updated_at

### Tags Table
- `id`: Primary key
- `name`: Tag name (required, max 255 chars)
- `slug`: URL-friendly identifier (required, unique, max 255 chars)
- `description`: Tag description (optional)
- `created_by`: Foreign key to users table (required, constrained)
- `timestamps`: created_at and updated_at

### Post-Tag Pivot Table
- `id`: Primary key
- `post_id`: Foreign key to posts table (constrained, cascade delete)
- `tag_id`: Foreign key to tags table (constrained, cascade delete)
- `timestamps`: created_at and updated_at

## API Endpoints

### Public Endpoints (No Authentication Required)
- `GET /api/posts` - List posts with filtering and pagination
  - Query params: `search`, `category`, `published`, `per_page`
- `GET /api/posts/{post}` - Get single post with relationships
- `GET /api/categories` - List categories with filtering and pagination
  - Query params: `search`, `parent_id`, `active`, `per_page`
- `GET /api/categories/{category}` - Get single category with relationships
- `GET /api/tags` - List tags with filtering and pagination
  - Query params: `search`, `per_page`
- `GET /api/tags/{tag}` - Get single tag with relationships

### Protected Endpoints (Authentication Required)
- `POST /api/posts` - Create new post
- `PUT/PATCH /api/posts/{post}` - Update existing post
- `DELETE /api/posts/{post}` - Delete post
- `POST /api/categories` - Create new category
- `PUT/PATCH /api/categories/{category}` - Update existing category
- `DELETE /api/categories/{category}` - Delete category
- `POST /api/tags` - Create new tag
- `PUT/PATCH /api/tags/{tag}` - Update existing tag
- `DELETE /api/tags/{tag}` - Delete tag

### Authentication Endpoints
- `POST /api/login` - Login and return API token
- `POST /api/register` - Register new user
- `POST /api/logout` - Logout and revoke token
- `GET /api/user` - Get authenticated user info

## Model Relationships

### Post Model
- `category()`: BelongsTo relationship to Category
- `author()`: BelongsTo relationship to User (via created_by)
- `tags()`: BelongsToMany relationship to Tag (via post_tag pivot)

### Category Model
- `posts()`: HasMany relationship to Post
- `parent()`: BelongsTo relationship to Category (self-referencing)
- `children()`: HasMany relationship to Category (self-referencing)
- `creator()`: BelongsTo relationship to User (via created_by)

### Tag Model
- `posts()`: BelongsToMany relationship to Post (via post_tag pivot)
- `creator()`: BelongsTo relationship to User (via created_by)

## Filament Admin Panel

The CMS includes a complete Filament admin panel with the following resources:

### Category Resource
- Create, read, update, delete operations
- Form fields: Name, slug, description, parent category, created by, is active
- Table columns: Name, slug, parent category, created by, active status, created at
- Navigation icon: Rectangle stack

### Post Resource
- Create, read, update, delete operations
- Form fields: Title, slug, excerpt, content (Rich Editor), category, author, published status
- Table columns: Title, slug, category, author, published status, created at
- Navigation icon: Document text

### Tag Resource
- Create, read, update, delete operations
- Form fields: Name, slug, description, created by
- Table columns: Name, slug, created by, created at
- Navigation icon: Tag

## Authentication System

The application uses Laravel Sanctum for token-based authentication:
- API users authenticate via `/api/login` and receive a Bearer token
- Protected API routes require the Authorization header with Bearer token
- The admin panel uses session-based authentication
- Rate limiting is implemented for login attempts

## Usage Examples

### API Usage
```bash
# Get authentication token
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com", "password":"password"}'

# Create a category (requires token)
curl -X POST http://localhost:8000/api/categories \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Technology","slug":"technology","description":"Posts about technology"}'

# Create a post (requires token)
curl -X POST http://localhost:8000/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"My Post","slug":"my-post","content":"Post content","category_id":1,"is_published":true}'

# Get posts (no authentication required)
curl http://localhost:8000/api/posts
```

### Admin Panel
- Access the admin panel at `/admin`
- Log in with your credentials
- Manage posts, categories, and tags through the intuitive interface
- All changes are immediately reflected in the API

## Seeding Data

To seed sample data for testing:
```bash
php artisan db:seed
```

Or create specific seeders for posts, categories, and tags as needed.