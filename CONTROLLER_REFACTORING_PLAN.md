# Controller Refactoring Plan

**Date**: 2025-12-02
**Status**: In Progress (6/8 tasks completed) ✅
**Priority**: HIGH 🔴

## ⚠️ IMPORTANT: Steps 6 & 8 are USER TASKS
- Step 6: Update route names in templates (biggest task - ~1-2 hours)
- Step 8: Manual testing after routes are updated

---

## Overview

The controllers were disorganized with mixed responsibilities. This plan reorganizes them by role and functionality.

### What's Been Completed ✅

1. ✅ **HomeController** created - Homepage and user routing
2. ✅ **ClientController** created - Client booking, cancel, reschedule
3. ✅ **BarberController** created - Barber calendar, schedule, appointments
4. ✅ **ProfileController** created - User profile editing for ALL roles
5. ✅ **AdminController** updated - Procedure methods merged, debug code removed
6. ✅ **Old controllers deleted** - MainController, UserController, ProcedureController

### What Remains ⏳ (USER TASKS)

7. ⏳ **Update all route names in templates** (See Step 6 below)
8. ⏳ **Testing** (See Step 8 below)

---

## Step 4: Create ProfileController ✅ COMPLETED

**Purpose**: Handle profile editing for ALL user types (CLIENT, BARBER, MANAGER, ADMIN)
**Status**: ✅ Done - File created at `src/Controller/ProfileController.php`

### File to Create
`src/Controller/ProfileController.php`

### Method to Extract
From `UserController::editUser()` (lines 36-156)

### Code Structure

