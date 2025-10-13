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
- `GET /api/posts` - List posts with advanced filtering and pagination
  - Query params: `search`, `category`, `published`, `per_page`
  - Advanced filtering: `filters[field][operator]=value`
- `GET /api/posts/{post}` - Get single post with relationships
- `GET /api/categories` - List categories with advanced filtering and pagination
  - Query params: `search`, `parent_id`, `active`, `per_page`
  - Advanced filtering: `filters[field][operator]=value`
- `GET /api/categories/{category}` - Get single category with relationships
- `GET /api/tags` - List tags with advanced filtering and pagination
  - Query params: `search`, `per_page`
  - Advanced filtering: `filters[field][operator]=value`
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
- Form fields: Title, slug, excerpt, content (TinyEditor), category, author, published status
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

## Advanced Filtering

The API supports advanced filtering through a flexible query syntax:

### Basic Structure
Use a filters object in the query string:
```
GET /posts?filters[field][operator]=value
```

### Supported Operators
- `eq` - Equal to (e.g., `filters[status][eq]=published`)
- `ne` - Not equal (e.g., `filters[status][ne]=draft`)
- `gt` - Greater than (e.g., `filters[views][gt]=1000`)
- `gte` - Greater than or equal (e.g., `filters[views][gte]=1000`)
- `lt` - Less than (e.g., `filters[views][lt]=500`)
- `lte` - Less than or equal (e.g., `filters[views][lte]=500`)
- `contains` - Text contains (e.g., `filters[title][contains]=laravel`)
- `in` - Value in array (e.g., `filters[status][in]=published,draft`)
- `nin` - Value not in array (e.g., `filters[status][nin]=archived,trashed`)

### Examples

#### Single filter
```
GET /posts?filters[status][eq]=published
```

#### Multiple filters (AND by default)
```
GET /posts?filters[status][eq]=published&filters[views][gt]=1000
```

#### OR conditions
```
GET /posts?filters[or][0][status][eq]=draft&filters[or][1][status][eq]=archived
```

#### Nested relation filtering
By category:
```
GET /posts?filters[category][slug][eq]=tech
```

By tags:
```
GET /posts?filters[tags][slug][in]=laravel,php
```

#### Search
```
GET /posts?filters[search]=laravel cms
```

#### Sorting
```
GET /posts?sort=-views,title
```

#### Pagination
```
GET /posts?page=2&per_page=20
```

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

# Get posts with advanced filtering (no authentication required)
curl "http://localhost:8000/api/posts?filters[status][eq]=published&filters[views][gt]=1000"

# Get posts with relation filtering
curl "http://localhost:8000/api/posts?filters[category][slug][eq]=tech"

# Get posts with search and sorting
curl "http://localhost:8000/api/posts?filters[search]=laravel&sort=-created_at"
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