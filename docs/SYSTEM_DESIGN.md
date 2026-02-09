# Sistem Pendaftaran Event Fun Run / Marathon

## 📋 Overview

Sistem pendaftaran event fun run/marathon berbasis web yang **scalable, maintainable, dan production-ready**. Sistem ini dirancang untuk menangani ribuan pendaftar dengan fitur lengkap mulai dari registrasi, pembayaran, notifikasi, hingga check-in.

---

## 🎯 Tujuan Sistem

Membangun sistem pendaftaran event fun run/marathon untuk **single event** yang:
- ✅ Mampu menangani banyak pendaftar secara bersamaan
- ✅ Mendukung kategori lomba dengan kuota dan harga berbeda
- ✅ Support tipe pendaftaran individual dan kolektif (5/10 orang)
- ✅ Memiliki alur pembayaran manual dengan verifikasi admin
- ✅ Notifikasi otomatis via WhatsApp dan Email dengan anti-spam strategy
- ✅ Generate e-ticket otomatis setelah pembayaran terverifikasi
- ✅ Sistem check-in untuk race pack collection
- ✅ Dashboard admin lengkap dengan reporting dan export

---

## 📚 Documentation Structure

Dokumentasi sistem ini telah dipecah menjadi beberapa bagian untuk kemudahan implementasi:

### 1. [SYSTEM_ARCHITECTURE.md](SYSTEM_ARCHITECTURE.md)
**Arsitektur Sistem & Tech Stack**
- Tech stack lengkap (Laravel 12, Filament v5, Livewire, Horizon, dll)
- Application architecture & design patterns
- Directory structure & layering
- Queue architecture & Horizon configuration
- Caching strategy
- Security considerations
- Performance optimization
- Scalability planning

### 2. [DATABASE_DESIGN.md](DATABASE_DESIGN.md)
**Database Schema & Design**
- Entity Relationship Diagram (ERD)
- Complete table definitions dengan constraints
- Indexing strategy untuk performance
- Data integrity & foreign keys
- Migration plan & execution order
- Seeding strategy
- Database optimization & monitoring
- Backup & recovery strategy

### 3. [FEATURES_SPECIFICATION.md](FEATURES_SPECIFICATION.md)
**Spesifikasi Fitur Lengkap**
- Event & Race Category Management
- Public Registration (Individual & Collective)
- Payment System (Upload & Verification)
- BIB Number Generation (race-condition safe)
- E-Ticket System dengan QR Code
- Check-In System untuk race pack
- Notification System (triggers & templates)
- Auto-Expiry System
- Admin Dashboard & Widgets
- Reporting & Export functionality
- Settings Management

Setiap fitur dilengkapi dengan:
- Use cases & user flows
- Validation rules
- Business logic & code examples
- Security considerations
- Edge cases handling

### 4. [API_NOTIFICATION_DESIGN.md](API_NOTIFICATION_DESIGN.md)
**Sistem Notifikasi & Anti-Spam Strategy**
- Notification architecture & flow
- WhatsApp integration (Fonnte API)
- Email system configuration
- Queue strategy untuk reliable delivery
- Anti-spam & anti-ban strategy:
  - Rate limiting & throttling
  - Random delay untuk human-like sending
  - Retry mechanism dengan exponential backoff
  - Delivery monitoring & logging
- Notification templates dengan variables
- Error handling & troubleshooting

### 5. [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md)
**Panduan Implementasi Step-by-Step**
- Implementation phases (Week 1-8)
- Phase 1: Foundation (Database, Models, Factories)
- Phase 2: Core Features (Registration, Payment)
- Phase 3: Admin Panel (Filament Resources)
- Phase 4: Notifications (Queue, WhatsApp, Email)
- Phase 5: Advanced Features (E-Ticket, Check-in)
- Phase 6: Testing & Polish
- Phase 7: Deployment
- Task breakdown dengan code examples
- Quick start commands
- Troubleshooting common issues

