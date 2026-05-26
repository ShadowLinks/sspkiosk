Cursor has already built the Laravel SSP Kiosk system using a temporary password flow.

Do not rebuild the application from scratch.

Create a focused patch to fix the temporary password workflow and add configurable password reset modes.

Current issue:
The current temporary password flow generates the temporary password only after Slack approval and displays it only on the originating kiosk session. That requires the student to wait at the kiosk until a tech approves the request.

That does not work operationally.

New required behavior:
The student must be able to complete the kiosk request and leave. The password they will use later must be given to them before Slack approval. It must not become active in Google unless the tech approves the request.

The app must support two password reset modes:

1. temporary_generated
2. student_selected_pending_approval

The active mode must be controlled by .env.

Add to .env.example:

RESET_PASSWORD_MODE=temporary_generated
# allowed: temporary_generated, student_selected_pending_approval

GOOGLE_FORCE_CHANGE_AT_NEXT_LOGIN_TEMPORARY=true
GOOGLE_FORCE_CHANGE_AT_NEXT_LOGIN_STUDENT_SELECTED=false

PENDING_PASSWORD_ENCRYPTION_ENABLED=true
DELETE_PENDING_PASSWORD_ON_APPROVAL=true
DELETE_PENDING_PASSWORD_ON_DENIAL=true
DELETE_PENDING_PASSWORD_ON_EXPIRATION=true
DELETE_PENDING_PASSWORD_ON_GOOGLE_FAILURE=true
RETAIN_PENDING_PASSWORD_ON_GOOGLE_FAILURE=false

PENDING_PASSWORD_DISPLAY_SECONDS=90
PENDING_PASSWORD_COPY_NOTICE_ENABLED=true

PASSWORD_MIN_LENGTH=12
PASSWORD_REQUIRE_UPPERCASE=true
PASSWORD_REQUIRE_LOWERCASE=true
PASSWORD_REQUIRE_NUMBER=true
PASSWORD_REQUIRE_SYMBOL=false
PASSWORD_PREVENT_EMAIL_PARTS=true
PASSWORD_PREVENT_NAME_PARTS=true

Configuration rules:
1. Do not hardcode these values.
2. Add them to .env.example.
3. Read them through a Laravel config file, preferably config/student-password-reset.php.
4. Application code must use config('student-password-reset...'), not env() directly outside config files.
5. If RESET_PASSWORD_MODE is missing or invalid, fail closed and block reset requests.

Database changes:
Add a migration to update password_reset_requests with these nullable fields:

- reset_mode string nullable
- encrypted_pending_password text nullable
- pending_password_created_at timestamp nullable
- pending_password_displayed_at timestamp nullable
- pending_password_deleted_at timestamp nullable
- pending_password_expires_at timestamp nullable
- pending_password_type string nullable
  # allowed: temporary_generated, student_selected

Each reset request must store the reset_mode used at the time the request was created.

Important concept:
In both reset modes, the password is pending and inactive until Slack approval.

Mode 1: temporary_generated

If RESET_PASSWORD_MODE=temporary_generated:

1. Student completes the kiosk identity flow:
   - lookup
   - confirmation
   - photo
   - challenge questions
2. If the challenge answers are correct, create a pending password reset request.
3. Immediately generate a temporary password.
4. Encrypt the temporary password before saving it.
5. Store the encrypted temporary password as encrypted_pending_password.
6. Display the temporary password to the student immediately on the kiosk.
7. Make it clear that the password will only work if the request is approved.

Student-facing message:

“Write down this temporary password. It will not work yet. If technology staff approve your request, this will become your temporary Google password. You will be required to change it when you sign in.”

8. Display it only once for the configured timeout.
9. After display timeout, do not show it again.
10. Send the Slack approval request to tech staff.
11. Student does not need to remain at the kiosk.

When tech approves:
1. Verify Slack signature.
2. Verify tech authorization.
3. Verify request is pending.
4. Verify request has not expired.
5. Load encrypted_pending_password.
6. Decrypt it inside the reset job.
7. Send it to Google Workspace using the Admin SDK Directory API.
8. Set changePasswordAtNextLogin=true by default for temporary_generated mode.
9. Immediately set encrypted_pending_password=null.
10. Set pending_password_deleted_at.
11. Mark the request completed.
12. Update Slack message.

If tech denies:
1. Set status denied.
2. Delete encrypted_pending_password.
3. Set pending_password_deleted_at.
4. Update Slack message.

