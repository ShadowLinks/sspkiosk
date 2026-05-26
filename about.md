# About SSP Kiosk

SSP Kiosk is a web application for K–12 schools that helps students recover a forgotten Google Workspace password without handing control to the student or exposing passwords in chat. Students use a district-managed kiosk; technology staff approve each request in Slack; only then does the system push the new password to Google.

The project is built on [Laravel](https://laravel.com), so installation follows the familiar pattern: clone the repo, configure `.env`, run migrations, and start a queue worker. Detailed guides live in [docs/SETUP.md](docs/SETUP.md) and [docs/INSTALL-UBUNTU.md](docs/INSTALL-UBUNTU.md) when you are ready to deploy.

---

## The problem it solves

Students forget passwords. Letting them reset Google passwords on their own is risky. Asking them to wait at a kiosk until someone approves in Slack does not work in real schools—students have class to get to.

SSP Kiosk separates three ideas that are easy to confuse:

1. **Identity** — Prove you are the right student (lookup, photo, challenge questions).
2. **Intent** — Record that you want a reset and capture the password that *would* be applied.
3. **Authorization** — A human in your tech team approves before anything changes in Google.

Until approval, the password exists only as encrypted data in the application. It does not work for Google sign-in.

---

## Two parts of the system

### Registration (while the student can still sign in)

A student signs in with Google once (when they still know their password) and completes registration:

- Challenge questions (answers stored hashed, never in plain text)
- A registration photo
- Optional requirement to use a registered kiosk for registration

After registration, the student is eligible for kiosk-assisted resets later.

### Password reset (when the student cannot sign in)

At a district kiosk, the student:

1. Enters email or student ID  
2. Confirms identity (name, photo)  
3. Takes a reset-request photo  
4. Answers random challenge questions from registration  

If verification succeeds, the app creates a **pending** reset request and notifies staff in Slack. The student can leave the kiosk—they are not stuck on a “waiting for approval” screen.

---

## How approval and Google reset work

When a reset request is created, Slack receives a rich message: student and kiosk details, photos, challenge results, risk flags, and **Approve**, **Deny**, or **Needs office verification** actions. Only members of a configured Slack user group can approve.

**Passwords never appear in Slack.** Messages only explain that an encrypted pending password is waiting.

After approval:

1. A background job loads the request by ID (the password is not in the job payload).
2. The job decrypts the pending password inside the server.
3. Google Workspace Directory API sets the student’s password.
4. The encrypted copy is deleted according to policy.
5. The request is marked completed and the Slack thread is updated.

If staff deny the request, or the request expires, or Google reset fails, the pending password is removed so it cannot be applied later.

There is no public URL that resets Google passwords. The only path is: validated kiosk → pending encrypted password → Slack approval → queued Google update.

---

## Password reset modes

The active mode is set with `RESET_PASSWORD_MODE` in `.env`. Both modes store the password as **pending and inactive** until Slack approval.

### `temporary_generated` (default)

After correct challenge answers, the system:

- Generates a temporary password (readable format, configurable word list)
- Encrypts and saves it on the reset request
- Shows it **once** on the kiosk for a limited time
- Tells the student it will not work until staff approve
- Queues the Slack approval message

By default, Google is configured to require a password change at next sign-in for this mode.

### `student_selected_pending_approval`

After correct challenge answers, the student:

- Enters and confirms a new password on the kiosk
- Passes server-side password policy checks
- Sees a confirmation that the request was submitted (the password is not shown again)
- Triggers Slack approval

By default, Google does **not** force an immediate change-at-login for this mode (configurable).

---

## What staff see in the admin dashboard

Administrators can manage kiosks, view students, browse reset requests, read audit logs, and review failed attempts. Request detail pages show **metadata only** for pending passwords: reset mode, whether an encrypted password exists, and timestamps (created, displayed, expired, deleted). There is no reveal button and no decrypted password in the UI.

---

## Security principles

- Pending passwords are encrypted at rest; plain text is not stored in the database, logs, audit trail, Slack, or admin screens.
- Challenge answers are hashed like passwords.
- Kiosk API calls use HMAC-signed requests, optional IP allowlists, and heartbeat checks.
- Slack interactions are signature-verified; approvers are restricted to a user group.
- Rate limits and lockouts reduce brute-force attempts at lookup and challenge steps.
- Sensitive log lines are redacted.

---

## Configurable options (overview)

Most behavior is controlled through `.env` without code changes. Grouped by topic:

| Area | What you can tune |
|------|-------------------|
| **Reset mode** | `temporary_generated` vs `student_selected_pending_approval` |
| **Google after reset** | Force password change at next login (separate defaults per mode) |
| **Pending password** | Encryption on/off, display duration, copy notice, when encrypted data is deleted (approval, denial, expiration, Google failure) |
| **Student password policy** | Length, uppercase/lowercase/number/symbol rules, block email or name fragments (student-selected mode) |
| **Challenge questions** | How many asked, how many must be correct, min/max stored per student, case sensitivity |
| **Request lifetime** | Minutes until a pending request expires |
| **Lockouts** | Max failed attempts per student or kiosk, lockout duration |
| **Photos** | Retention days, storage disk, max upload size |
| **Slack** | Approval required or not, office-verification path, channel and approver group |
| **Kiosk** | Allowed networks, heartbeat requirement, whether reset must use a kiosk |
| **Registration** | Whether registration requires a kiosk, registration enabled/disabled |
| **Google identity** | Student domain, allowed org units, blocked staff org units |
| **Messaging** | Student-facing notices for reset, challenge failure, pending temp password, submission confirmation |
| **Audit** | Enable/disable, retention days |
| **Admin** | Route prefix, allowed admin emails |

Invalid or missing `RESET_PASSWORD_MODE` causes the kiosk reset flow to fail closed until configuration is fixed.

---

## Who this is for

- **Students** — Self-service at a trusted kiosk without waiting for a technician at the machine.  
- **Technology staff** — Approve or deny from Slack with context (photos, risk signals).  
- **Administrators** — Enroll kiosks, monitor requests, and audit activity.

SSP Kiosk is designed for districts that already use Google Workspace for students and want a controlled, auditable bridge between “I forgot my password” and “Google account updated.”
