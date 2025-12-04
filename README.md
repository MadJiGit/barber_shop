# Barber Shop - Appointment Booking System

A comprehensive web-based appointment booking system designed for barbershops and beauty salons with the flexibility to expand to other service-based businesses.

## Project Overview

This platform enables clients to book appointments online while providing powerful management tools for business owners and staff. The system supports both registered users and guest bookings, with a flexible role-based access control system.

## Core Features

### User Roles & Permissions

The system implements a hierarchical role-based access control system with the following roles:

**Role Hierarchy:**
```
ROLE_CLIENT (base)
├─ ROLE_BARBER_JUNIOR
│  └─ ROLE_BARBER
│     └─ ROLE_BARBER_SENIOR
├─ ROLE_RECEPTIONIST
├─ ROLE_MANAGER (inherits all barber levels + receptionist + client)
├─ ROLE_ADMIN (inherits manager + all others)
└─ ROLE_SUPER_ADMIN (inherits everything)
```

**ROLE_SUPER_ADMIN** (Developer Access)
- Full system access and control
- Cannot be modified or banned by any other role
- Manages ADMIN roles and their permissions
- Database-level access and system configuration
- Creates and manages all service procedures
- Access: `/admin/*` routes

**ROLE_ADMIN** (Business Owner)
- Manages all aspects of the business
- Manages all user roles except SUPER_ADMIN
- Can ban/unban users (except SUPER_ADMIN)
- Access to all appointments and business analytics
- Cannot modify SUPER_ADMIN permissions
- Cannot create/edit procedures (SUPER_ADMIN only)
- Access: `/admin/*` routes

**ROLE_MANAGER** (Operations Manager) ⚠️ *Planned - not yet implemented*
- Intended to manage appointments across all barbers
- Will view and modify bookings
- Access to scheduling and calendar management
- Cannot modify user roles or system settings
- Planned access: `/manager/*` routes

**ROLE_RECEPTIONIST** (Front Desk) ⚠️ *Planned - not yet implemented*
- Intended to book appointments on behalf of clients
- Will answer phone calls and manually create bookings
- View daily schedule
- Basic client information access
- Planned access: `/receptionist/*` routes

**ROLE_BARBER_SENIOR / ROLE_BARBER / ROLE_BARBER_JUNIOR** (Service Providers)
- **All barber levels can:**
  - View upcoming appointments (clients who booked with them)
  - Manage personal weekly schedule (working hours per day)
  - Set schedule exceptions (days off, custom hours, excluded time slots)
  - Select which procedures they can perform
  - View monthly calendar with appointment occupancy
  - Complete appointments (mark as done)
  - Cancel appointments with client notification
- **Cannot access other barbers' private data**
- **Seniority determines pricing:**
  - `ROLE_BARBER_SENIOR` and `ROLE_BARBER` → `price_master` and `duration_master`
  - `ROLE_BARBER_JUNIOR` → `price_junior` and `duration_junior`
- Access: `/user_edit/{id}`, `/barber/calendar/*`, `/barber/schedule/*`

**ROLE_CLIENT** (Registered User)
- Book appointments online
- View booking history (past and upcoming)
- Manage personal profile (name, phone, nickname)
- Cancel appointments
- Reschedule appointments (cancel + rebook workflow)
- Must complete profile (first name required) before booking
- Access: `/barber_appointments/{id}`, `/user_edit/{id}`, `/appointment/cancel/{id}`

**GUEST** (Non-registered User) ⚠️ *Planned - not yet implemented*
- Intended to book appointments with name and email only
- Will receive email confirmation with booking details
- Limited to basic booking functionality
- Option to create account after booking
- No access to booking history

### Appointment Management

#### Client Booking Flow ✅
1. **Select Procedure** - Choose from available services
2. **Barber Filtering** - System shows only barbers who can perform selected procedure
3. **Date Selection** - Navigate calendar (blocks past dates)
4. **Time Slot Selection** - Smart slot display:
   - 30-minute intervals for procedures that are 30/90 minutes
   - Full hour intervals for 60/120 minute procedures
   - Occupied slots shown as unavailable
   - Validates against barber working hours and schedule exceptions
