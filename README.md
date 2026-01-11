# MyBookstore

A Symfony-based web application for managing a bookstore, including books, authors, publishers, categories, orders, and user management.

## Features

- **Book Management**: Add, edit, and manage books with details like title, author, publisher, category, and cover images.
- **Author, Publisher, and Category Management**: CRUD operations for authors, publishers, and categories.
- **User Management**: User registration, authentication, and profiles.
- **Order Management**: Handle customer orders and order items.
- **Admin Dashboard**: Powered by EasyAdmin for easy administration.
- **File Uploads**: Support for uploading book cover images using VichUploaderBundle.
- **Responsive UI**: Built with Twig templates and Bootstrap (via base.html.twig).

## Requirements

- PHP 8.2 or higher
- Composer
- Symfony CLI (recommended)
- Docker and Docker Compose (for containerized setup)

## Installation

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd ProjectPHP
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Environment Configuration**:
   - Copy `.env` to `.env.local` and configure your database and other settings.
   - For development, you can use `.env.dev`.

4. **Database Setup**:
   - Create the database: `php bin/console doctrine:database:create`
   - Run migrations: `php bin/console doctrine:migrations:migrate`
   - Load fixtures (optional): `php bin/console doctrine:fixtures:load`

5. **Assets**:
   - Install assets: `php bin/console assets:install`
   - If using AssetMapper, run: `php bin/console importmap:install`

## Running the Application

### Using Symfony CLI
```bash
symfony server:start
```
Access at `http://localhost:8000`

### Using Docker
If Docker Compose is set up:
```bash
docker-compose up -d
```

## Usage

- **Public Site**: Browse books, add to cart, place orders.
- **Admin Panel**: Access at `/admin` (requires admin role).
- **User Registration/Login**: Available on the site.

## Testing

Run tests with PHPUnit:
```bash
php bin/phpunit
```

## Contributing

1. Fork the repository.
2. Create a feature branch.
3. Make your changes.
4. Run tests.
5. Submit a pull request.

## License

This project is proprietary.

## Technologies Used

- **Symfony 6.4**: Framework
- **Doctrine ORM**: Database abstraction
- **EasyAdmin**: Admin interface
- **Twig**: Templating
- **VichUploaderBundle**: File uploads
- **Bootstrap**: CSS framework (integrated via templates)