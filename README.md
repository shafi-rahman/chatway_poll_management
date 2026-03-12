<div align="center"><img src="https://files-cdn.chatway.app/assets/images/logo-text.svg" width="400" alt="Chatway Logo"></div>

# Chatway Poll Management
This project is a Poll Management System built as part of a technical assignment for Chatway.

The goal of this project was to build a simple but scalable polling platform where admins can create polls and users can vote through a public link. The system also demonstrates real-time vote updates using Laravel broadcasting.

The application focuses on clean backend structure, proper validation, and a straightforward user experience.


### Features

# Admin Features
- Admin registration and authentication
- Admin profile management
- Create polls with multiple options
- Edit polls before votes are recorded
- Poll scheduling (start and end time)
- Activate or deactivate polls
- View poll results
- Share public poll links

# Public Poll Features
- Public poll page accessible via shareable link
- Users can vote without authentication
- Each user/IP can vote only once
- Poll availability based on:
  - active/inactive status
  - start time
  - end time
- Poll results displayed on the poll page

# Real-Time Updates
- Vote results update instantly using Laravel broadcasting
- Admin results page updates live
- Public poll results update live
- No page refresh required

### Tech Stack
Backend:
- Laravel
- MySQL
- Laravel Broadcasting
- Laravel Reverb (WebSocket server)

Authentication:
- Laravel Breeze (for admin authentication and profile management)

Frontend:
- Blade
- TailwindCSS
- JavaScript (Fetch API + Laravel Echo)

Realtime:
- Laravel Reverb
- Laravel Echo
- WebSockets

### Installation

Clone the repository:
git clone https://github.com/your-repo/chatway-poll-management.git
cd chatway-poll-management

Install dependencies:
composer install
npm install

Copy environment file:
cp .env.example .env
*updated the .env.example same as .env on local

Generate application key:
php artisan key:generate

### Database Setup

Update your `.env` database configuration.

Run migrations:
php artisan migrate

### Running the Application

Start the Laravel server:
php artisan serve

Start Vite for frontend assets:
npm run dev

### Running WebSockets (Realtime)

This project uses Laravel Reverb for real-time broadcasting.

Start the WebSocket server:
php artisan reverb:start

Now the application will broadcast vote updates to connected clients.

### Running Tests

Run the test suite with:
php artisan test

Tests cover:
- poll creation
- vote submission
- duplicate vote prevention

### Project Structure Overview

Important components:
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

### Key Design Decisions

# Laravel Breeze Authentication
Laravel Breeze was used to provide a lightweight authentication system for admin users.
It provides login, registration, and profile management out of the box while keeping the application structure simple and maintainable.

# Poll UUIDs
Polls use UUIDs instead of numeric IDs.
This prevents predictable URLs and makes public poll links safer to share.

# Vote Protection
To prevent duplicate voting, the system uses a combination of:
- IP address
- session token

This helps ensure each visitor can only vote once per poll.

# Editing Restrictions
Polls cannot be edited after votes have been recorded.
This protects the integrity of the poll results.

# Real-Time Results
Each vote triggers a broadcast event which updates:
- admin results page
- public poll results

in real time without requiring a page refresh.

