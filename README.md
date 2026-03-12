<div align="center"><img src="https://files-cdn.chatway.app/assets/images/logo-text.svg" width="400" alt="Chatway Logo"></div>

# Chatway Poll Management

This project is a **Poll Management System** developed as part of a **technical assignment for Chatway**.

The application allows administrators to create and manage polls while enabling users to vote through a public link. The system also demonstrates **real-time vote updates using Laravel broadcasting and WebSockets**.

The main goal of the project was to implement a clean, scalable backend structure with proper validation, a simple user interface, and real-time functionality.

---

# Features

## Admin Features

* Admin registration and authentication
* Admin profile management
* Create polls with multiple options
* Edit polls before votes are recorded
* Poll scheduling (start and end time)
* Activate or deactivate polls
* View poll results
* Share public poll links

## Public Poll Features

* Public poll page accessible via a shareable link
* Users can vote without authentication
* Each user/IP can vote only once
* Poll availability controlled by:

  * active/inactive status
  * start time
  * end time
* Poll results displayed directly on the poll page

## Real-Time Updates

* Vote counts update instantly using Laravel broadcasting
* Admin results page updates live
* Public poll results update live
* No page refresh required

---

# Tech Stack

## Backend

* Laravel
* MySQL
* Laravel Broadcasting
* Laravel Reverb (WebSocket server)

## Authentication

* Laravel Breeze (for admin authentication and profile management)

## Frontend

* Blade
* TailwindCSS
* JavaScript (Fetch API + Laravel Echo)

## Realtime

* Laravel Reverb
* Laravel Echo
* WebSockets

---

# Installation

Clone the repository:

```bash
git clone https://github.com/shafi-rahman/chatway_poll_management.git
cd chatway_poll_management
```

Install dependencies:

```bash
composer install
npm install
```

Copy the environment file:

```bash
cp .env.example .env
```

Update the `.env` file with your database credentials and application configuration.

Generate the application key:

```bash
php artisan key:generate
```

---

# Database Setup

Update the database credentials inside `.env`.

Run migrations:

```bash
php artisan migrate
```

---

# Running the Application (Local Development)

Start the Laravel development server:

```bash
php artisan serve
```

Start Vite for frontend assets:

```bash
npm run dev
```

---

# Building Assets for Production

For production environments, compile frontend assets using:

```bash
npm run build
```

---

# Running WebSockets (Realtime)

This project uses **Laravel Reverb** for real-time broadcasting.

Start the WebSocket server:

```bash
php artisan reverb:start
```

Once the server is running, the application will broadcast vote updates to connected clients.

---

# Testing Real-Time Updates

To verify real-time functionality:

1. Open the **admin poll results page** in one browser tab.
2. Open the **public poll page** in another browser or incognito window.
3. Submit a vote from the public poll page.
4. The results on the admin page should update instantly without refreshing.

---

# Running Tests

Run the test suite:

```bash
php artisan test
```

Tests currently cover:

* poll creation
* vote submission
* duplicate vote prevention

---

# Project Structure Overview

Key parts of the application:

```
app/
 ├── Events/
 │   └── PollVoteUpdated.php
 ├── Models/
 │   ├── Poll.php
 │   ├── PollOption.php
 │   └── Vote.php
 ├── Http/Controllers/
 │   ├── Admin/PollController.php
 │   └── PublicPollController.php

resources/views/
 ├── admin/polls/
 │   ├── create.blade.php
 │   ├── edit.blade.php
 │   ├── index.blade.php
 │   └── results.blade.php
 └── polls/
     └── show.blade.php
```

---

# Key Design Decisions

## Laravel Breeze Authentication

Laravel Breeze was used to provide a lightweight authentication system for admin users.
It provides login, registration, and profile management while keeping the application simple and maintainable.

## Poll UUIDs

Polls use UUIDs instead of numeric IDs to avoid predictable URLs and make public poll links safer to share.

## Vote Protection

To prevent duplicate voting, the system uses a combination of:

* IP address
* session token

This helps ensure each visitor can vote only once per poll.

## Editing Restrictions

Polls cannot be edited after votes have been recorded to protect the integrity of the results.

## Real-Time Results

Each vote triggers a broadcast event that updates:

* the admin results page
* the public poll results page

in real time without requiring a page refresh.