5. **Conflict Detection** - Prevents double-booking for both client and barber
6. **Validation** - Comprehensive checks:
   - Cannot book in the past
   - Barber must be working at selected time
   - Appointment must fit within barber's working hours
   - No overlapping appointments
7. **Confirmation** - Appointment created with status 'confirmed'

#### Barber Schedule Management ✅
- **Weekly Schedule Template** - Set working hours for each day of week
- **Schedule Exceptions:**
  - Full day off (vacation, sick day)
  - Custom hours for specific dates
  - Exclude specific time slots (e.g., lunch break)
- **Monthly Calendar View** - Visual display of occupancy:
  - Available (no appointments)
  - Partial (some slots booked)
  - Full (all slots booked)
  - Unavailable (day off)
- **Appointment Visibility** - View all clients who booked appointments
- **Appointment Actions:**
  - Mark as completed ⚠️ *Backend TODO*
  - Cancel with client notification ⚠️ *Backend TODO*

#### Barber-Procedure Mapping ✅
- Barbers select which procedures they can perform
- Many-to-many relationship with temporal validity
- `valid_from` and `valid_until` dates track when barber gained/lost capability
- `can_perform` flag for active/inactive procedures
- System filters barbers in booking form based on procedure capability

#### Planned Features
- Calendar export (iCal/Google Calendar integration)
- Email reminders (24 hours before appointment)
- SMS notifications (optional, if phone provided)
- Email confirmation system
- Guest booking workflow
- Recurring appointments
- Waitlist functionality
- No-show tracking
- Client blacklist for repeated no-shows
- Manager/Admin appointment editing interface

### Service Management

- Multiple service types with custom pricing
- Different rates for Senior/Master/Junior barbers
- Custom duration per service and barber level
- Service availability toggle
- Price history tracking
- Service gallery with portfolio images

### Admin Panel

- Dashboard with key metrics
- Appointment calendar view
- User management interface
- Service/procedure management
- Business hours configuration
- Reporting and analytics
- Revenue tracking
- Barber performance metrics
- Customer retention analytics

### Additional Features (Planned)

**Client Features:**
- Review and rating system for barbers
- Photo gallery of completed work
- Loyalty/rewards program (every Nth service free/discounted)
- Online gratuity/tips
- Favorite barber selection
- Appointment history with photos

**Business Features:**
- Multi-location support (future)
- Franchise management
- Inventory tracking for products
- Staff scheduling
- Commission tracking
- Marketing campaign tools
- Client demographics and analytics

**Technical Features:**
- Real-time availability updates (WebSockets)
- Progressive Web App (PWA) support
- Mobile responsive design
- API for future mobile apps
- Multi-language support
- Email queue system
- Cache optimization for performance

## Technical Stack

- **Framework**: Symfony 7.1
- **PHP Version**: 8.2+
- **Database**: Doctrine ORM (MySQL/PostgreSQL)
- **Templating**: Twig
- **Authentication**: Symfony Security Component
- **Email**: Symfony Mailer
- **Frontend**: Bootstrap 4.x (from template)
- **UI Template**: Professional Barber Shop HTML5 Template
- **Queue**: Symfony Messenger (planned)
- **Cache**: Redis/Memcached (planned)
- **Real-time**: Mercure/WebSockets (planned)

## Frontend Template & Assets

This project uses a professional HTML5 Barber Shop template for the UI/UX design.

**Template Structure:**
- **Assets Location**: `/public/assets/`
  - `css/` - Template stylesheets (Bootstrap, custom styles, animations)
  - `js/` - Template JavaScript files (jQuery, Bootstrap, plugins)
  - `img/` - Template images, icons, and graphics
  - `fonts/` - Icon fonts (FontAwesome, Themify) and custom fonts
  - `scss/` - Source SCSS files (for customization if needed)

- **Custom Assets**: `/public/`
  - `css/` - Our custom CSS overrides and additional styles
  - `js/` - Our custom JavaScript functionality
  - `images/` - Our custom images and uploads

- **Reference HTML**: `/template_reference/`
  - Original HTML files kept for reference during development
  - `index.html` - Homepage design and structure
  - `services.html` - Services page layout
  - `portfolio.html` - Gallery/portfolio design
  - `contact.html` - Contact form design
  - `about.html`, `blog.html`, etc. - Other pages for future use

