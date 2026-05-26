You are working inside an existing clean Laravel application.



Build a secure K-12 student password reset request system using Laravel.



The system will allow students to register for password reset while they can still authenticate with their Google Workspace student account. Later, if they forget their password, they can use a district-owned kiosk to request a password reset. The request is sent to a Slack channel where a technology staff member can review the request, compare pictures, review challenge question results, and click Approve, Deny, or Needs Office Verification. Only after an authorized tech approves the Slack request should the system reset the student’s Google Workspace password.



Do not build this as a fully automated password reset based only on security questions. The challenge questions are supporting evidence only. The final reset decision must require Slack approval by an authorized technology staff member.



Technology stack:

\- Existing clean Laravel install

\- MySQL 

\- Laravel migrations, models, controllers, services, jobs, middleware, policies where appropriate

\- Laravel queues for Slack notifications and Google password reset jobs

\- Blade is acceptable for UI unless the existing Laravel install already has React/Vite configured

\- Use .env for every secret and configurable value

\- Do not hardcode secrets, tokens, channel IDs, domain names, Google credentials, or Slack values



Core integrations:

1\. Google Sign-In / OpenID Connect for student registration authentication.

2\. Google Workspace Admin SDK Directory API to reset approved student passwords.

3\. Slack Block Kit interactive messages for Approve, Deny, and Needs Office Verification.

4\. Slack request signing verification for Slack callbacks.

5\. Kiosk device validation so someone cannot simply copy the URL and use it from another device.



Important official references:

\- Google OpenID Connect: https://developers.google.com/identity/openid-connect/openid-connect

\- Google Admin SDK Directory API users resource: https://developers.google.com/workspace/admin/directory/reference/rest/v1/users

\- Google Admin SDK manage users guide: https://developers.google.com/workspace/admin/directory/v1/guides/manage-users

\- Slack Block Kit: https://docs.slack.dev/block-kit/

\- Slack request signing verification: https://docs.slack.dev/authentication/verifying-requests-from-slack



Core modules:

1\. Student registration portal

2\. Kiosk password reset request portal

3\. Slack approval workflow

4\. Google Workspace password reset integration

5\. Kiosk device validation

6\. Admin dashboard

7\. Audit logging



Important security principle:

There must be no route where a student, kiosk, unauthenticated user, or normal web user can directly reset a Google password.



The only valid reset path is:



Validated kiosk request

→ pending reset request

→ Slack approval by authorized tech

→ backend Google Workspace reset job

→ forced password change at next Google login



Application behavior:



A. First-time student registration



The student must authenticate using Google Sign-In / OpenID Connect.



The system must:



1\. Redirect the student to Google login.

2\. Validate the returned Google identity token server-side.

3\. Only allow accounts from the configured student Google Workspace domain.

4\. Reject staff/admin accounts and accounts outside the allowed student OU/domain configuration.

5\. Create or update a local student registration record.

6\. Ask the student to create up to 10 challenge questions and answers.

7\. Store challenge answers securely using a slow hash. Never store answers in plain text.

8\. Capture a registration photo from the kiosk/webcam.

9\. Store the registration photo securely outside the public web root.

10\. Record registration metadata:

&#x20;  - student email

&#x20;  - Google user ID/sub

&#x20;  - name

&#x20;  - school if available

&#x20;  - grade if available

&#x20;  - registration kiosk/device ID if registration happens on a kiosk

&#x20;  - IP address

&#x20;  - user agent

&#x20;  - timestamp

11\. Mark the student as registered for kiosk password reset.



Registration must include this notice:



“This system is used to request password assistance. Your photo may be captured and reviewed by school technology staff to verify your identity and protect your account. Misuse of this system may result in disciplinary action.”



B. Kiosk password reset request



The reset page must only work from validated district kiosks. Do not trust the URL alone.



Student flow:



1\. Student enters their student email or student ID.

2\. System checks whether the student is registered.

3\. If not registered, show a safe generic message that does not help account enumeration.

4\. If registered, show limited confirmation, such as first name and last initial.

5\. Display a notice that a photo will be taken and sent to technology staff for review.

6\. Capture a reset request photo.

7\. Randomly select 3 of the student’s registered challenge questions.

8\. Collect answers.

