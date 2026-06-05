# DeliveryMS - Professional Delivery Management System

## 📋 Executive Summary

DeliveryMS is a comprehensive, enterprise-grade delivery management system built with PHP and a custom MVC framework. Designed specifically for e-commerce platforms and logistics providers, it streamlines the entire fulfillment pipeline from order intake through final delivery, with real-time courier tracking, inventory automation, and secure payment processing.

### Business Value & Solutions

- **Operational Efficiency**: Automated order routing and inventory management reduce manual overhead and order processing time
- **Real-time Visibility**: Live courier tracking via OpenStreetMap integration provides customers and management with shipment transparency
- **Revenue Protection**: Integrated PayPal payments with role-based security ensure secure transactions and reduce fraud risk
- **Business Intelligence**: Built-in reporting and analytics tools support data-driven decision-making for inventory and logistics optimization
- **Scalability**: Role-based access control (root, admin, user, courier) enables multi-team collaboration in growing organizations

---

## 🚀 Key Features

* **Order Management** - Create, track, and update orders with status management and full order history
* **Inventory Management** - Real-time stock monitoring with automatic updates based on order fulfillment
* **Courier & Logistics** - Assign deliveries, track courier location with OpenStreetMap, optimize routes
* **User Roles & Permissions** - Four-tier role system (root, admin, user, courier) with granular access control
* **Payment Integration** - Secure PayPal integration for online transactions with transaction tracking
* **Email Notifications** - Automated notifications via Mailtrap for orders, deliveries, and system alerts
* **Reports & Analytics** - Comprehensive reporting on orders, inventory, courier performance, and revenue
* **Data Export** - Generate PDF and Excel reports for analysis and archival

---

## 🧰 Requirements

