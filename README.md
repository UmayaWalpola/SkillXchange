# SkillXchange

A skill-based collaboration platform connecting individuals and organizations for project-based learning and teamwork.

## 🚀 Technology Stack

**100% Vanilla Stack - No Frameworks or Libraries**

- **Frontend**: Pure HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 8.2+ (no frameworks)
- **Database**: MariaDB 10.4+
- **Server**: Apache 2.4+ (XAMPP)
- **Architecture**: Custom MVC Pattern

**No external dependencies:**
- ✅ No npm/Node.js required
- ✅ No Composer packages
- ✅ No CSS frameworks (Bootstrap, Tailwind, etc.)
- ✅ No JS libraries (jQuery, React, Vue, etc.)
- ✅ Pure, lightweight, fast

## 📋 Requirements

- PHP 8.2 or higher
- MariaDB 10.4 or higher
- Apache 2.4 or higher
- XAMPP recommended for local development

## 🛠️ Installation

1. **Clone or download the repository**
   ```bash
   git clone https://github.com/UmayaWalpola/SkillXchange.git
   cd SkillXchange
   ```

2. **Set up database**
   - Import `insert-test-data.sql` into your MariaDB database
   - Update database credentials in `app/config/config.php`

3. **Configure XAMPP**
   - Place project in `C:\xampp\htdocs\SkillXchange`
   - Start Apache and MySQL in XAMPP Control Panel

4. **Access the application**
   - Navigate to: `http://localhost/SkillXchange/public/`

## 📁 Project Structure

```
SkillXchange/
├── app/                # Application Layer (MVC)
│   ├── config/         # Database configuration
│   ├── controllers/    # Controllers (Business Logic)
│   ├── models/         # Models (Data Layer)
│   ├── views/          # Views (Presentation Layer)
│   ├── helpers/        # Helper functions
│   ├── middlewares/    # Request/response middleware
│   └── validators/     # Input validation
├── core/               # Core Framework
│   ├── Controller.php  # Base controller
│   ├── Core.php        # Router (URL dispatcher)
│   └── Database.php    # Database wrapper (PDO)
├── public/             # Public Web Root
│   ├── index.php       # Front Controller (entry point)
│   ├── .htaccess       # URL rewriting
│   ├── assets/         # Static assets
│   │   ├── css/        # Stylesheets (vanilla CSS)
│   │   └── js/         # JavaScript (vanilla JS)
│   ├── uploads/        # User uploads
│   └── api/            # API endpoints (optional)
├── database/           # Database files
│   └── migrations/     # SQL migration files
├── scripts/            # Development utilities
│   ├── tests/          # Test scripts
│   └── debug/          # Debug utilities
├── README.md           # Project documentation
├── SETUP_INSTRUCTIONS.txt
├── TESTING_GUIDE.md
└── MVC_STRUCTURE.md    # Complete MVC architecture guide
```

**📖 For detailed MVC architecture documentation, see [MVC_STRUCTURE.md](MVC_STRUCTURE.md)**

## 🎯 Features

- **User Management**: Registration, login, profiles
- **Organizations**: Create and manage organizations
- **Projects**: Post and manage collaborative projects
- **Applications**: Apply to projects and manage applications
- **Team Management**: Assign roles, manage members
- **Task Tracking**: Create, assign, and monitor tasks
- **Progress Dashboard**: Real-time metrics and analytics
- **Communities**: Join and participate in skill communities
- **Chat System**: Real-time project communication
- **Wallet**: Track earnings and transactions

## 🎨 Design System

**Color Palette:**
- Primary Blue: `#658396`
- Accent Blue: `#9cc7df`
- Blue Background: `#d5eaf6`
- Dark Background: `#12120D`
- White: `#FFFFFF`

**Typography:**
- Font Family: Poppins, system-ui, sans-serif
- Base Font Size: 18px
- Line Height: 1.6

## 🔐 Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention via prepared statements
- XSS protection with `htmlspecialchars()`
- Session-based authentication
- Role-based access control

## 📱 Responsive Design

- Mobile-first approach
- Breakpoints: 480px, 768px, 1024px
- Flexible grid layouts
- Touch-friendly UI elements

## 🧪 Testing

Refer to `TESTING_GUIDE.md` for comprehensive testing procedures.

## 📖 Documentation

- `README.md` - Project overview (this file)
- `MVC_STRUCTURE.md` - **Complete MVC architecture guide**
- `SETUP_INSTRUCTIONS.txt` - Setup guide
- `TESTING_GUIDE.md` - Testing procedures
- `insert-test-data.sql` - Test data for development

## 🤝 Contributing

This is a university project. For collaboration inquiries, please contact the repository owner.

## 📄 License

Educational/Academic Project

## 👥 Author

**Umaya Walpola**
- GitHub: [@UmayaWalpola](https://github.com/UmayaWalpola)
- Project: SkillXchange Platform

## 🌟 Acknowledgments

Built with pure web technologies for optimal performance and learning.

---

**Note**: This project demonstrates modern web development using vanilla technologies without relying on frameworks or external libraries.
