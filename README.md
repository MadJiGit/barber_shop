# Barber Shop Booking System

A professional appointment booking system built with **Symfony 7.3** and **PostgreSQL** for barber shops and salons.

## Overview

This project provides a complete solution for barbers and salon owners to:
- Manage staff schedules with flexible working hours
- Accept online bookings (with or without registration)
- Track appointments with advanced filtering and search
- Send automatic email confirmations and reminders
- Handle timezone-aware scheduling (Europe/Sofia)

Suitable for:
- Barber shops
- Hair salons
- Beauty studios
- Any appointment-based service business

---

## Key Features

### For Guests (Non-registered Users)
- **Guest Booking** - Book appointments without registration
- **Email Confirmation** - 15-minute confirmation window via email link
- **Easy Upgrade** - Seamless conversion to registered user account

### For Clients (Registered Users)
- **Instant Booking** - No email confirmation needed
- **Appointment Management** - View, cancel upcoming appointments
- **Appointment History** - Track past visits and services

### For Barbers
- **Visual Calendar** - Monthly view with color-coded availability
- **Schedule Management** - Set working hours, breaks, days off
- **Excluded Time Slots** - Block specific hours (lunch breaks, etc.)
- **Custom Hours** - Override default schedule for specific dates
- **Read-Only Past Dates** - View historical schedule without editing
- **Appointment Dashboard** - Filter by date, status, client

### For Managers
- **Multi-Barber View** - Manage schedules for all barbers
- **Appointment Oversight** - View and manage all bookings
- **Staff Coordination** - Centralized schedule management

### For Admins
- **Full System Control** - EasyAdmin 4 interface
- **User Management** - Create, edit, deactivate users
- **Procedure Management** - Add/edit services and pricing
- **Protected Super Admin** - Cannot be edited by regular admins

---

## User Roles & Hierarchy

The system implements 8 hierarchical roles:

```
SUPER_ADMIN → ADMIN → MANAGER → [RECEPTIONIST, BARBER_SENIOR]
                                           ↓
                      BARBER_SENIOR → BARBER → BARBER_JUNIOR → CLIENT
```

### SUPER_ADMIN
- Cannot be edited or banned by regular ADMIN
- Full system access

### ADMIN
- Manage users, appointments, procedures
- Access to EasyAdmin dashboard

### MANAGER
- Oversee all barbers and appointments
- Manage schedules for all staff
- Cannot access admin settings

### BARBER (Senior/Regular/Junior)
- Manage own schedule and appointments
- Different pricing/duration based on level
- View client information

### CLIENT
- Book appointments
- View own appointment history
- Manage profile

---

## Technical Stack

### Backend
- **PHP 8.2+**
- **Symfony 7.3**
- **Doctrine ORM**
- **PostgreSQL 16** (with TIMESTAMPTZ for timezone support)

### Frontend
- **Twig Templates**
- **Bootstrap 4**
- **jQuery**
- **Webpack Encore** (asset compilation)
- **Stimulus** (interactive components)
- **Gijgo Datepicker** (Bulgarian format support)

### Email Service
- **Brevo (Sendinblue)** - SMTP relay
- 8 email templates (confirmation, cancellation, registration, etc.)

### Deployment
- **Render.com** - Docker-based hosting
- **Multi-stage Docker build** (Node.js → Composer → Apache)
- **Automatic migrations** on deploy
- **PostgreSQL managed database**

---

## Timezone Handling

All dates and times use **Europe/Sofia (UTC+2/+3 DST)**:
- `DateTimeHelper::now()` for all datetime operations
- PostgreSQL `TIMESTAMPTZ` columns (timezone-aware)
- Prevents timezone bugs and ambiguities

---

## Guest Booking Flow

1. **Guest fills booking form** (no registration required)
2. **System creates inactive user** + pending appointment
3. **Email sent** with confirmation/cancellation links
4. **15-minute window** to confirm via email
5. **Unconfirmed bookings** automatically expire (don't block slots)
6. **Optional upgrade** - Guest can register later with same email

---

## Development Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 20+
- Docker Desktop (for PostgreSQL)

### Local Installation

1. **Clone repository**
   ```bash
   git clone <repository-url>
   cd barber_shop
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Start PostgreSQL (Docker)**
   ```bash
   docker-compose up -d postgres
   ```

4. **Configure environment**
   ```bash
   cp .env .env.local
   # Edit .env.local with your settings
   ```

5. **Run migrations**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

6. **Load seed data**
   ```bash
   psql -h localhost -U postgres -d barber_shop < data/barbers_postgres.sql
   psql -h localhost -U postgres -d barber_shop < data/procedures_postgres.sql
   ```

7. **Build frontend assets**
   ```bash
   npm run watch
   ```

8. **Start Symfony dev server**
   ```bash
   symfony serve
   ```

9. **Access application**
   - Open: http://localhost:8000
   - Default users in `data/barbers_postgres.sql` (password: `12345678`)

---

## Production Deployment (Render.com)

### Automatic Deployment

1. **Push to GitHub main branch**
   ```bash
   git push origin main
   ```

2. **Render auto-deploys** (if Auto-Deploy enabled)
   - Builds Docker image
   - Runs migrations via `docker-entrypoint.sh`
   - Deploys to production

### Manual Deployment

1. Open Render Dashboard
2. Select "barber-shop-web" service
3. Click "Manual Deploy" → "Deploy latest commit"

### Environment Variables (Render)

Required environment variables in Render Dashboard:

- `APP_ENV=prod`
- `APP_DEBUG=0`
- `APP_SECRET` (auto-generated)
- `DATABASE_URL` (auto-generated from PostgreSQL service)
- `MAILER_DSN` (must be set manually - Brevo SMTP)
- `MAILER_NOREPLY_EMAIL=reg9643@gmail.com`
- `MAILER_NOREPLY_NAME=Barber Shop`

---

## Testing

Run unit tests:
```bash
php bin/phpunit
```

Coverage report:
```bash
php bin/phpunit --coverage-html=coverage/
```

**Test Suite:**
- 41 tests focused on service layer
- AppointmentValidatorTest (20 tests, 77% coverage)
- EmailServiceTest (10 tests, 75% coverage)
- BarberScheduleServiceTest (11 tests, 29% coverage)

---

## Documentation

- **[USER_GUIDE.md](USER_GUIDE.md)** - User-facing features documentation
- **[CLAUDE.md](CLAUDE.md)** - Development guidelines and project context

---

## Recent Updates

### 2026-01-08
- ✅ Timezone migration (TIMESTAMP → TIMESTAMPTZ)
- ✅ Read-only mode for past dates in calendar
- ✅ Fixed excluded slots counting
- ✅ Backend validation for past date editing
- ✅ Comprehensive user guide

### 2025-12-17
- ✅ Migrated from MySQL to PostgreSQL
- ✅ Fixed 15-minute expired confirmation logic
- ✅ Added manager barber schedules feature

---

## Contributing

This is a private project. For customization or licensing inquiries, please contact the project owner.

---

## License

Proprietary - All rights reserved.

---

**Built with ❤️ using Symfony 7.3 and PostgreSQL**