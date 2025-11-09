# Zector - Blog Website

A modern, feature-rich blog platform built with vanilla PHP, HTML, CSS, and JavaScript.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2-blue.svg)
![MySQL](https://img.shields.io/badge/MySQL-8.0-blue.svg)

## ✨ Features

- 🔐 **User Authentication** - Secure registration and login system
- 📝 **Post Management** - Create, edit, and delete blog posts
- 🖼️ **Image Upload** - Support for post images and profile photos
- 👍 **Reactions System** - Like and dislike posts
- 👤 **User Profiles** - Customizable profile photos and account settings
- 🎨 **Modern UI** - Dark theme with responsive design
- 📧 **Contact Form** - Get in touch with website admin
- 🔒 **Security** - Password hashing, session management, SQL injection protection

## 🚀 Quick Start

### Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP (for local development)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/blogssite.git
   cd blogssite
   ```

2. **Import the database**
   - Open phpMyAdmin
   - Create a new database named `blog_website`
   - Import `setup_complete.sql`



3. **Set folder permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/profiles/
   ```

4. **Access the application**
   - Open `(https://zector.42web.io/Zector/frontend/index.html)`
   - Register a new account and start blogging!

## 📁 Project Structure

```
blogssite/
├── backend/              # PHP backend files
│   ├── config.php       # Database configuration (reads from .env)
│   ├── .env            # Environment variables (not committed)
│   ├── .env.example    # Environment template
│   ├── *_handler.php   # Request handlers
│   └── get_*.php       # Data retrieval
├── frontend/            # Frontend files
│   ├── *.html          # HTML pages
│   ├── style.css       # Styles
│   └── script.js       # JavaScript
├── uploads/             # User uploads
│   └── profiles/       # Profile photos
├── database.sql         # Basic database schema
├── setup_complete.sql   # Complete database with indexes
└── README.md           # This file
```

## 🗄️ Database Schema

### Tables

- **users** - User accounts and authentication
  - `id`, `username`, `email`, `password`, `profile_photo`, `created_at`

- **posts** - Blog posts
  - `id`, `user_id`, `title`, `content`, `image`, `created_at`, `updated_at`

- **post_reactions** - Likes and dislikes
  - `id`, `post_id`, `user_id`, `reaction`, `created_at`

## 🎨 Features in Detail

### User System
- Secure password hashing with PHP's `password_hash()`
- Session-based authentication
- Profile customization with photo uploads
- Account deletion option

### Blog Posts
- Rich text content
- Optional image attachments
- Edit and delete own posts
- View all posts in dashboard

### Reactions
- Like/dislike functionality
- One reaction per user per post
- Real-time reaction counts

### Security
- Prepared statements to prevent SQL injection
- Password strength requirements (min 6 characters)
- Session management with secure cookies
- File upload validation (type and size)

## 🔧 Configuration

### Environment Variables (.env)

```env
# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=blog_website

# Session Configuration
SESSION_LIFETIME=0
SESSION_PATH=/blogssite/
```

### File Upload Limits

- Profile photos: Max 2MB (JPG, PNG, GIF)
- Post images: Max 5MB (JPG, PNG, GIF)






## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 👨‍💻 Author

Your Name - [GitHub Profile](https://github.com/Nadeesha-Maduwantha)

## 🙏 Acknowledgments

- Built with vanilla PHP, HTML, CSS, and JavaScript
- No frameworks or libraries required
- Modern UI design inspired by contemporary blog platforms




**Happy Blogging! 📝✨**

