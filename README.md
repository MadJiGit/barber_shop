# Barber Shop - Appointment Booking System

A comprehensive web-based appointment booking system designed for barbershops and beauty salons with the flexibility to expand to other service-based businesses.

## Project Overview

This platform enables clients to book appointments online while providing powerful management tools for business owners and staff. The system supports both registered users and guest bookings, with a flexible role-based access control system.

## Core Features

### User Roles & Permissions

**SUPER_ADMIN** (Developer Access)
- Full system access and control
- Cannot be modified or banned by any other role
- Manages ADMIN roles and their permissions
- Database-level access and system configuration

**ADMIN** (Business Owner)
- Manages all aspects of the business
- Creates and manages services/procedures
- Manages all user roles except SUPER_ADMIN
- Can ban/unban users (except SUPER_ADMIN)
- Access to all appointments and business analytics
- Cannot modify SUPER_ADMIN permissions

**MANAGER** (Operations Manager)
- Manages appointments across all barbers
- Can view and modify bookings
- Access to scheduling and calendar management
- Cannot modify user roles or system settings

**RECEPTIONIST** (Front Desk)
- Book appointments on behalf of clients
- Answer phone calls and manually create bookings
- View daily schedule
- Basic client information access

**BARBER_SENIOR** / **BARBER** / **BARBER_JUNIOR** (Service Providers)
- View own appointments (past and future)
- Manage personal schedule
- Reserve workspace slots (future feature)
- Update appointment status
- Cannot access other barbers' private data
- Different pricing tiers based on experience level

**CLIENT** (Registered User)
- Book appointments online
- View booking history
- Manage personal profile
- Receive email confirmations and reminders
- Cancel/reschedule appointments
- Loyalty points tracking

**GUEST** (Non-registered User)
- Book appointments with name and email only
- Receive email confirmation with booking details
- Limited to basic booking functionality
- Option to create account after booking
- No access to booking history

### Appointment Management

#### Current Features
- Select barber by name
- Choose service/procedure
- Pick date and available time slot
- Real-time availability display
- Basic form handling

#### In Development
- Conflict prevention (double-booking protection)
- Email confirmation system
- Guest booking workflow
- Appointment validation

#### Planned Features
- Calendar export (iCal/Google Calendar integration)
- Email reminders (24 hours before appointment)
- SMS notifications (optional, if phone provided)
- Cancellation and rescheduling
- Recurring appointments
- Waitlist functionality
- No-show tracking
- Client blacklist for repeated no-shows

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

## Current Development Status

### Completed ✅
- User authentication system
- Role-based access control (basic implementation)
- User entity with roles (SUPER_ADMIN, ADMIN, BARBER, BARBER_JUNIOR, CLIENT)
- Appointments entity structure
- Procedure/Service entity with dual pricing (master/junior)
- Basic appointment booking form
- User profile management
- Admin panel structure
- Security implementation (CSRF, password hashing)
- Barber selection dropdown
- Procedure selection
- Time slot picker UI
- Date selection with calendar

### In Progress 🔄
- Appointment conflict validation
- Enhanced booking interface
- Guest booking workflow
- Email notification foundation
- Cleanup of debug code in MainController
- Bug fixes (getProcedure method, POST handling)

### High Priority (Next Steps) 🎯
- Fix critical bugs in MainController
- Implement appointment conflict detection
- Email confirmation system for bookings
- Guest booking complete workflow
- RECEPTIONIST role implementation
- No-show tracking system
- Appointment cancellation/rescheduling
- Improve appointment form validation

### Medium Priority 📋
- Calendar export functionality (iCal)
- Email reminder system (24h before)
- Barber workspace reservation
- Review and rating system
- Photo gallery for services
- Enhanced admin dashboard with metrics
- Loyalty program foundation
- Client blacklist functionality

### Low Priority / Future 🔮
- SMS integration
- Recurring appointments
- Advanced analytics and reporting
- Mobile app API development
- Payment integration
- Multi-location support
- Mobile native apps (iOS/Android)
- Real-time notifications with WebSockets
- Waitlist management
- Online tips/gratuity processing

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

### Main Entities
- **User**: Authentication, profiles, and role management
- **Appointments**: Booking records with client, barber, service
- **Procedure**: Services offered with pricing tiers
- **AppointmentHours**: Available time slots configuration
- **Roles**: User role definitions (enum)

### Planned Entities
- **Review**: Client reviews for barbers
- **Gallery**: Portfolio images for services
- **LoyaltyPoints**: Rewards tracking
- **NoShow**: Track missed appointments
- **Blacklist**: Banned clients
- **WorkspaceReservation**: Barber station booking
- **Notification**: Email/SMS queue
- **BusinessHours**: Operating hours configuration

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

1. **MainController.php**
   - Line 95: Direct $_POST access instead of form handling
   - Lines 98-106: Commented debug code with hardcoded IDs
   - Line 222: Bug in getProcedure() - uses = instead of ==
   - addAppointment() method incomplete

2. **Appointments Entity**
   - Commented code in relationship definitions
   - Needs cleanup

3. **General**
   - Need comprehensive input validation
   - Missing email notification system
   - No conflict detection for double bookings
   - Guest booking flow incomplete

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

**Last Updated**: 2024-11-26
**Current Branch**: appointment
**Project Status**: Active Development