9\. Compare answers against stored hashes.

10\. Do not tell the student which answers were wrong.

11\. Create a pending password reset request.

12\. Send a Slack approval message to the configured technology channel.



The kiosk must not directly reset the password.



C. Slack approval message



Send a Slack Block Kit message to the configured channel.



The Slack message should include:



\- Student name

\- Student email

\- School/grade if available

\- Kiosk name/location

\- Request timestamp

\- Request IP/device ID

\- Registration photo

\- Current reset request photo

\- Challenge result, such as “3 of 3 matched”

\- Number of failed attempts today

\- Last password reset date

\- Risk flags if any



Buttons:



1\. Approve Reset

2\. Deny Request

3\. Needs Office Verification



Do not post the temporary password in a public Slack channel.



D. Slack callback security



For every Slack interactive callback:



1\. Verify the Slack request signature using the Slack signing secret.

2\. Reject invalid signatures.

3\. Reject replayed requests or timestamps outside the allowed tolerance.

4\. Verify the Slack user is authorized to approve password resets.

5\. Verify the request is still pending.

6\. Verify the request has not expired.

7\. Verify the request has not already been approved, denied, or escalated.

8\. Record the approving/denying/escalating Slack user ID and timestamp.

9\. Make all Slack decisions idempotent.



E. Google Workspace password reset



Only after an authorized Slack user clicks Approve Reset:



1\. Generate a temporary password server-side.

2\. Password must meet the configured Google Workspace password policy.

3\. Use the Google Workspace Admin SDK Directory API to update the student user password.

4\. Set changePasswordAtNextLogin to true.

5\. Record the Google API result.

6\. Mark the request as approved.

7\. Update the Slack message to show:

&#x20;  - approved status

&#x20;  - approving tech

&#x20;  - timestamp

&#x20;  - no temporary password

8\. Send the temporary password to the kiosk session that originated the request, if still active.

9\. If the kiosk session is no longer active, require the student to restart or require office/tech staff handling.



The temporary password should be shown only once on the kiosk screen and should time out quickly.



Never store the temporary password in plain text.

Never log the temporary password.

Never send the temporary password to a Slack channel.



F. Kiosk validation



This is critical. Someone should not be able to copy the URL and run the reset page from another device.



Implement layered kiosk validation. Do not rely on one check.



At minimum, implement:



1\. Registered kiosk device records



Create a kiosks table with:



\- id

\- kiosk\_uuid

\- name

\- school

\- location

\- status: active, disabled

\- allowed\_ip

\- allowed\_subnet

\- secret\_hash

\- last\_seen\_at

\- created\_at

\- updated\_at



2\. Kiosk enrollment



Admin can create a kiosk enrollment record.



Create a kiosk\_enrollment\_codes table with:



\- id

\- code\_hash

\- kiosk\_id

\- expires\_at

\- used\_at

\- created\_at

\- updated\_at



Behavior:



\- Admin creates a kiosk record.

\- System generates a one-time enrollment code.

\- On first kiosk setup, kiosk submits the enrollment code.

\- Server issues a kiosk credential/secret once.

\- Store only a hashed version of the kiosk secret server-side.

\- Kiosk stores its secret locally.

\- Enrollment codes expire and can be used only once.



3\. Kiosk heartbeat



Create a heartbeat endpoint.



Kiosk periodically sends heartbeat.



Backend records:



\- kiosk ID

\- last\_seen\_at

\- IP address

\- user agent

\- device fingerprint hints if available



If a kiosk has not checked in recently, block or flag reset requests from that kiosk.



4\. IP/network allowlisting



Kiosk reset routes should only work from configured district IPs, private VLANs, or approved subnets.



If behind a reverse proxy, correctly handle trusted proxy headers.



Never trust X-Forwarded-For unless the proxy is trusted.



5\. Kiosk signed request header



Every kiosk request must include:



\- kiosk\_id

\- timestamp

\- nonce

\- HMAC signature



HMAC must be generated using the kiosk secret.



Server verifies:



\- kiosk exists

\- kiosk is active

\- timestamp is recent

\- nonce has not been used

\- signature is valid

\- source IP/subnet matches kiosk configuration



6\. Session binding



A reset request must be bound to the kiosk session that created it.