```php
<?php

namespace App\Controller;

use App\Form\UserEditFormType;
use App\Repository\AppointmentsRepository;
use App\Repository\BarberProcedureRepository;
use App\Repository\UserRepository;
use App\Service\BarberScheduleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $em;
    private AppointmentsRepository $appointmentsRepository;
    private BarberProcedureRepository $barberProcedureRepository;
    private BarberScheduleService $scheduleService;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $em,
        AppointmentsRepository $appointmentsRepository,
        BarberProcedureRepository $barberProcedureRepository,
        BarberScheduleService $scheduleService
    ) {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->appointmentsRepository = $appointmentsRepository;
        $this->barberProcedureRepository = $barberProcedureRepository;
        $this->scheduleService = $scheduleService;
    }

    /**
     * Edit user profile - works for ALL roles (CLIENT, BARBER, MANAGER, ADMIN)
     * Renders different templates based on user role
     */
    #[Route('/profile/{id}', name: 'profile_edit')]
    public function edit(Request $request, int $id): Response
    {
        // Get target user
        $user = $this->userRepository->findOneById($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Security check
        $authUser = parent::getUser();
        $isAuthUserAdmin = $authUser && in_array('ROLE_ADMIN', $authUser->getRoles());
        $isAuthUserSuperAdmin = $authUser && in_array('ROLE_SUPER_ADMIN', $authUser->getRoles());

        if (!$authUser || ($authUser->getId() !== $user->getId() && !$isAuthUserAdmin)) {
            $this->addFlash('error', 'Нямате достъп до този профил.');
            return $this->redirectToRoute('main');
        }

        // Create form
        $form = $this->createForm(UserEditFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $firstName = $form->get('first_name')->getData();
            $lastName = $form->get('last_name')->getData();
            $nickName = $form->get('nick_name')->getData();
            $phone = $form->get('phone')->getData();

            // If nickname is empty, use first_name
            if (empty($nickName)) {
                $nickName = $firstName;
            }

            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setNickName($nickName);
            $user->setPhone($phone);
            $user->setDateLastUpdate(new \DateTimeImmutable('now'));

            $this->em->persist($user);
            $this->em->flush();
            $this->em->clear();

            $this->addFlash('success', 'Профилът е обновен успешно!');

            return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
        }

        // Get user's appointments (all - past, future, cancelled)
        $userAppointments = $this->userRepository->findAppointmentsByUserId($user->getId());

        // Initialize variables for BARBER-specific data
        $allProcedures = [];
        $barberProcedureIds = [];
        $barberAppointments = [];
        $calendar = [];
        $calendarYear = null;
        $calendarMonth = null;
        $calendarMonthName = '';
        $prevYear = null;
        $prevMonth = null;
        $nextYear = null;
        $nextMonth = null;

        // Load BARBER-specific data if user is barber
        if ($user->isBarber()) {
            // Get barber's upcoming appointments
            $barberAppointments = $this->appointmentsRepository->findUpcomingAppointmentsByBarber($user);
            $allProcedures = $this->em->getRepository(\App\Entity\Procedure::class)->findAll();
            $barberProcedures = $this->em->getRepository(\App\Entity\BarberProcedure::class)
                ->findActiveProceduresForBarber($user);
            $barberProcedureIds = array_map(fn($p) => $p->getId(), $barberProcedures);

            // Get calendar data - check for year/month in query params
            $calendarYear = $request->query->get('year');
            $calendarMonth = $request->query->get('month');

            if (!$calendarYear || !$calendarMonth) {
                $now = new \DateTime('now');
                $calendarYear = (int)$now->format('Y');
                $calendarMonth = (int)$now->format('m');
            } else {
                $calendarYear = (int)$calendarYear;
                $calendarMonth = (int)$calendarMonth;
            }

            $calendar = $this->scheduleService->getMonthCalendar($user, $calendarYear, $calendarMonth);

            // Calculate previous and next month
            $currentDate = new \DateTime("$calendarYear-$calendarMonth-01");
            $prevMonthDate = (clone $currentDate)->modify('-1 month');
            $nextMonthDate = (clone $currentDate)->modify('+1 month');

            $calendarMonthName = $this->getMonthNameBg($calendarMonth);
            $prevYear = (int)$prevMonthDate->format('Y');
            $prevMonth = (int)$prevMonthDate->format('m');
            $nextYear = (int)$nextMonthDate->format('Y');
            $nextMonth = (int)$nextMonthDate->format('m');
        }

        // Render different templates based on user type
        $template = $user->isBarber() ? 'barber/profile.html.twig' : 'client/profile.html.twig';

        return $this->render($template, [
            'user' => $user,
            'isAdmin' => $isAuthUserAdmin,
            'isSuperAdmin' => $isAuthUserSuperAdmin,
            'form' => $form->createView(),
            'userAppointments' => $userAppointments,
            'barberAppointments' => $barberAppointments,
            'allProcedures' => $allProcedures,
            'barberProcedureIds' => $barberProcedureIds,
            'calendar' => $calendar,
            'year' => $calendarYear,
            'month' => $calendarMonth,
            'monthName' => $calendarMonthName,
            'prevYear' => $prevYear,
            'prevMonth' => $prevMonth,
            'nextYear' => $nextYear,
            'nextMonth' => $nextMonth,
        ]);
    }

    /**
     * Helper: Get Bulgarian month name
     */
    private function getMonthNameBg(int $month): string
    {
        $months = [
            1 => 'Януари', 2 => 'Февруари', 3 => 'Март', 4 => 'Април',
            5 => 'Май', 6 => 'Юни', 7 => 'Юли', 8 => 'Август',
            9 => 'Септември', 10 => 'Октомври', 11 => 'Ноември', 12 => 'Декември',
        ];

        return $months[$month] ?? '';
    }
}
```

---

## Step 5: Move Procedure Methods to AdminController ✅ COMPLETED

**Status**: ✅ Done - Procedure methods added to AdminController, debug code removed

### What Was Done

Add these methods from `ProcedureController.php`:

