# Changelog

All notable changes to the headless CMS project will be documented in this file.

## [Unreleased]

## [1.0.0] - 2025-09-30

### Added
- Complete CMS functionality with Posts, Categories, and Tags
- Database migrations for all CMS entities
- Eloquent models with proper relationships
- API controllers with full CRUD operations
- API routes with public and protected endpoints
- Filament admin panel for content management
- Authentication system with Sanctum tokens
- Comprehensive documentation
- Created by Fkadeal Matiwos

### Features
- Posts: Title, slug, content, excerpt, categories, tags, publishing status
- Categories: Hierarchical structure with parent-child relationships
- Tags: For post categorization and filtering
- API: Token-based authentication with full CRUD
- Admin Panel: Intuitive interface for content management
- Relationships: Posts belong to categories, many-to-many with tags

### Security
- CSRF protection for web forms
- Token-based authentication for API
- Rate limiting for authentication attempts
- Proper validation and sanitization

[Unreleased]: https://github.com/fkadeal/headless/compare/v1.0.0...HEAD