Only that kiosk session can receive/display the temporary password after approval.



7\. Admin controls



Admin can:



\- disable a kiosk

\- rotate a kiosk secret

\- view kiosk heartbeat

\- view kiosk request history



Nice-to-have stronger options to leave hooks for:



\- client certificate or mutual TLS for kiosks

\- kiosk VLAN with firewall rules allowing only this app

\- ChromeOS managed guest session or locked app mode

\- reverse proxy rule blocking kiosk routes except from approved networks

\- device certificate issued through district device management



G. Admin dashboard



Create an admin dashboard with:



1\. Pending requests

2\. Approved requests

3\. Denied requests

4\. Needs office verification requests

5\. Student registration lookup

6\. Kiosk management

7\. Audit log search

8\. Failed attempt report

9\. Ability to disable a student’s reset eligibility

10\. Ability to disable a kiosk

11\. Ability to rotate a kiosk secret



H. Database migrations



Create migrations for at least these tables:



students



\- id

\- google\_sub

\- email

\- name

\- school

\- grade

\- org\_unit\_path

\- registered\_at

\- reset\_enabled

\- created\_at

\- updated\_at



student\_challenge\_questions



\- id

\- student\_id

\- question\_text

\- answer\_hash

\- created\_at

\- updated\_at



student\_photos



\- id

\- student\_id

\- type: registration or reset\_request

\- storage\_path

\- metadata JSON

\- created\_at

\- updated\_at



kiosks



\- id

\- kiosk\_uuid

\- name

\- school

\- location

\- status

\- allowed\_ip

\- allowed\_subnet

\- secret\_hash

\- last\_seen\_at

\- created\_at

\- updated\_at



kiosk\_enrollment\_codes



\- id

\- code\_hash

\- kiosk\_id

\- expires\_at

\- used\_at

\- created\_at

\- updated\_at



password\_reset\_requests



\- id

\- student\_id

\- kiosk\_id

\- status: pending, approved, denied, needs\_office\_verification, expired, failed

\- challenge\_questions\_presented JSON

\- challenge\_score

\- reset\_photo\_id

\- slack\_channel\_id

\- slack\_message\_ts

\- requested\_at

\- expires\_at

\- approved\_by\_slack\_user\_id

\- approved\_at

\- denied\_by\_slack\_user\_id

\- denied\_at

\- denial\_reason

\- google\_reset\_attempted\_at

\- google\_reset\_success

\- google\_error\_message

\- created\_at

\- updated\_at



audit\_logs



\- id

\- actor\_type: student, tech, admin, system, kiosk

\- actor\_id

\- action

\- target\_type

\- target\_id

\- ip\_address

\- user\_agent

\- metadata JSON

\- created\_at



used\_nonces



\- id

\- kiosk\_id

\- nonce

\- created\_at



I. Suggested Laravel structure



Create services:



\- app/Services/GoogleAuthService.php

\- app/Services/GoogleWorkspaceDirectoryService.php

\- app/Services/SlackApprovalService.php

\- app/Services/KioskSecurityService.php

\- app/Services/ChallengeQuestionService.php

\- app/Services/PasswordGeneratorService.php

\- app/Services/AuditLogService.php



Create jobs:



\- app/Jobs/SendSlackResetApprovalJob.php

\- app/Jobs/ResetGooglePasswordJob.php

\- app/Jobs/ExpirePendingResetRequestsJob.php



Create middleware:



\- app/Http/Middleware/ValidateKioskRequest.php

\- app/Http/Middleware/VerifySlackSignature.php

\- app/Http/Middleware/EnsureAdminUser.php



Create controllers:



\- app/Http/Controllers/StudentRegistrationController.php

\- app/Http/Controllers/KioskResetController.php

\- app/Http/Controllers/KioskEnrollmentController.php

\- app/Http/Controllers/SlackInteractionController.php

\- app/Http/Controllers/Admin/DashboardController.php

\- app/Http/Controllers/Admin/KioskController.php

\- app/Http/Controllers/Admin/PasswordResetRequestController.php

\- app/Http/Controllers/Admin/AuditLogController.php



J. Routes



Create routes similar to:



Student registration:



\- GET /register

\- GET /auth/google/redirect

\- GET /auth/google/callback

