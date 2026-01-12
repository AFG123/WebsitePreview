# Wedding-Card-Portfolio

A dynamic wedding card / wedding website preview platform built using **PHP, JavaScript, and MySQL**, featuring both a **public-facing website** and an **admin dashboard** for managing content.

This project demonstrates a real-world PHP application structure with backend logic, API usage, and admin-side management.

---

## 📌 Features

### 🌐 User Side
- Home page showcasing wedding card designs
- Product listing page
- Individual product detail view
- Responsive UI for desktop and mobile
- Dynamic content loaded from backend

### 🛠 Admin Dashboard
- Secure admin login/logout
- Manage categories
- Add, update, and delete products
- Change website settings
- Centralized reusable functions

### 🔗 API
- REST-style API endpoint for fetching product data
- Used to dynamically load products on the frontend

---

## 🗂 Project Structure

WebsitePreview/
│
├── admin/ # Admin dashboard files
│ ├── index.php
│ ├── login.php
│ ├── logout.php
│ ├── products.php
│ ├── categories.php
│ ├── settings.php
│ └── functions.php
│
├── api/ # Backend API endpoints
│ └── products.php
│
├── Cards/ # Images and card assets
│
├── uploads/ # User-uploaded files (ignored in git)
│
├── index.php # Main landing page
├── all-products.php # Product listing page
├── product.php # Single product page
├── script.js # Frontend JavaScript
├── config.php # Database configuration (ignored)
└── .gitignore


---
## ⚙️ Technologies Used

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP (Core PHP)
- **Database:** MySQL
- **Server:** Apache (XAMPP)
- **Version Control:** Git & GitHub

---

## 🚀 How to Run Locally

1. Install **XAMPP**
2. Clone this repository into: C:/xampp/htdocs/
3. Create a MySQL database
4. Configure database connection in `config.php`
5. Start **Apache** and **MySQL**
6. Open browser and visit:
http://localhost/WebsitePreview


---

## 🔒 Security Notes

- `config.php` and sensitive files are excluded using `.gitignore`
- Uploaded files are not tracked in version control
- Admin routes are separated from public routes

---

## 📈 Purpose of This Project

- Freelance portfolio project
- Demonstrates backend logic with PHP
- Shows CRUD operations and admin management
- Practical example of full-stack web development using PHP

---

## 👤 Author

**Aryan Damai**  
GitHub: https://github.com/AFG123
