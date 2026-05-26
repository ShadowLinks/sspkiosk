Configuration rule:



Anything that could vary by school division, environment, Google Workspace tenant, Slack workspace, kiosk network, security policy, retention rule, timeout, route behavior, feature flag, or password policy must be configurable through .env and Laravel config files.



Do not hardcode configurable values anywhere in controllers, services, jobs, middleware, routes, views, JavaScript, tests, or seeders.



All configurable values must follow this pattern:



1\. Add the value to .env.example.

2\. Read it through a Laravel config file, such as:

&#x20;  - config/google-workspace.php

&#x20;  - config/slack.php

&#x20;  - config/kiosk.php

&#x20;  - config/password-reset.php

3\. Access it in code using config('...'), not env('...') directly outside config files.

4\. Provide safe defaults only when appropriate.

5\. Fail closed when a required security value is missing.



Examples of values that must be configurable:



\- student Google domain

\- allowed student org units

\- blocked staff/admin org units

\- Google OAuth client ID

\- Google OAuth client secret

\- Google service account path

\- Google admin impersonation email

\- Google API scopes

\- Slack bot token

\- Slack signing secret

\- Slack reset channel ID

\- Slack approver user group ID

\- kiosk allowed networks

\- kiosk HMAC tolerance

\- kiosk heartbeat interval

\- kiosk heartbeat expiration window

\- reset request expiration time

\- temporary password display time

\- photo retention period

\- maximum failed attempts

\- lockout period

\- number of challenge questions to ask

\- minimum number of correct answers required

\- whether Slack approval is required

\- whether office verification is allowed

\- temporary password word list source

\- temporary password format

\- temporary password length

\- allowed registration locations

\- whether registration requires a kiosk

\- whether reset requires a kiosk

\- whether to allow student ID lookup

\- whether to allow email lookup

\- admin notification settings

\- audit log retention period

\- feature flags for mTLS/client certificate support

\- feature flags for printing temporary passwords

\- feature flags for SIS integration later



Create config files for these groups:



config/google-workspace.php

config/slack.php

config/kiosk.php

config/student-password-reset.php

config/audit.php



The application should validate required configuration at startup or during a health check. If required values are missing, show a clear admin-facing configuration error and block sensitive workflows.



I would also update the existing .env.example section to include more policy settings:



\# Student Password Reset Policy

RESET\_REQUEST\_EXPIRATION\_MINUTES=10

TEMP\_PASSWORD\_DISPLAY\_SECONDS=90

PHOTO\_RETENTION\_DAYS=90

AUDIT\_LOG\_RETENTION\_DAYS=365

MAX\_FAILED\_ATTEMPTS\_PER\_STUDENT=3

MAX\_FAILED\_ATTEMPTS\_PER\_KIOSK=10

LOCKOUT\_MINUTES=30

CHALLENGE\_QUESTIONS\_TO\_ASK=3

CHALLENGE\_QUESTIONS\_REQUIRED\_CORRECT=3

SLACK\_APPROVAL\_REQUIRED=true

OFFICE\_VERIFICATION\_ALLOWED=true

REGISTRATION\_REQUIRES\_KIOSK=false

RESET\_REQUIRES\_KIOSK=true

ALLOW\_STUDENT\_ID\_LOOKUP=true

ALLOW\_EMAIL\_LOOKUP=true



\# Kiosk Security

KIOSK\_ALLOWED\_NETWORKS=

KIOSK\_HMAC\_TOLERANCE\_SECONDS=300

KIOSK\_HEARTBEAT\_INTERVAL\_SECONDS=60

KIOSK\_HEARTBEAT\_EXPIRES\_AFTER\_SECONDS=180

KIOSK\_REQUIRE\_ACTIVE\_HEARTBEAT=true

KIOSK\_ENABLE\_MTLS=false

KIOSK\_ENABLE\_CLIENT\_CERTIFICATE\_CHECK=false



\# Temporary Password Policy

TEMP\_PASSWORD\_WORD\_LIST=default

TEMP\_PASSWORD\_FORMAT=word-word-4digits-word

TEMP\_PASSWORD\_MIN\_LENGTH=14



\# Optional Features

ENABLE\_TEMP\_PASSWORD\_PRINTING=false

ENABLE\_SIS\_INTEGRATION=false

ENABLE\_PHOTO\_RETENTION\_CLEANUP=true



The key instruction is this line:



Do not hardcode configurable values anywhere. Add them to .env.example, expose them through Laravel config files, and access them only with config('...') in application code.

