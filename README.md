# FC Barcelona Ticketing System
## IS 6465 Web Applications — Group 4 Final Project

### Project Overview

The FC Barcelona Ticketing System is a PHP and SQLite web
application developed for IS 6465 Web Applications.

The application allows fans to register, manage their accounts,
view scheduled matches, reserve seats, and complete simulated
ticket purchases.

Administrators manage user accounts, matches, seats, tickets,
and transactions.

### Technologies

- PHP 8
- SQLite
- HTML5
- CSS3
- Bootstrap 5.3
- JavaScript
- GitHub Codespaces

### Database

The application uses five related SQLite tables:

1. users
2. matches
3. seats
4. tickets
5. transactions

The database schema is defined in:

database/schema.sql

The SQLite database file is excluded from Git using .gitignore.

### Installation and Setup

Open the project in GitHub Codespaces.

Initialize the database:

```bash
php scripts/setup.php
```

Load demonstration data:

```bash
php scripts/seed.php
```

Start the PHP development server:

```bash
php -S 0.0.0.0:8000
```

Open port 8000 in Codespaces and navigate to index.php.

The setup script will not overwrite an existing database.
The seed script skips seeding when users already exist.

### Demonstration Accounts

Administrator:
- Username: admin
- Password: AdminDemo2026!

Fan:
- Username: fan
- Password: FanDemo2026!

Additional seeded fan accounts:
- sspasovski
- cosmond
- mahmed

These additional fan accounts use the same seeded fan password.

Passwords are stored using PHP password hashing.

These credentials are intended for local demonstration only.
Change them before any public deployment.

### User Management — UC01–UC04

- Create user accounts
- View account information
- Update account details
- Delete or deactivate accounts
- Prevent duplicate usernames and emails
- Restrict access to other users' accounts
- Preserve accounts with ticket or transaction history

### Match Management — UC05–UC08

- Create matches
- View scheduled matches
- Update match details
- Delete matches when permitted
- Validate match dates, times, and statuses
- Restrict match management to administrators

### Seat Management — UC09–UC12

- Create seats
- View and filter seats
- Update seat information and status
- Delete or deactivate seats when permitted
- Prevent duplicate Section, Row, and Number combinations
- Reject invalid row, seat number, and price values

### Ticket Management — UC13–UC16

- Reserve available seats
- View tickets
- Update permitted ticket statuses
- Cancel Pending tickets
- Preserve historical ticket records
- Prevent duplicate active reservations
- Automatically expire Pending reservations after 15 minutes

Fans can view only their own tickets.
Administrators can manage ticket records subject to
ticket-status and transaction-history restrictions.

### Transaction Management — UC17–UC20

- Create simulated payment transactions
- View transaction history
- Filter transactions by match, status, and date
- Void Completed transactions through administrator controls
- Soft-delete transaction records using DeletedAt
- Preserve transaction history

No real payment information is collected.

### Interactive Workflows

#### Fan Ticket Reservation

1. Log in as a fan.
2. Open Available Matches.
3. Select a Scheduled match.
4. Choose an available seat.
5. Reserve the seat.
6. Complete simulated checkout within 15 minutes.
7. View the Purchased ticket under My Tickets.

#### Administrator-Created Reservation

1. Log in as an administrator.
2. Open Manage Tickets.
3. Select Add Ticket.
4. Choose a Scheduled match.
5. Assign the ticket to an active fan.
6. Select an available seat.
7. Reserve the seat.

The administrator returns to Manage Tickets.

The assigned fan can open My Tickets and select Checkout
before the reservation expires.

#### Transaction Void

1. Open Manage Transactions as an administrator.
2. Select a Completed transaction.
3. Open Update Transaction.
4. Select Void Transaction.
5. Confirm the update.

The transaction changes to Voided and the associated
Purchased ticket changes to Cancelled.

Both records remain in the database.

### Security and Validation

The application implements:

- Session-based authentication
- Password hashing and verification
- Role-based authorization
- Account ownership restrictions
- Ticket ownership restrictions
- CSRF protection for state-changing forms
- Server-side input validation
- SQLite foreign-key enforcement
- Duplicate seat and reservation protection
- Historical record preservation

### Wireframe References

- WF01 — Home
- WF02 — Login
- WF03 — Create User Account
- WF04 — My Account
- WF04A — Manage Users (Admin)
- WF05 — Update User Account
- WF06 — Create Match
- WF07 — Available Matches (User)
- WF07A — Manage Matches (Admin)
- WF08 — Update Match
- WF09 — Create Seat
- WF10 — Manage Seats
- WF11 — Update Seat
- WF12 — Reserve Seat (User)
- WF12A — Reserve Seat (Admin)
- WF13 — My Tickets (User) / Manage Tickets (Admin)
- WF14 — Update Ticket
- WF15 — Create Transaction / Checkout
- WF16 — Manage Transactions
- WF17 — Update Transaction

### Testing

Database verification:

```bash
php scripts/test-database.php
```

Ticketing prerequisites:

```bash
php scripts/test-ticketing.php
```

The ticketing script checks prerequisites and foreign-key
configuration; it is not a complete automated checkout test.

Manual testing included:

- User, Match, and Seat CRUD operations
- Ticket reservations and simulated payments
- Failed and successful payment outcomes
- Administrator transaction voiding
- Ticket cancellation
- Reservation expiration and seat release
- Transaction soft deletion
- Duplicate seat and reservation prevention
- Invalid seat number and negative price validation
- Unauthorized account and checkout access
- Inactive account login rejection
- Database foreign-key integrity

### Project Notes

This application is an academic demonstration project.

Payments are simulated, and no real payment card
information is collected.

The SQLite database file is intentionally excluded from Git.
New installations must run the setup and seed scripts.

The PHP development server is intended for local testing,
not production deployment.
