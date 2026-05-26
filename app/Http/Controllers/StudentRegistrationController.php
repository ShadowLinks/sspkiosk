<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\ValidatedGoogleStudent;
use App\Enums\AuditActorType;
use App\Exceptions\StudentAuthenticationException;
use App\Http\Requests\StoreChallengeQuestionsRequest;
use App\Http\Requests\StoreRegistrationPhotoRequest;
use App\Models\Student;
use App\Services\AuditLogService;
use App\Services\ChallengeQuestionService;
use App\Services\ConfigurationValidatorService;
use App\Services\GoogleAuthService;
use App\Services\RegistrationProgressService;
use App\Services\StudentRegistrationPhotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentRegistrationController extends Controller
{
    public function __construct(
        private readonly GoogleAuthService $googleAuth,
        private readonly AuditLogService $auditLog,
        private readonly ConfigurationValidatorService $configurationValidator,
        private readonly ChallengeQuestionService $challengeQuestions,
        private readonly StudentRegistrationPhotoService $registrationPhotos,
        private readonly RegistrationProgressService $registrationProgress,
    ) {}

    public function index(): View
    {
        return view('register.index', [
            'notice' => config('student-password-reset.registration_notice'),
            'isConfigured' => $this->configurationValidator->isWorkflowConfigured('google_auth'),
        ]);
    }

    public function redirectToGoogle(): RedirectResponse
    {
        return $this->googleAuth->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $validated = $this->googleAuth->handleCallback();
            $student = $this->upsertStudent($validated);

            $request->session()->put(
                config('student-password-reset.registration_session_key'),
                $student->id,
            );

            $this->auditLog->log(
                'student.registration.google_auth.success',
                AuditActorType::Student,
                (string) $student->id,
                'student',
                (string) $student->id,
                [
                    'email' => $student->email,
                    'org_unit_path' => $student->org_unit_path,
                ],
                $request,
            );

            if ($student->isRegistered()) {
                return redirect()->route('register.already-registered');
            }

            return redirect()->route($this->registrationProgress->nextRouteName($student));
        } catch (StudentAuthenticationException $exception) {
            $this->auditLog->log(
                'student.registration.google_auth.rejected',
                AuditActorType::System,
                null,
                null,
                null,
                [
                    'reason_code' => $exception->getReasonCode(),
                    'message' => $exception->getMessage(),
                ],
                $request,
            );

            return redirect()
                ->route('register.index')
                ->with('error', $exception->getUserMessage());
        }
    }

    public function continue(Request $request): RedirectResponse
    {
        /** @var Student $student */
        $student = $request->attributes->get('registration_student');

        return redirect()->route($this->registrationProgress->nextRouteName($student));
    }

    public function kioskRequired(): View
    {
        return view('register.kiosk-required');
    }

    public function showQuestions(Request $request): View
    {
        /** @var Student $student */
        $student = $request->attributes->get('registration_student');

        return view('register.questions', [
            'student' => $student,
            'notice' => config('student-password-reset.registration_notice'),
            'minQuestions' => config('student-password-reset.min_challenge_questions_per_student'),
            'maxQuestions' => config('student-password-reset.max_challenge_questions_per_student'),
            'existingQuestions' => $student->challengeQuestions()->orderBy('id')->get(),
        ]);
    }

    public function storeQuestions(StoreChallengeQuestionsRequest $request): RedirectResponse
    {
        /** @var Student $student */
        $student = $request->attributes->get('registration_student');

        try {
            $this->challengeQuestions->storeAnswers($student, $request->questionsAndAnswers());
        } catch (\InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        $this->auditLog->logStudent(
            'student.registration.questions.saved',
            $student->id,
            ['question_count' => $student->challengeQuestions()->count()],
            $request,
        );

        return redirect()
            ->route('register.photo')
            ->with('status', 'Security questions saved. Please take your registration photo next.');
    }

    public function showPhoto(Request $request): View|RedirectResponse
    {
        /** @var Student $student */
        $student = $request->attributes->get('registration_student');

        if (! $this->registrationProgress->hasEnoughChallengeQuestions($student)) {
            return redirect()
                ->route('register.questions')
                ->with('error', 'Please set up your security questions first.');
        }

        return view('register.photo', [
            'student' => $student,
            'notice' => config('student-password-reset.registration_notice'),
        ]);
    }

    public function storePhoto(StoreRegistrationPhotoRequest $request): RedirectResponse
    {
        /** @var Student $student */
        $student = $request->attributes->get('registration_student');

        if (! $this->registrationProgress->hasEnoughChallengeQuestions($student)) {
            return redirect()
                ->route('register.questions')
                ->with('error', 'Please set up your security questions first.');
        }

        $photo = $this->registrationPhotos->storeRegistrationPhoto(
            $student,
            $request->file('photo'),
            $request,
        );

        $this->auditLog->logStudent(
            'student.registration.photo.saved',
            $student->id,
            ['photo_id' => $photo->id],
            $request,
        );

        return redirect()
            ->route('register.review')
            ->with('status', 'Photo saved. Review and finish registration.');
    }

    public function showReview(Request $request): View|RedirectResponse
    {
        /** @var Student $student */
        $student = $request->attributes->get('registration_student');

        if (! $this->registrationProgress->isReadyToComplete($student)) {
            return redirect()->route($this->registrationProgress->nextRouteName($student));
        }

        return view('register.review', [
            'student' => $student,
            'questionCount' => $student->challengeQuestions()->count(),
            'notice' => config('student-password-reset.registration_notice'),
        ]);
    }

    public function complete(Request $request): RedirectResponse
    {
        /** @var Student $student */
        $student = $request->attributes->get('registration_student');

        if (! $this->registrationProgress->isReadyToComplete($student)) {
            return redirect()
                ->route($this->registrationProgress->nextRouteName($student))
                ->with('error', 'Please complete all registration steps first.');
        }

        $student->update([
            'registered_at' => now(),
            'reset_enabled' => true,
        ]);

        $registrationPhoto = $student->photos()
            ->where('type', \App\Enums\StudentPhotoType::Registration)
            ->latest('id')
            ->first();

        $this->auditLog->logStudent(
            'student.registration.completed',
            $student->id,
            [
                'email' => $student->email,
                'google_sub' => $student->google_sub,
                'name' => $student->name,
                'school' => $student->school,
                'grade' => $student->grade,
                'org_unit_path' => $student->org_unit_path,
                'photo_id' => $registrationPhoto?->id,
                'question_count' => $student->challengeQuestions()->count(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'kiosk_id' => $request->session()->get(config('kiosk.registration_session_kiosk_key')),
            ],
            $request,
        );

        $request->session()->forget(config('student-password-reset.registration_session_key'));

        return redirect()->route('register.complete');
    }

    public function showComplete(): View
    {
        return view('register.complete');
    }

    public function alreadyRegistered(Request $request): View
    {
        /** @var Student|null $student */
        $student = $request->attributes->get('registration_student');

        return view('register.already-registered', [
            'student' => $student,
        ]);
    }

    private function upsertStudent(ValidatedGoogleStudent $validated): Student
    {
        return Student::query()->updateOrCreate(
            ['google_sub' => $validated->googleSub],
            [
                'email' => $validated->email,
                'name' => $validated->name,
                'school' => $validated->school,
                'grade' => $validated->grade,
                'org_unit_path' => $validated->orgUnitPath,
            ],
        );
    }
}
