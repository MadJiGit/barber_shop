# Barber Shop Booking System

A lightweight and customizable web-based application built with **Symfony** and **MySQL** to manage appointments in a barber shop.

## Overview

This project allows barbers and salon owners to:
- Organize staff schedules
- Let clients book appointments online
- Manage appointments from a clean dashboard
- Send automatic email confirmations

The system is suitable for:
- Barber shops
- Hair salons
- Small grooming studios
- Other appointment-based services

---

## Purpose

This project was created as a ready-to-use appointment booking system for barbers.  
If you are a barber or own a salon and want to use or customize this system for your needs, feel free to get in touch.  
We offer installation, customization, branding, and long-term support.

---

## Features

- User roles (Admin, Barber, Receptionist, Client)
- Mobile-friendly interface
- Email confirmations (via Resend)
- Role-based access
- Multi-language support (EN/BG)
- Easy deployment on Vercel, shared hosting, or VPS

---

## User Roles & Permissions

### Admin (Shop Owner)
- Full access to manage barbers, appointments, and settings

### Barber
- View own upcoming appointments
- Mark appointments as completed or canceled

### Receptionist
- Create, edit, or cancel appointments
- No access to sensitive shop settings

### Client
- Can request an appointment via public booking form
- Receives confirmation email

---

## Technologies Used

- PHP 8.3+
- Symfony 7+
- Doctrine ORM
- MySQL
- Twig Templates
- Bootstrap 5
- Resend.com (email service)
- Vercel or other hosting providers