### 6. [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)
**Panduan Deployment Production**
- Server requirements & specifications
- Server setup (Ubuntu, Nginx, PHP, MySQL, Redis)
- Application deployment process
- Database configuration & optimization
- Queue & scheduler setup dengan Supervisor
- Email & notification configuration
- Security hardening (Firewall, Fail2Ban, SSL)
- Performance optimization
- Monitoring & logging setup
- Backup strategy & automation
- Production checklist (pre & post deployment)
- Maintenance & troubleshooting guide
- Zero-downtime deployment script

---

## 🚀 Quick Start

### For Developers (Implementation)
1. Read [SYSTEM_ARCHITECTURE.md](SYSTEM_ARCHITECTURE.md) untuk memahami struktur sistem
2. Review [DATABASE_DESIGN.md](DATABASE_DESIGN.md) untuk database schema
3. Follow [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md) step-by-step

### For System Administrators (Deployment)
1. Check [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) untuk server requirements
2. Follow deployment checklist
3. Setup monitoring & backup systems

### For Project Managers
1. Review [FEATURES_SPECIFICATION.md](FEATURES_SPECIFICATION.md) untuk scope lengkap
2. Use implementation phases untuk project planning
3. Track progress dengan task breakdown

---

## 💻 Tech Stack Summary

### Backend & Admin Panel
- **Laravel 12** - PHP framework
- **Filament v5** - Admin panel
- **Laravel Horizon** - Queue monitoring
- **Pest PHP v4** - Testing framework

### Frontend (Public Site)
- **Laravel Blade** - Template engine
- **Livewire v4** - Reactive components
- **Alpine.js** - JavaScript framework
- **Tailwind CSS v4** - Styling

### Infrastructure
- **MySQL 8.0+ / PostgreSQL 14+** - Database
- **Redis 7.0+** - Cache & queue
- **Nginx** - Web server
- **Supervisor** - Process manager

### Integrations
- **WhatsApp**: Fonnte API
- **Email**: SMTP / Mailgun / SES
- **Storage**: AWS S3 / DigitalOcean Spaces
- **PDF**: DomPDF untuk E-Ticket

---

## 📊 Key Features Summary


## 📊 Key Features Summary

### 1. Event Management
- Single event dengan multiple race categories
- Category quota management
- BIB number range allocation
- Registration open/close scheduling

### 2. Registration System
- **Individual**: 1 peserta per registrasi
- **Collective**: 5 atau 10 peserta per registrasi
- Auto-generate unique registration number
- 24-hour payment deadline dengan auto-expiry
- Real-time quota checking

### 3. Payment System
- Manual payment dengan QRIS / Bank Transfer
- Upload bukti pembayaran
- Admin verification workflow
- Auto-generate BIB numbers setelah verified
- Payment rejection dengan re-upload capability

### 4. Notification System
- WhatsApp via Fonnte API
- Email via SMTP
- Queue-based delivery dengan rate limiting
- Anti-spam strategy:
  - Random delay (5-15 seconds)
  - Max messages per minute (configurable)
  - Retry dengan exponential backoff
- Complete delivery logging

### 5. E-Ticket System
- Auto-generate PDF ticket setelah payment verified
- QR Code untuk check-in
- Email & WhatsApp delivery
- Downloadable dengan signed URL

### 6. Check-In System
- External app dengan PIN authentication
- QR code scanner
- Real-time participant validation
- Manual search fallback

### 7. Admin Dashboard
- Stats overview widgets
- Registration & payment monitoring
- Pending verification queue
- Financial reports
- Participant export (CSV/Excel)

### 8. Reporting & Export
- Financial reports dengan breakdown
- Participant list dengan custom columns
- Vendor-friendly format (jersey, medal, dll)
- Check-in status reports

---

## 🔒 Security Features

- ✅ HTTPS enforcement dengan SSL certificate
- ✅ CSRF protection
- ✅ XSS protection (Blade auto-escaping)
- ✅ SQL injection protection (Eloquent ORM)
- ✅ Rate limiting pada registration endpoints
- ✅ File upload validation & restriction
- ✅ Secure file storage (private disk)
- ✅ Role-based access control
- ✅ Activity logging
- ✅ Environment variables untuk sensitive data

---

## ⚡ Performance Features

