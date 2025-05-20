# Perfume Store POS System

A comprehensive Point of Sale (POS) system designed specifically for perfume stores. This system helps manage inventory, process sales, track expenses, and generate reports.

## Features

- **Admin Dashboard**
  - Product management (CRUD operations)
  - Category management
  - Expense tracking
  - Sales reports and analytics
  - User management

- **Cashier Interface**
  - Quick product search and filtering
  - Shopping cart management
  - Cash payment processing
  - Automatic receipt generation

- **Reports and Analytics**
  - Sales trends
  - Top-selling products
  - Expense summaries
  - Profit analysis

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Installation

1. Clone the repository to your web server directory:
   ```bash
   git clone https://github.com/yourusername/perfume-pos.git
   ```

2. Create a MySQL database:
   ```sql
   CREATE DATABASE perfume_pos;
   ```

3. Import the database schema:
   ```bash
   mysql -u your_username -p perfume_pos < database/schema.sql
   ```

4. Configure the database connection:
   - Open `config/database.php`
   - Update the database credentials:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'your_username');
     define('DB_PASSWORD', 'your_password');
     define('DB_NAME', 'perfume_pos');
     ```

5. Set up the web server:
   - For Apache, ensure mod_rewrite is enabled
   - Point the document root to the project directory
   - Ensure the web server has write permissions for the `assets/images` directory

6. Initialize the database:
   - Visit `http://your-domain/database/init.php` to create the initial admin user
   - Default admin credentials:
     - Username: admin
     - Password: admin123
   - **Important**: Change the default password after first login

## Directory Structure

```
perfume-pos/
├── admin/              # Admin dashboard files
├── api/               # API endpoints
├── assets/            # Static assets
│   ├── css/          # Stylesheets
│   ├── js/           # JavaScript files
│   └── images/       # Product images
├── config/           # Configuration files
├── database/         # Database files
├── includes/         # Common PHP files
└── README.md         # This file
```

## Usage

### Admin Dashboard

1. Log in using admin credentials
2. Navigate through the sidebar menu to access different features
3. Manage products, categories, and expenses
4. View reports and analytics

### Cashier Interface

1. Log in using cashier credentials
2. Use the search bar to find products
3. Filter products by category
4. Add items to cart
5. Process payments and print receipts

## Security

- All user inputs are sanitized
- Passwords are hashed using PHP's password_hash()
- SQL injection prevention using prepared statements
- XSS prevention using htmlspecialchars()
- CSRF protection implemented
- Session management with proper security measures

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please open an issue in the GitHub repository or contact the development team. 