**Template Features:**
- ✨ Fully responsive design (mobile, tablet, desktop)
- 🎨 Modern animations and smooth transitions
- 💈 Professional barber shop aesthetic
- 📦 Pre-built sections (hero slider, services grid, portfolio, testimonials)
- 📝 Contact forms and booking interface components
- 🎯 Cross-browser compatibility
- ⚡ Optimized performance

**Asset Loading:**
- Template assets load first from `/public/assets/`
- Custom overrides load after from `/public/css/` and `/public/js/`
- This allows us to customize without modifying the original template

## Architecture & Services

### Core Services

**BarberScheduleService** (`/src/Service/BarberScheduleService.php`)
- Manages all barber availability and schedule logic
- Key methods:
  - `getMonthCalendar()` - Returns calendar with daily availability status
  - `getDaySchedule()` - Returns 30-min time slots for a specific date
  - `isBarberWorkingAt()` - Validates barber availability at specific datetime
  - `getWorkingHoursForDate()` - Returns working hours for specific date
  - `saveException()` - Creates/updates schedule exceptions
- Accounts for: default schedule + exceptions + occupied appointments + excluded slots

**AppointmentValidator** (`/src/Service/AppointmentValidator.php`)
- Validates appointments before booking
- Key methods:
  - `validateAppointment()` - Comprehensive validation, returns array of errors
  - `isBarberAvailable()` - Checks for barber time conflicts
  - `isClientAvailable()` - Checks for client time conflicts
  - `isInPast()` - Prevents booking in past
- Validation checks:
  - Time not in past
  - Barber is working at selected time
  - Appointment fits within working hours
  - No overlapping appointments (barber or client)

### Controllers & Routes

**MainController** (`/src/Controller/MainController.php`)
- `/` - Homepage
- `/barber_appointments/{id}` - Appointment booking form (GET, POST)
- `/api/occupied-slots/{date}` - AJAX endpoint for occupied slots
- Integrates: BarberScheduleService, AppointmentValidator, BarberProcedureRepository

**UserController** (`/src/Controller/UserController.php`)
- `/user_edit/{id}` - User profile edit
- `/appointment/cancel/{id}` - Cancel appointment (client)
- `/appointment/reschedule/{id}` - Start reschedule workflow
- `/barber/calendar/{year}/{month}` - Barber monthly calendar view
- `/barber/schedule/day/{date}` - Get day schedule (AJAX)
- `/barber/schedule/save` - Save schedule exception
- `/barber/procedures/save` - Save barber procedures

**AdminController** (`/src/Controller/AdminController.php`)
- `/admin_menu/{id}` - Admin dashboard
- `/view_all_clients` - List all clients
- `/list_all_barbers` - List all barbers
- `/list_without_role` - Users without role
- `/admin/user_edit/{id}` - Edit user & assign roles

**ProcedureController** (`/src/Controller/ProcedureController.php`)
- `/procedure_show/{id}` - List procedures (SUPER_ADMIN)
- `/procedure_add/{id}` - Create procedure (SUPER_ADMIN)
- `/procedure_edit/{id}` - Edit procedure (SUPER_ADMIN)
- `/procedure_delete/{id}` - Delete procedure (SUPER_ADMIN)

### Frontend Architecture

**Asset Organization:**
```
/public/
├── assets/          # Template assets (Bootstrap, jQuery, plugins)
│   ├── css/
│   ├── js/
│   ├── img/
│   └── fonts/
├── css/             # Custom CSS
│   ├── barber_appointments.css
│   └── [other custom styles]
├── js/              # Custom JavaScript
│   ├── appointment_form.js      # Client booking logic
│   └── barber_appointments.js   # Barber appointment actions
└── images/          # Custom images
```

**Code Separation Standards:**
- ❌ NO JavaScript code in Twig templates (except initialization)
- ✅ All JS logic → `/public/js/`
- ✅ All CSS → `/public/css/`
- ✅ All HTML → `/templates/`

## Current Development Status

### Completed ✅
**Authentication & Authorization:**
- User authentication system with form login
- Role-based access control with 7-level hierarchy
- CSRF protection and password hashing
- Remember me functionality (7-day sessions)