- ✅ Redis caching (config, queries, sessions)
- ✅ OPcache untuk PHP optimization
- ✅ Database indexing strategy
- ✅ Query optimization dengan eager loading
- ✅ Asset optimization (minification, compression)
- ✅ Gzip compression enabled
- ✅ HTTP/2 support
- ✅ CDN-ready static assets

---

## 📈 Scalability Features

- ✅ Queue-based background processing
- ✅ Horizontal scaling ready (stateless app)
- ✅ Database connection pooling
- ✅ Redis untuk distributed caching
- ✅ Load balancer compatible
- ✅ Multiple queue workers support
- ✅ Partition-ready database design

---

## 🧪 Testing Strategy

### Unit Tests
- Models & relationships
- Services & actions
- Helpers & utilities

### Feature Tests
- Registration flow
- Payment flow
- Notification delivery
- Admin operations

### Browser Tests
- Critical user journeys
- Form submissions
- Payment upload
- Check-in scanner

### Coverage Target
- Minimum 80% code coverage
- 100% coverage untuk critical paths

---

## 📞 Support & Maintenance

### Monitoring
- Laravel Horizon (queue monitoring)
- Application logs (Laravel Log)
- Server monitoring (CPU, Memory, Disk)
- Error tracking (Sentry)

### Backup
- Daily database backups (30-day retention)
- Automated backup to S3
- Point-in-time recovery capability

### Updates
- Regular security updates
- Dependency updates
- Performance optimization
- Bug fixes & improvements

---

## 📖 Getting Started

### Prerequisites
- PHP 8.4+
- Composer
- Node.js 20+
- MySQL 8.0+ / PostgreSQL 14+
- Redis 7.0+

### Installation (Development)
```bash
# Clone repository
git clone <repository-url>
cd funrun-app

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Build assets
npm run dev

# Start services
php artisan serve
php artisan queue:work
php artisan horizon
```

### Deployment (Production)
See [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) for complete production deployment instructions.

---

## 🎓 Documentation Guide

**New to the project?** Start here:
1. Read this overview document
2. Review [SYSTEM_ARCHITECTURE.md](SYSTEM_ARCHITECTURE.md)
3. Study [DATABASE_DESIGN.md](DATABASE_DESIGN.md)

**Ready to implement?**
1. Follow [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md) phase by phase
2. Reference [FEATURES_SPECIFICATION.md](FEATURES_SPECIFICATION.md) untuk detail fitur
3. Check [API_NOTIFICATION_DESIGN.md](API_NOTIFICATION_DESIGN.md) untuk notifikasi

**Ready to deploy?**
1. Follow [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) step by step
2. Complete all checklists
3. Setup monitoring & backups

---

## 👥 Contributing

### Development Workflow
1. Create feature branch dari `main`
2. Implement feature dengan tests
3. Run `vendor/bin/pint` untuk formatting
4. Run `php artisan test` untuk validasi
5. Create Pull Request
6. Code review & approval
7. Merge to `main`

### Code Standards
- Follow PSR-12 coding standards
- Use Laravel best practices (see [AGENTS.md](../AGENTS.md))
- Write descriptive commit messages
- Add tests untuk new features
- Update documentation jika diperlukan

---

## 📄 License

[Your License Here]

---

## 💬 Contact & Support

- **Project Lead**: [Your Name]
- **Technical Lead**: [Tech Lead Name]
- **Email**: support@yourdomain.com
- **Documentation**: This repository `/docs` folder

---

## 🎉 Conclusion

Sistem ini dirancang sebagai **production-ready solution** untuk event fun run/marathon dengan:

✅ **Complete Features** - Registration hingga check-in  
✅ **Scalable Architecture** - Handle ribuan pendaftar  
✅ **Reliable Notifications** - Anti-spam & anti-ban strategy  
✅ **Secure & Performant** - Best practices & optimization  
✅ **Well Documented** - 6 detailed documentation files  
✅ **Easy to Deploy** - Complete deployment guide  
✅ **Maintainable** - Clean code & clear structure  

**Ready to build?** Start dengan [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md)  
**Ready to deploy?** Follow [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)

---

*Last Updated: February 2026*