```php
/**
 * List all procedures
 */
#[Route('/procedures', name: 'admin_procedures')]
public function listProcedures(Request $request): Response
{
    $userAuth = parent::getUser();
    $user = $this->userRepository->findOneById($userAuth->getId());

    if (!$user->isUserIsSuperAdmin()) {
        return $this->redirectToRoute('user', ['username' => $user->getEmail()]);
    }

    $procedures = $this->procedureRepository->getAllProcedures();
    $header_row = '';
    if (!empty($procedures)) {
        $header_row = array_keys($procedures[0]);
        $header_row[] = 'edit';
        $header_row[] = 'delete';
        array_shift($header_row);
    }

    return $this->render('admin/procedures.html.twig', [
        'procedures' => $procedures,
        'fields' => $header_row,
        'user' => $user,
    ]);
}

/**
 * Add new procedure
 */
#[Route('/procedure/add', name: 'admin_procedure_add')]
public function addProcedure(Request $request): Response
{
    $userAuth = parent::getUser();
    $user = $this->userRepository->findOneById($userAuth->getId());

    if (!$user->isUserIsSuperAdmin()) {
        return $this->redirectToRoute('user', ['username' => $user->getEmail()]);
    }

    $procedure = new Procedure();
    $form = $this->createForm(ProcedureFormType::class, $procedure);

    try {
        $form->handleRequest($request);
    } catch (\Exception $e) {
        echo 'failed : '.$e->getMessage();
    }

    if ($form->isSubmitted() && $form->isValid()) {
        $procedure->setDateAdded();
        $procedure->setDateLastUpdate();

        $this->em->persist($procedure);
        $this->em->flush();
        $this->em->clear();

        return $this->redirectToRoute('admin_procedures');
    }

    return $this->render('admin/procedure_form.html.twig', [
        'user' => $user,
        'form' => $form->createView(),
    ]);
}

/**
 * Edit procedure
 */
#[Route('/procedure/{id}/edit', name: 'admin_procedure_edit')]
public function editProcedure(Request $request, int $id): Response
{
    $userAuth = parent::getUser();

    if (!$userAuth->isUserIsSuperAdmin()) {
        return $this->redirectToRoute('user', ['username' => $userAuth->getEmail()]);
    }

    $procedure = $this->procedureRepository->findOneBy(['id' => $id]);

    $form = $this->createForm(ProcedureFormType::class, $procedure);

    try {
        $form->handleRequest($request);
    } catch (\Exception $e) {
        echo 'failed : '.$e->getMessage();
    }

    if ($form->isSubmitted() && $form->isValid()) {
        $this->em->persist($procedure);
        $this->em->flush();
        $this->em->clear();

        return $this->redirectToRoute('admin_procedures');
    }

    return $this->render('admin/procedure_form.html.twig', [
        'user' => $userAuth,
        'form' => $form->createView(),
    ]);
}

/**
 * Delete procedure
 */
#[Route('/procedure/{id}/delete', name: 'admin_procedure_delete')]
public function deleteProcedure(Request $request, int $id): Response
{
    $userAuth = parent::getUser();

    if (!$userAuth->isUserIsSuperAdmin()) {
        return $this->redirectToRoute('user', ['username' => $userAuth->getEmail()]);
    }

    $procedure = $this->procedureRepository->findOneBy(['id' => $id]);

    if ($procedure) {
        $this->em->remove($procedure);
        $this->em->flush();
        $this->addFlash('success', 'Процедурата е изтрита успешно.');
    }

    return $this->redirectToRoute('admin_procedures');
}
```

**Also add to AdminController constructor:**
```php
private ProcedureRepository $procedureRepository;

// Add to __construct parameters:
ProcedureRepository $procedureRepository

// Add to __construct body:
$this->procedureRepository = $procedureRepository;
```

---

## Step 6: Update ALL Route Names in Templates

This is the BIGGEST task. Every template reference to old routes must be updated.

### Route Mapping Reference

| Old Route Name                | New Route Name                  | Controller                      |
|-------------------------------|---------------------------------|---------------------------------|
| `main`                        | `main`                          | HomeController ✅ (no change)    |
| `user`                        | `main`                          | HomeController ✅                |
| `barber_appointments`         | `client_book_appointment`       | ClientController                |
| `appointment`                 | `client_book_appointment`       | ClientController                |
| `appointment_cancel`          | `client_cancel_appointment`     | ClientController                |
| `appointment_reschedule`      | `client_reschedule_appointment` | ClientController                |
| `user_edit`                   | `profile_edit`                  | ProfileController               |
| `barber_calendar`             | `barber_calendar`               | BarberController ✅ (no change)  |
| `barber_schedule_day`         | `barber_schedule_day`           | BarberController ✅ (no change)  |
| `barber_schedule_save`        | `barber_schedule_save`          | BarberController ✅ (no change)  |
| `barber_appointment_complete` | `barber_appointment_complete`   | BarberController ✅ (no change)  |
| `barber_appointment_cancel`   | `barber_appointment_cancel`     | BarberController ✅ (no change)  |
| `barber_procedures_save`      | `barber_procedures_save`        | BarberController ✅ (no change)  |
| `procedure_show`              | `admin_procedures`              | AdminController                 |
| `procedure_add`               | `admin_procedure_add`           | AdminController                 |
| `procedure_edit`              | `admin_procedure_edit`          | AdminController                 |
| `procedure_delete`            | `admin_procedure_delete`        | AdminController                 |

