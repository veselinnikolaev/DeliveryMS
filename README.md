# DeliveryMS

DeliveryMS is a PHP-based delivery management system tailored for e-commerce platforms. It allows the tracking and administration of orders, inventory, couriers, and user roles with real-time logistics updates and secure payment integrations.

## 🚀 Key Features

* Order management (creation, tracking, updating status)
* Warehouse and inventory management
* Courier and delivery logistics (OpenStreetMap integration)
* User roles (root, admin, user, courier)
* PayPal payment integration
* Email service integration via Mailtrap
* Reports and analytics for decision-making

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

## 📊 System Modules Overview

1. **Order Management**

   * Create and manage customer orders
   * Track and update statuses
   * Generate order reports

2. **Inventory Management**

   * Monitor stock levels
   * Perform inventory operations
   * Auto-update inventory based on order activity

3. **Courier Logistics**

   * Assign and track couriers
   * Use OpenStreetMap for real-time tracking and optimized routing

4. **User Roles**

   * `root`, `admin`, `user`, `courier`
   * Each role has different access rights and responsibilities

5. **Payment Integration**

   * Secure payments via PayPal

6. **Email Integration**

   * Uses Mailtrap for testing email functionality

7. **Reports & Analytics**

   * Generate insights for operations and business analysis

---

## 🎯 Project Objective

To develop an integrated delivery management system for online stores that streamlines the logistics chain, optimizes internal processes, and ensures secure transactions.

---

## 📌 Technologies Used

* **Backend:** PHP (Custom MVC Framework)
* **Frontend:** HTML, CSS, JavaScript, jQuery
* **Database:** MySQL
* **Email:** Mailtrap with PHPMailer
* **PDF/Excel Export:** TCPDF, SimpleXLSXGen
* **Payments:** PayPal API
* **Location Services:** OpenStreetMap API

---

## 📬 Contact & Support

For help, issues, or feature requests, please contact the project maintainer.

---

> Developed as a diploma project at "Dr. Petar Beron" High School of Mathematics, Varna.