* [XAMPP](https://www.apachefriends.org/index.html)
* PHP 7.x+
* MySQL 5.7+
* Web browser (Chrome, Firefox, etc.)

---

## 🛠️ Installation Guide

### Step 1: Setup XAMPP

1. Download and install XAMPP from the official website.
2. During installation, ensure Apache and MySQL are selected.

### Step 2: Project Setup

1. Download or clone the DeliveryMS project into your `htdocs` directory:

   ```
   C:\xampp\htdocs\DeliveryMS
   ```

### Step 3: Start XAMPP

1. Open the XAMPP Control Panel.
2. Start the **Apache** and **MySQL** services.

### Step 4: Run Installer

1. In your browser, navigate to:

   ```
   http://localhost/DeliveryMS
   ```
2. Follow the installation wizard.

### Step 5: Database Configuration

Input the following MySQL settings:

* **Host:** `localhost`
* **Username:** `root`
* **Password:** *(leave blank if none)*
* **Database Name:** *(choose one, e.g., deliveryms\_db)*

### Step 6: Create Root Profile

Provide administrator details:

* Name
* Email
* Password

### Step 7: PayPal Setup

Enter your business PayPal email address to enable secure payments.

### Step 8: Mailtrap Setup

Enter the following Mailtrap details from your account:

* Host
* Port
* Username
* Password

### Step 9: Finish Installation

After filling out all the fields, click to finish. You will be redirected to the application's home page.

---

## 🔐 Login & Usage

Use the root account you created during installation to log in and start managing deliveries, inventory, and more.

---

## 🏗️ Architecture Overview

DeliveryMS follows a **Model-View-Controller (MVC)** architecture with clear separation of concerns:

### Project Structure

```
DeliveryMS/
├── Core/                          # Framework foundation & utilities
│   ├── Controller.php            # Base controller class
│   ├── Model.php                 # Base model with ORM functionality
│   ├── Router.php                # URL routing and request dispatch
│   ├── Security.php              # Authentication, authorization, validation
│   ├── View.php                  # Template rendering
│   ├── Services/                 # Business services
│   │   ├── ExportService.php     # PDF/Excel export functionality
│   │   └── MailService.php       # Email handling
│   └── Exceptions/               # Custom exceptions
├── App/
│   ├── Controllers/              # Feature controllers (Auth, Order, Product, etc.)
│   ├── Models/                   # Domain models (User, Order, Product, etc.)
│   └── Views/                    # HTML templates organized by feature
├── config/                        # Configuration & helper functions
├── web/                          # Frontend assets (CSS, JS, images)
├── tests/                        # PHPUnit test suite
├── index.php                     # Application entry point
└── phpunit.xml                   # Test configuration
```

### Design Patterns Used

- **MVC Pattern**: Separation of business logic, data access, and presentation
- **ORM Pattern**: Database abstraction through Model base class
- **Service Layer**: Business logic isolation (MailService, ExportService)
- **Factory Pattern**: Controller instantiation via Router
- **Singleton Pattern**: Database and configuration management

---

## 📊 System Modules Overview

1. **Order Management** (`OrderController`, `Order` model)
   * Create and manage customer orders
   * Track delivery status with real-time updates
   * Generate order reports and analytics

2. **Inventory Management** (`ProductController`, `Product` model)
   * Monitor stock levels in real-time
   * Auto-update inventory based on order fulfillment
   * Prevent overselling with stock validation

3. **Courier Logistics** (`CourierController`, `CourierLocation` model)
   * Assign deliveries to couriers
   * Real-time location tracking via OpenStreetMap
   * Route optimization for efficient delivery

4. **User Management** (`UserController`, `User` model)
   * Four-tier role system: root (superadmin), admin (manager), user (staff), courier
   * Fine-grained permission control per role
   * Account management and activity logging

5. **Notification System** (`NotificationController`, `Notification` model)
   * Automated order status notifications
   * Delivery alerts to customers
   * System notifications for staff

6. **Payment Processing**
   * Secure PayPal integration for online payments
   * Order status tied to payment completion
   * Transaction tracking and reconciliation

7. **Reports & Analytics**
   * Order fulfillment reports
   * Courier performance metrics
   * Revenue and sales analytics
   * Exportable reports (PDF, Excel)

---

## 🎯 Core Business Objectives

- Streamline the logistics chain from order placement to delivery confirmation
- Optimize internal processes through automation and real-time visibility
- Ensure secure, compliant transactions with integrated payment processing
- Provide business intelligence tools for informed operational decisions
- Support multi-user collaboration with role-based access control

---

## 📌 Technologies Used

| Layer | Technologies |
|-------|--------------|
| **Backend** | PHP 7.x+ (Custom MVC Framework) |
| **Frontend** | HTML5, CSS3, JavaScript, jQuery |
| **Database** | MySQL 5.7+ |
| **Email** | Mailtrap + PHPMailer |
| **File Export** | TCPDF (PDF), SimpleXLSXGen (Excel) |
| **Payments** | PayPal API |
| **Location/Maps** | OpenStreetMap API |
| **Testing** | PHPUnit 9.x |

---

## 🧪 Testing

The project uses **PHPUnit** for automated testing. Tests are located in the `tests/` directory and mirror the project structure.

### Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Unit/Controllers/OrderControllerTest.php

# Run with coverage report
vendor/bin/phpunit --coverage-html coverage/
```

### Test Structure

```
tests/
├── bootstrap.php                 # Test configuration
├── Core/                         # Core framework tests
│   └── SecurityTest.php         # Security & validation tests
├── Unit/
│   ├── Controllers/             # Controller tests
│   ├── Models/                  # Model tests
│   └── Services/                # Service tests
```

### Writing Tests

When contributing, include tests for:
- **Controllers**: Request handling, authorization checks, response validation
- **Models**: Data persistence, validation rules, relationships
- **Services**: Business logic, error handling, integration points

See existing tests in `tests/Unit/` for examples and patterns.

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. **Code Style**: Follow PSR-12 PHP coding standards
2. **Testing**: Add tests for all new features (PHPUnit)
3. **Documentation**: Update README for new features or significant changes
4. **Commits**: Use clear, descriptive commit messages

### Code Review Checklist

- [ ] Tests written and passing
- [ ] README updated if needed
- [ ] Code follows PSR-12 standards
- [ ] No security vulnerabilities introduced
- [ ] Performance implications reviewed

---

## 📬 Support

For questions, bug reports, or feature requests, please open an issue in the repository.

---

## 📄 License

This project is available for educational and commercial use.

---

> Developed as a diploma project at "Dr. Petar Beron" High School of Mathematics, Varna.