### Templates to Update

#### 1. Navigation/Menu Templates
- `templates/base.html.twig` - Main navigation

```bash
# Search and replace:
sed -i '' 's/barber_appointments/client_book_appointment/g' templates/base.html.twig
sed -i '' 's/user_edit/profile_edit/g' templates/base.html.twig
```

#### 2. Client Templates
- `templates/client/book_appointment.html.twig`
- `templates/client/profile.html.twig`

```bash
# In client/book_appointment.html.twig:
sed -i '' 's/barber_appointments/client_book_appointment/g' templates/client/book_appointment.html.twig
sed -i '' 's/user_edit/profile_edit/g' templates/client/book_appointment.html.twig

# In client/profile.html.twig:
sed -i '' 's/appointment_reschedule/client_reschedule_appointment/g' templates/client/profile.html.twig
sed -i '' 's/user_edit/profile_edit/g' templates/client/profile.html.twig
```

#### 3. Barber Templates
- `templates/barber/profile.html.twig`
- `templates/barber/calendar.html.twig`

```bash
# In barber/profile.html.twig:
sed -i '' 's/user_edit/profile_edit/g' templates/barber/profile.html.twig
sed -i '' 's/appointment_reschedule/client_reschedule_appointment/g' templates/barber/profile.html.twig

# In barber/calendar.html.twig:
# Check for any route references
```

#### 4. Admin Templates
- `templates/admin/dashboard.html.twig`
- `templates/admin/users.html.twig`
- `templates/admin/user_edit.html.twig`
- `templates/admin/procedures.html.twig`
- `templates/admin/procedure_form.html.twig`

```bash
# In admin templates:
sed -i '' 's/user_edit/profile_edit/g' templates/admin/*.html.twig
sed -i '' 's/procedure_show/admin_procedures/g' templates/admin/*.html.twig
sed -i '' 's/procedure_add/admin_procedure_add/g' templates/admin/*.html.twig
sed -i '' 's/procedure_edit/admin_procedure_edit/g' templates/admin/*.html.twig
sed -i '' 's/procedure_delete/admin_procedure_delete/g' templates/admin/*.html.twig
```

#### 5. Manager Templates
- `templates/manager/dashboard.html.twig`
- `templates/manager/appointments.html.twig`

```bash
# Check for route references
grep -r "path(" templates/manager/
```

#### 6. Shared Templates
- `templates/shared/_appointment_table.html.twig`

```bash
# Update if needed
sed -i '' 's/appointment_reschedule/client_reschedule_appointment/g' templates/shared/_appointment_table.html.twig
```

#### 7. Security Templates
- `templates/security/login.html.twig`

```bash
# Usually no changes needed - check anyway
grep -r "path(" templates/security/
```

### Verification Script

After making changes, verify all routes exist:

```bash
# List all route references in templates
grep -roh "path('[^']*')" templates/ | sort -u > /tmp/template_routes.txt

# List all registered routes
php bin/console debug:router --format=txt | awk '{print $1}' | sort -u > /tmp/registered_routes.txt

# Find routes in templates that don't exist
comm -23 /tmp/template_routes.txt /tmp/registered_routes.txt
```

---

## Step 7: Delete Old Controllers and Debug Code

### Files to Delete

1. **Delete MainController.php**
```bash
rm src/Controller/MainController.php
```

2. **Delete UserController.php**
```bash
rm src/Controller/UserController.php
```

3. **Delete ProcedureController.php**
```bash
rm src/Controller/ProcedureController.php
```

### Clean Up AdminController

Remove debug methods from `AdminController.php`:
- `new_test()` (line 194)
- `add_data_to_query()` (line 218)
- `select_query()` (line 233)
- `add_data_to_query_origin()` (line 251)
- `test()` (line 258)

```php
// DELETE THESE METHODS (lines 194-end of file)
```

---

## Step 8: Testing Checklist

### Test Each Route Manually

#### Homepage & Authentication
- [ ] `/` - Homepage loads
- [ ] `/login` - Login page works
- [ ] `/register` - Registration works
- [ ] `/logout` - Logout works

#### Client Routes
- [ ] `/book-appointment/{id}` - Booking form loads
- [ ] POST `/book-appointment/{id}` - Booking submission works
- [ ] POST `/appointment/cancel/{id}` - Cancel works
- [ ] GET `/appointment/reschedule/{id}` - Reschedule redirects to booking

