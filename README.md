# Translation API

A Laravel-based REST API for managing translations with complete CRUD operations, Swagger documentation, and testing utilities.

## Features

- **Full CRUD Operations**
  - Create, Read, Update, Delete translations
  - Filterable listings with pagination
- **Swagger Documentation**
  - Interactive API documentation
  - Automatic endpoint documentation
- **Testing Support**
  - Database factories for fake translations
  - API test cases
- **Advanced Features**
  - Bulk export functionality
  - Search/filter capabilities

## API Documentation

Interactive Swagger documentation is available at:
`http://localhost:8000/api/documentation`

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/eli02922/translations-api
   cd translation-api
Install dependencies:

bash
composer install
Configure environment:

bash
cp .env.example .env
php artisan key:generate
Set up database:

bash
php artisan migrate
Generate Swagger docs:

bash
php artisan l5-swagger:generate
