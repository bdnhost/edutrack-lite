# Changelog
All notable changes to EduTrack Lite will be documented in this file.

## [1.0.2] - 2024-12-06

### Fixed
- 🐛 **Critical:** Fixed `course.html` data loading issues
  - Fixed API endpoint URLs (`course_id` → `id` parameter)
  - Fixed stats endpoint to use `courses.php` instead of non-existent `stats.php`
  - Added comprehensive logging for debugging
  - Improved error handling for all AJAX calls
- 📊 **Dashboard:** Fixed courses not showing on initial load
  - Added detailed logging to `index.html`
  - Identified course ownership issue
- 📱 **QR Code:** Fixed QR not loading in `session.html`
  - Moved `startLesson()` function to `main.js` for global access
  - Fixed session creation and QR generation

### Added
- 🧪 **Testing Tools:**
  - `test_course.html` - Comprehensive course.html testing
  - `test_dashboard.html` - Dashboard functionality testing
  - `test_qr.html` - QR code generation testing
- 🔧 **Diagnostic Tools:**
  - `api/fix_courses.php` - Course ownership repair tool
  - `api/test_session.php` - Session diagnostics
  - `api/advanced_debug.php` - Advanced session debugging
- 📄 **Hebrew Documentation:**
  - `תיקון_קורסים_ותלמידים.txt` - Quick fix guide
  - `דוח_בדיקת_עומק.md` - Comprehensive system audit (70-91 hours of work identified)
  - `TODO_PRIORITY.md` - Prioritized feature roadmap
  - Multiple Hebrew testing guides

### Changed
- 📝 Improved logging throughout the system
- 🔍 Enhanced error messages for better debugging
- 🔄 Standardized API response format handling

## [1.0.1] - 2024-12-05

### Fixed
- 🐛 **Critical:** Fixed session logout issue - users no longer get kicked back to login
- 🔧 Fixed database field mismatch (`user_id` → `lecturer_id`)
- ⏰ Extended session lifetime from 30 minutes to 24 hours
- 🔄 Fixed `requireAuth()` function to use callbacks properly
- 📝 Removed premature `session_write_close()` calls

### Added
- ✅ Created `api/config.php` with correct database credentials
- 🛠️ Added `api/test_db.php` - database diagnostic tool
- 🔍 Added `api/debug_session.php` - session debugging tool
- 📚 Added `lessons` table support for lesson management
- 📄 Added comprehensive documentation:
  - `INSTALLATION.md` - Installation guide
  - `FIXES_SUMMARY.md` - Detailed fix summary
  - `START_HERE.md` - Quick start guide
- 🔄 Added database migrations:
  - `migration_user_to_lecturer.sql`
  - `migration_add_lessons.sql`
  - `FULL_MIGRATION.sql` - Complete migration script

### Changed
- 📊 Updated database schema with `lecturer_id` field
- 🔐 Improved session configuration with better cookie settings
- 📱 Updated `index.html` and `session.html` to use callback pattern
- 📝 Updated all API files to use `lecturer_id` instead of `user_id`

### Documentation
- 📖 Updated README.md with troubleshooting section
- 📝 Added detailed installation instructions
- 🔧 Added migration guides

## [1.0.0] - 2024-12-05

### Added
- 🎉 Initial release
- ✅ User authentication (login/register)
- 📚 Course management (create, view, archive)
- 👥 Student management (manual add, CSV import)
- 📱 QR Code attendance system
- ⏱️ 30-minute session timer
- 🔄 Real-time attendance updates
- 📊 Basic statistics and reports
- 🌐 Full RTL Hebrew support
- 🔐 Security features (password hashing, SQL injection protection)
- 📱 Responsive design (desktop, tablet, mobile)
- 📂 CSV import/export functionality

### Features Implemented
- Session-based authentication
- Multi-institution support
- Attendance tracking with phone validation
- Auto-closing expired sessions
- Interactive dashboard
- Bootstrap 5 UI
- MySQL database with views for statistics

### Security
- Password encryption (bcrypt)
- Prepared statements (SQL injection protection)
- XSS protection
- Session security
- .htaccess security headers

---

## [Planned] - Future Versions

### v1.1.0
- [ ] WhatsApp integration for notifications
- [ ] Email reports
- [ ] Advanced filtering in reports
- [ ] Export to Excel with formatting

### v1.2.0
- [ ] Multi-language support
- [ ] Dark mode
- [ ] Custom branding per institution
- [ ] API documentation

### v2.0.0
- [ ] Mobile app (React Native)
- [ ] Integration with Moodle/LMS
- [ ] Advanced analytics with charts
- [ ] Role-based permissions (admin, lecturer, TA)

---

**Note:** This project follows [Semantic Versioning](https://semver.org/).