**User Management:**
- User registration (creates ROLE_CLIENT)
- Profile management (name, phone, nickname)
- Admin user management (role assignment)
- Profile completion enforcement before booking

**Appointment System:**
- Client booking workflow with procedure selection
- Barber filtering by procedure capability
- Smart time slot display (30-min intervals, filtered by procedure duration)
- Date navigation with past date blocking
- Comprehensive appointment validation (AppointmentValidator)
- Conflict detection (prevents double-booking)
- Appointment cancellation (client side)
- Occupied slot display on booking form

**Barber Management:**
- Barber-procedure many-to-many mapping with temporal validity
- Weekly schedule template (BarberSchedule)
- Schedule exceptions (days off, custom hours, excluded slots)
- Monthly calendar view with occupancy status
- Day view with 30-minute time slot detail
- Barber appointments table (view clients who booked)

**Services:**
- BarberScheduleService for all availability logic
- AppointmentValidator for comprehensive validation
- Dual pricing system (master/junior)
- Procedure CRUD (SUPER_ADMIN only)

**Frontend:**
- Clean separation: JS → /public/js/, CSS → /public/css/, HTML → /templates/
- Bootstrap 4 template integration
- Responsive design
- AJAX-based slot updates

### High Priority (Next Steps) 🎯
1. **Complete barber appointment actions** (immediate priority)
   - Backend endpoints: `/barber/appointment/{id}/complete` and `/barber/appointment/{id}/cancel`
   - Update appointment status
   - Client notification system

2. **Implement Manager/Admin appointment editing** (high priority)
   - Manager dashboard with appointment list
   - Full edit capability (change date, time, barber, procedure)
   - Route access control (ROLE_MANAGER or ROLE_ADMIN)

3. **Database performance optimization**
   - Add indexes for common queries
   - Optimize barber_procedure lookups

### Medium Priority 📋
- Email confirmation system for bookings
- Email reminders (24h before appointment)
- Guest booking workflow (book without registration)
- RECEPTIONIST role implementation with booking interface
- Enhanced client reschedule flow (direct to form with pre-selected procedure)
- No-show tracking system
- Review and rating system for barbers
- Photo gallery for services
- Client blacklist functionality

### Low Priority / Future 🔮
- Calendar export (iCal/Google Calendar integration)
- SMS notifications
- Recurring appointments
- Waitlist management
- Advanced analytics and reporting
- Payment integration
- Online tips/gratuity processing
- Mobile app API development
- Multi-location support
- Real-time notifications with WebSockets

## Installation

```bash
# Clone repository
git clone [repository-url]

# Install dependencies
composer install

# Configure environment
cp .env .env.local
# Edit .env.local with your database credentials

# Create database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate

# Load fixtures (optional - for development)
php bin/console doctrine:fixtures:load

# Start development server
php -S localhost:8000 -t public/
# or with Symfony CLI
symfony serve
```

## Database Schema

### Core Entities ✅

**User** (`/src/Entity/User.php`)
- Authentication, profiles, and role management
- Fields: email (unique), password (hashed), roles (JSON array), first_name, last_name, nick_name, phone
- Status: is_active, is_banned
- Timestamps: date_added, date_last_update, date_banned
- Email verification: confirmation_token, token_expires_at

**Appointments** (`/src/Entity/Appointments.php`)
- Booking records linking client → barber → procedure
- Fields: date (DateTimeImmutable), duration (int minutes), status (string), notes, cancellation_reason
- Relationships: client (ManyToOne User), barber (ManyToOne User), procedure_type (ManyToOne Procedure)
- Status values: 'pending', 'confirmed', 'completed', 'cancelled'
- Timestamps: date_added, date_last_update, date_canceled
- Indexes: date, (barber_id, date), (client_id, date), status

**Procedure** (`/src/Entity/Procedure.php`)
- Service type definitions with dual pricing
- Fields: type (string), price_master, price_junior, duration_master, duration_junior, available (bool)
- Timestamps: date_added, date_last_update, date_stopped