\- POST /register/questions

\- POST /register/photo

\- POST /register/complete



Kiosk:



\- POST /kiosk/enroll

\- POST /kiosk/heartbeat

\- GET /kiosk/reset

\- POST /kiosk/reset/lookup

\- POST /kiosk/reset/photo

\- POST /kiosk/reset/submit

\- GET /kiosk/reset/status/{request}

\- GET /kiosk/reset/temp-password/{request}



Slack:



\- POST /slack/interactions



Admin:



\- GET /admin/dashboard

\- GET /admin/requests

\- GET /admin/kiosks

\- POST /admin/kiosks

\- POST /admin/kiosks/{kiosk}/disable

\- POST /admin/kiosks/{kiosk}/rotate-secret

\- GET /admin/audit



K. Environment variables



Add these to .env.example:



STUDENT\_GOOGLE\_DOMAIN=

ALLOWED\_STUDENT\_ORG\_UNITS=

GOOGLE\_CLIENT\_ID=

GOOGLE\_CLIENT\_SECRET=

GOOGLE\_REDIRECT\_URI=

GOOGLE\_SERVICE\_ACCOUNT\_JSON\_PATH=

GOOGLE\_ADMIN\_IMPERSONATION\_EMAIL=

GOOGLE\_DIRECTORY\_SCOPES=



SLACK\_BOT\_TOKEN=

SLACK\_SIGNING\_SECRET=

SLACK\_RESET\_CHANNEL\_ID=

SLACK\_APPROVER\_USERGROUP\_ID=



KIOSK\_ALLOWED\_NETWORKS=

KIOSK\_HMAC\_TOLERANCE\_SECONDS=300

RESET\_REQUEST\_EXPIRATION\_MINUTES=10

TEMP\_PASSWORD\_DISPLAY\_SECONDS=90

PHOTO\_RETENTION\_DAYS=90



L. Temporary password generation



Generate temporary passwords that are:



\- random

\- not based on student information

\- not logged

\- readable enough for students to type

\- compliant with Google Workspace password policy



Example style:



word-word-4digits-word



Use a secure random source and a safe word list.



M. UI requirements



Keep the UI kiosk-friendly:



\- large buttons

\- simple language

\- minimal text

\- clear camera preview

\- clear privacy notice

\- clear “request sent” screen

\- waiting screen while Slack approval is pending

\- clear temporary password screen after approval

\- auto-timeout and return to start screen

\- no admin links on kiosk screen



N. Testing



Add tests for:



1\. Student Google login domain validation.

2\. Challenge answer hashing and validation.

3\. Kiosk HMAC validation.

4\. Kiosk nonce replay rejection.

5\. Kiosk IP allowlist rejection.

6\. Slack signature validation.

7\. Unauthorized Slack user cannot approve.

8\. Request cannot be approved twice.

9\. Expired request cannot be approved.

10\. Google reset job only runs after approval.

11\. Temporary password is not logged or stored.

12\. Staff/admin accounts are rejected.



O. Build phases



Implement this in phases.



Phase 1:

\- migrations

\- models

\- config files

\- .env.example additions

\- basic service class stubs

\- audit logging service

\- README setup notes



Phase 2:

\- Google student sign-in and registration



Phase 3:

\- challenge questions and registration photo capture



Phase 4:

\- kiosk enrollment, heartbeat, and HMAC validation



Phase 5:

\- kiosk reset request flow



Phase 6:

\- Slack Block Kit approval message and Slack signature verification



Phase 7:

\- Google Workspace password reset job



Phase 8:

\- kiosk temporary password display after approval



Phase 9:

\- admin dashboard



Phase 10:

\- tests and security hardening



Phase 11:

\- complete Ubuntu server install guide (clean install)

\- PHP, MySQL, Nginx, Composer, Node, queue worker

\- Google Cloud + Google Workspace Admin steps

\- install path /var/www/sspkiosk

\- Google JSON credentials path (e.g. /var/www/sspkiosk/storage/app/google/service-account.json)

\- see docs/INSTALL-UBUNTU.md



Important instruction:

Before coding, inspect the current Laravel project structure and propose the exact files you will add or modify. Then implement Phase 1 only. Do not continue to Phase 2 until I approve.

