# jumpseat - User Manual

This manual explains how to use the `jumpseat` web application.

## 1) Purpose

`jumpseat` helps pilots share planned flights and lets other users express interest in available seats.

## 2) Access and language

- Open the app in your browser
- Use language buttons in the top bar:
  - `EN` for English
  - `FR` for French

## 3) Account management

### Register

1. Click `Register`.
2. Fill:
   - Username (required, unique)
   - Email (required, unique)
   - Password (required, minimum 8 characters)
   - Photo (optional)
   - Phone (optional)
   - Notify me on new flights (enabled by default)
3. Submit the form.

### Login / Logout

- Login: click `Login`, enter email + password.
- Logout: click `Logout` in top bar.

### Forgot / Reset password

1. On login page click `Forgot password`.
2. Enter your email.
3. Use the reset link from received email
4. Set a new password.

### Profile

Open `Profile` to:
- view username/email
- change photo
- update phone
- set up to 3 aircraft models
- change password
- enable/disable new-flight notifications
- deregister account

## 4) Flight creation and editing

### Create flight

1. Click `New flight` from top bar, or click an empty day cell in calendar.
2. Fill required fields:
   - Flight title
   - Origin
   - Destination
   - Aircraft (dropdown from profile aircraft models)
   - Planned departure date/time (local)
   - Planned return date/time (local)
   - Planned flight duration (minutes, step 15)
   - Number of seats available (0 to 3)
   - Additional information (optional)
3. Click `Save`.

Notes:
- Menu-based creation defaults to today with `08:00` departure and `17:00` return.
- Day-click creation pre-fills clicked date with same default times.

### Edit / Delete your flights

- In calendar event cards, use `Edit` and `Delete`.
- Or open `My flights` page and use the same actions.

## 5) Calendar usage

### Week navigation

- `Previous week` and `Next week` change week.
- `Today` returns to current week.

### Events

- Events display:
  - Title
  - Route
  - Aircraft
  - By (username)
  - Seats
- Colors are assigned per event owner.
- Flights with 0 seats are grayed out.

### Flight details modal

- Click an event to open details.
- Modal includes owner photo (if available).
- `Contact` opens interest notification dialog (enabled only if seats > 0).

## 6) Interest notifications

1. Open a flight details modal.
2. Click `Contact`.
3. Enter your message.
4. Click `Send`.

The flight owner receives an email notification.


## 7) New flight notifications

When a user creates a flight, users who opted in receive a notification email with flight details.

## 8) Disclaimer

- Use the `Disclaimer` button at page bottom.


## 9) Administrator features

Users with admin privileges can open `Admin` and:
- list users
- block/unblock users
- delete non-admin users
- reset a user's password