**BarberProcedure** (`/src/Entity/BarberProcedure.php`)
- Many-to-many mapping with temporal validity
- Relationships: barber (ManyToOne User), procedure (ManyToOne Procedure)
- Fields: can_perform (bool), valid_from (DateTimeImmutable), valid_until (DateTimeImmutable, nullable)
- Method: isCurrentlyValid() - checks if barber can perform procedure now

**BarberSchedule** (`/src/Entity/BarberSchedule.php`)
- Weekly recurring schedule template per barber
- Relationship: barber (ManyToOne User)
- schedule_data (JSON): `{"0": {"working": false}, "1": {"start": "09:00", "end": "18:00", "working": true}, ...}`
- Default: Mon-Fri 09:00-18:00, Sat 09:00-13:00, Sun off
- Timestamps: created_at, updated_at

**BarberScheduleException** (`/src/Entity/BarberScheduleException.php`)
- Overrides default schedule for specific dates
- Relationship: barber (ManyToOne User), created_by (ManyToOne User)
- Fields: date (DateImmutable), is_available (bool), start_time, end_time (nullable), excluded_slots (JSON array), reason
- Use cases: vacations, special hours, lunch breaks, training days
- Timestamp: created_at

**AppointmentHours** (`/src/Entity/AppointmentHours.php`)
- Fixed time slot definitions (legacy/reference)
- Hard-coded slots: 10:00, 11:00, 12:00, 13:00, 14:00, 15:00, 16:00, 17:00

**BusinessHours** (`/src/Entity/BusinessHours.php`)
- Global business hours (shop-wide, currently unused)
- Fields: day_of_week (0-6), open_time, close_time, is_closed

### Planned Entities
- **Review**: Client reviews for barbers
- **Gallery**: Portfolio images for services
- **LoyaltyPoints**: Rewards tracking
- **NoShow**: Track missed appointments
- **Blacklist**: Banned clients
- **WorkspaceReservation**: Barber station booking
- **Notification**: Email/SMS queue

## Security Considerations

- CSRF protection on all forms
- Password hashing (Symfony default with bcrypt/argon2)
- Role-based access control (RBAC)
- SUPER_ADMIN role protection (immutable by other users)
- Session security and timeout
- Input validation and sanitization
- Rate limiting for guest bookings (spam protection)
- SQL injection prevention (Doctrine ORM)
- XSS protection (Twig auto-escaping)

## Known Issues & Technical Debt

1. **Barber Appointment Actions** (High Priority)
   - Complete/Cancel buttons in UI are placeholders
   - Backend endpoints not implemented: `/barber/appointment/{id}/complete`, `/barber/appointment/{id}/cancel`
   - TODO comments in `/public/js/barber_appointments.js`

2. **Manager/Admin Features** (High Priority)
   - ROLE_MANAGER exists in hierarchy but has no dedicated controller or routes
   - No appointment editing interface for managers/admins
   - Cannot reschedule appointments on behalf of clients

3. **Email System** (Medium Priority)
   - No confirmation emails after booking
   - No reminder emails before appointments
   - No notification on cancellation
   - Token system exists but verification flow incomplete

4. **Guest Booking** (Medium Priority)
   - Guest booking workflow not implemented
   - System requires registration before booking

5. **Performance** (Low Priority)
   - Need database indexes on common query patterns
   - Consider caching for barber schedule lookups

6. **Miscellaneous**
   - BusinessHours entity exists but not integrated into system
   - AppointmentHours entity is legacy (replaced by dynamic 30-min slots)
   - is_banned flag in User entity not enforced at login

## API Documentation (Future)

RESTful API endpoints will be documented here for mobile app integration.

## Testing

```bash
# Run tests (when implemented)
php bin/phpunit

# Code style check
php vendor/bin/php-cs-fixer fix --dry-run

# Static analysis (future)
php vendor/bin/phpstan analyse
```

## Contributing

This is a private project. Contact the repository owner for contribution guidelines.

## License

Proprietary - All rights reserved

## Changelog

### v0.1.0 (Current - Development)
- Initial project setup
- Basic authentication system
- Role management
- Appointment booking foundation
- Admin panel structure

---

**Last Updated**: 2025-12-02
**Current Branch**: appointment
**Project Status**: Active Development
**Symfony Version**: 7.1
**PHP Version**: 8.2+