#### Profile Routes
- [ ] `/profile/{id}` - CLIENT profile loads (simple)
- [ ] `/profile/{id}` - BARBER profile loads (with tabs)
- [ ] `/profile/{id}` - MANAGER profile loads
- [ ] POST `/profile/{id}` - Profile update works

#### Barber Routes
- [ ] `/barber/calendar/{year}/{month}` - Calendar loads
- [ ] GET `/barber/schedule/day/{date}` - Day schedule (AJAX)
- [ ] POST `/barber/schedule/save` - Save schedule
- [ ] POST `/barber/appointment/{id}/complete` - Mark complete
- [ ] POST `/barber/appointment/{id}/cancel` - Cancel appointment
- [ ] POST `/barber/procedures/save` - Save procedures

#### Manager Routes
- [ ] `/manager/dashboard` - Dashboard loads
- [ ] `/manager/appointments` - Appointments list loads
- [ ] GET `/manager/appointment/{id}/details` - Details (AJAX)
- [ ] POST `/manager/appointment/{id}/update` - Update appointment
- [ ] POST `/manager/appointment/{id}/cancel` - Cancel appointment

#### Admin Routes
- [ ] `/admin_menu/{id}` - Admin dashboard loads
- [ ] `/view_all_clients` - Users list loads
- [ ] `/list_without_role` - Users without role
- [ ] `/list_all_barbers` - Barbers list loads
- [ ] `/admin/user_edit/{id}` - User edit form (admin)
- [ ] `/procedures` - Procedures list loads
- [ ] `/procedure/add` - Add procedure form
- [ ] POST `/procedure/add` - Add procedure works
- [ ] `/procedure/{id}/edit` - Edit procedure form
- [ ] POST `/procedure/{id}/edit` - Edit procedure works
- [ ] `/procedure/{id}/delete` - Delete procedure works

### Test Error Scenarios
- [ ] Access `/profile/{other_user_id}` as non-admin (should deny)
- [ ] Access `/barber/calendar` as CLIENT (should deny)
- [ ] Access `/manager/dashboard` as CLIENT (should deny)
- [ ] Access `/admin_menu` as CLIENT (should deny)
- [ ] Try to book appointment without completing profile
- [ ] Try to cancel past appointment
- [ ] Try to complete already completed appointment

### Test Functional Workflows
- [ ] Complete booking flow: Client books → Barber sees it → Barber completes
- [ ] Cancel flow: Client cancels → Slot becomes available
- [ ] Reschedule flow: Client reschedules → Old cancelled, new created
- [ ] Barber schedule: Set day off → Slots hidden in booking form
- [ ] Procedure assignment: Barber selects procedures → Only shows in booking

---

## Summary

**New Controller Structure:**

```
src/Controller/
├── HomeController.php          ✅ Created - Homepage & routing
├── ClientController.php        ✅ Created - Booking, cancel, reschedule
├── BarberController.php        ✅ Created - Calendar, schedule, appointments
├── ProfileController.php       ⏳ TO CREATE - Profile editing (all roles)
├── AdminController.php         ⏳ TO UPDATE - Merge procedures, remove debug
├── ManagerController.php       ✅ Keep as-is
├── SecurityController.php      ✅ Keep as-is
└── RegistrationController.php  ✅ Keep as-is

DELETED:
├── MainController.php          ❌ Delete after route updates
├── UserController.php          ❌ Delete after route updates
└── ProcedureController.php     ❌ Delete after merging to AdminController
```

**Files Changed by Step:**

- Step 4: Create 1 file (ProfileController.php)
- Step 5: Update 1 file (AdminController.php)
- Step 6: Update ~15-20 templates
- Step 7: Delete 3 files, clean 1 file
- Step 8: Manual testing

**Estimated Time:**
- Step 4: 15 minutes
- Step 5: 20 minutes
- Step 6: 1-2 hours (careful find/replace)
- Step 7: 5 minutes
- Step 8: 30-45 minutes

**Total: ~3 hours**

---

## Quick Reference Commands

```bash
# Find all route usages in templates
grep -rn "path('" templates/

# Find all route definitions in controllers
grep -rn "#\[Route" src/Controller/

# Test route exists
php bin/console debug:router | grep client_book_appointment

# Clear cache after changes
php bin/console cache:clear
```
