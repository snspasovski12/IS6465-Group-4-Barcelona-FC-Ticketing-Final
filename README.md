
# FC Barcelona Ticketing System (IS 6465, Group 4)

Mid-term project, **CODING** phase: the Home page, the Login page, and two CRUD sets
(User Management and Match Management), coded in HTML/CSS with Bootstrap 5.3 and saved as PHP pages.

This is a PHP session-based prototype. There is no database integration yet.
Sample User and Match records are initialized in `includes/init.php` and stored
in PHP sessions. Create, Update, and Delete operations modify this session data,
allowing changes to appear throughout the application.

The final project will replace session storage with database operations.
Authentication is simulated for the midterm.

## Run it

In a Codespace (or any computer with PHP 8):

```bash
php -S 0.0.0.0:8000
```

Then open port 8000 and start at `index.php`.

**Demo login:** any username with a password of at least 8 characters signs in as a fan.
Use the username `admin` (any 8+ character password) to see the administrator pages.
A shorter password shows the "Invalid username/email or password" message.

## Pages

| Wireframe | Page | Use case | Who |
|---|---|---|---|
| WF01 Home | `index.php` | Home (upcoming matches) | Everyone |
| WF02 Login | `login.php` | Sign in | Everyone |
| WF03 Create User Account | `create-user.php` | UC 01 Create User Account | Fan (Register Here) or admin (`?from=admin`) |
| WF04 My Account | `my-account.php` | UC 02 View User Account, UC 04 Delete (button + confirmation) | Fan |
| WF04A Manage Users | `manage-users.php` | UC 02 View, UC 04 Delete (Edit and Delete per row) | Admin |
| WF05 Update User Account | `update-user.php?user_id=1` | UC 03 Update User Account | Fan or admin (`&from=admin`) |
| WF06 Create Match | `create-match.php` | UC 05 Create Match | Admin |
| WF07 View Matches | `matches.php` | UC 06 View Match | Everyone |
| WF07A Manage Matches | `manage-matches.php` | UC 06 View, UC 08 Delete (Edit and Delete per row) | Admin |
| WF08 Update Match | `update-match.php?match_id=1` | UC 07 Update Match | Admin |
| | `logout.php` | Ends the session, returns to Home | Signed in |

## Navigation (from the wireframes)

- **Login:** a fan goes to Home; an admin goes to Manage Matches. Register Here opens Create User Account.
- **Create User Account:** Create Account goes to Login (an admin returns to Manage Users). Cancel goes back without saving.
- **Update User Account:** a fan returns to My Account; an admin returns to Manage Users. Cancel saves nothing.
- **Delete:** every Delete opens a confirmation prompt. Cancel Delete stays on the page; Confirm Delete returns to the updated list when deletion is permitted.
- **Account deletion restriction:** for the midterm demonstration, User ID 1 cannot be deleted because it is treated as having associated tickets or transactions. A blocked deletion displays an error message and preserves the account.
- **Create Match / Update Match:** both return to Manage Matches after successful submission. Cancel saves nothing.
- **Delete Match:** an administrator can remove a match after confirmation, regardless of whether customers have purchased tickets.
- The navigation bar changes with the role: guest (Home, Matches, Login), fan (adds My Account, Logout), admin (adds Manage Users, Manage Matches, Logout).

## CRUD Functionality

**User Management (UC 01–04)**

- Create user accounts and display them in Manage Users.
- View existing user account information.
- Update account information and display the changes.
- Delete user accounts after confirmation.
- Prevent duplicate usernames and email addresses during account creation.
- Validate required fields and email formats.
- Simulate blocked deletion for User ID 1 because of associated tickets or transactions.
- Display success or error messages based on the operation.

**Match Management (UC 05–08)**

- Create matches and display them in the match list.
- View upcoming matches.
- Update existing match information and display the changes.
- Delete matches after administrator confirmation.
- Validate required fields, dates, times, and match types.
- Prevent creating or updating a match with a past date.
- Restrict Match Type to League or Cup.
- Prevent multiple matches from having the same date and time.
- Display success or error messages based on the operation.

All CRUD changes use PHP session storage rather than a database.
Changes are temporary and may reset when the session ends.

## Files

```
index.php, login.php, logout.php            Home, Login, Logout
create-user.php, my-account.php,
manage-users.php, update-user.php           User Management CRUD (UC 01-04)
create-match.php, matches.php,
manage-matches.php, update-match.php        Match Management CRUD (UC 05-08)
includes/init.php          time zone, sessions, helper functions, sample data
includes/header.php        <head>, Bootstrap, stylesheet, navigation
includes/nav.php           role-based navigation bar
includes/footer.php        footer and Bootstrap JavaScript
includes/messages.php      success and error messages (?msg=...) from an allow-list
includes/delete-modal.php  shared delete confirmation prompt
includes/match-table.php   match list used by Matches and Manage Matches
includes/match-form.php    fields shared by Create Match and Update Match
css/styles.css             club colors and layout
js/app.js                  fills the delete prompt with the selected record
```

## Notes

- Forms use POST and then redirect after successful operations (Post/Redirect/Get), so refreshing the destination page does not resubmit the form.
- Delete is a POST from the confirmation prompt, never a GET link.
- Values printed in HTML go through `e()` (`htmlspecialchars`), and record IDs in URLs are read using `filter_input(..., FILTER_VALIDATE_INT)`.
- Browser validation and selected PHP server-side validation are implemented, including required fields, email formats, duplicate account checks, match dates, match types, and scheduling conflicts.
- User and Match CRUD changes are stored in PHP sessions and are not permanent.
- User ID 1 has simulated ticket or transaction history for demonstrating the account deletion restriction.
- Administrators can delete matches without a simulated restriction based on ticket purchases.
- Login authentication is simulated. Newly registered accounts are not authenticated against stored passwords.
- Server-side authorization, CSRF protection, permanent database storage, and full ticket/transaction relationships remain future work.
- The final project will replace the demonstration data and restrictions with database-backed functionality.

## Testing

The primary User Management and Match Management CRUD workflows were manually tested against the approved wireframes.

**User Management testing:**
- Successful and invalid demonstration login.
- User account creation and display in Manage Users.
- User account updates and display of changed information.
- Successful administrator account deletion.
- Blocked deletion for an account with simulated ticket or transaction history.
- Delete confirmation and cancellation.
- Browser-side invalid email validation.

**Match Management testing:**
- Create Match and display the new match in Manage Matches.
- View existing matches.
- Update Match and display the changed information.
- Delete Match and remove the selected match from the list.
- Delete confirmation and cancellation.

Additional validation testing, authorization checks, and security testing remain before the final project.