If request expires:
1. Set status expired.
2. Delete encrypted_pending_password.
3. Set pending_password_deleted_at.
4. Audit expiration without logging secrets.

If Google reset fails:
1. Default behavior is to delete encrypted_pending_password.
2. Do not retain it unless RETAIN_PENDING_PASSWORD_ON_GOOGLE_FAILURE=true.
3. Default must be false.

Mode 2: student_selected_pending_approval

If RESET_PASSWORD_MODE=student_selected_pending_approval:

1. Student completes the kiosk identity flow.
2. Student enters and confirms the new password they want.
3. Validate the password server-side using configured password rules.
4. Client-side validation can be used for usability, but server-side validation is required.
5. Encrypt the student-selected password before saving it.
6. Store it as encrypted_pending_password.
7. Send Slack approval request.
8. Show student this message:

“Your request has been submitted. If approved, your Google password will be changed to the password you entered.”

9. Student does not need to remain at the kiosk.

When tech approves:
1. Load encrypted_pending_password.
2. Decrypt it inside the reset job.
3. Send it to Google Workspace as the new student password.
4. Set changePasswordAtNextLogin=false by default for student_selected_pending_approval mode.
5. Immediately delete encrypted_pending_password.
6. Set pending_password_deleted_at.
7. Mark request completed.

Security requirements for both modes:

1. Pending passwords must never be stored in plain text.
2. Pending passwords must never be logged.
3. Pending passwords must never be sent to Slack.
4. Pending passwords must never be shown to tech staff.
5. Pending passwords must never be shown in the admin dashboard.
6. Pending passwords must never be written to audit logs.
7. Pending passwords must never be included in queued job payloads.
8. Pending passwords must never be included in exception messages.
9. Pending passwords must never be included in validation error messages.
10. Pending passwords must never be included in request dumps or debug output.
11. The reset job should receive only the password_reset_request ID.
12. The reset job should load and decrypt the password inside the job.
13. The reset job must immediately delete the encrypted_pending_password after use or failure according to config.

Slack message changes:

For temporary_generated mode, Slack message should say:

“System generated a temporary password and showed it to the student. It is encrypted and will only become active if this request is approved. The password is not shown in Slack.”

For student_selected_pending_approval mode, Slack message should say:

“Student selected a new password at the kiosk. It is encrypted and will only be sent to Google if approved. The password is not shown in Slack.”

The Slack message must never contain the actual password.

Status changes:
Review current statuses and add or adapt as needed:

- pending
- approved_processing
- completed
- denied
- needs_office_verification
- expired
- failed

Important behavior change:
Do not require the student to stay on the kiosk waiting page in either mode.

After request submission:
- In temporary_generated mode, show the pending temp password once, then end the kiosk session.
- In student_selected_pending_approval mode, show a confirmation message, then end the kiosk session.

Remove or modify the old behavior where the kiosk waits for Slack approval and then displays the temp password after approval.

Admin dashboard:
Update reset request detail page to show:
- reset mode
- pending password type
- whether encrypted pending password exists: yes/no
- pending password created timestamp
- pending password displayed timestamp
- pending password expiration timestamp
- pending password deleted timestamp

Do not show the encrypted password.
Do not show the decrypted password.
Do not add a reveal button.

Tests:
Add or update tests for:

1. temporary_generated mode generates a pending temp password at request time.
2. temporary_generated mode displays the pending temp password before approval.
3. temporary_generated mode does not send password to Google until Slack approval.
4. temporary_generated mode deletes encrypted pending password after approval.
5. temporary_generated mode deletes encrypted pending password after denial.
6. temporary_generated mode deletes encrypted pending password after expiration.
7. student_selected_pending_approval mode requires password and confirmation.
8. student_selected_pending_approval mode encrypts selected password.
9. selected password is deleted after approval.
10. Slack message does not contain either type of password.
11. audit logs do not contain either type of password.
12. queued job payload does not contain either type of password.
13. invalid RESET_PASSWORD_MODE fails closed.
14. Google force-change behavior differs correctly by mode:
    - temporary_generated defaults true
    - student_selected_pending_approval defaults false

Do not weaken kiosk validation.
Do not weaken Slack approval verification.
Do not allow any direct unauthenticated Google password reset route.
Do not rebuild the app.
Before coding, inspect the existing Laravel files and list the exact files you will modify. Then implement only this patch.