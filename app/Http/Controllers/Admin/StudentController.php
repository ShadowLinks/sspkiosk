<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StudentPhotoType;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\AdminStudentService;
use App\Services\ResetAttemptLimiterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(
        private readonly AdminStudentService $adminStudents,
        private readonly ResetAttemptLimiterService $attemptLimiter,
    ) {}

    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        $students = Student::query()
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($inner) use ($query): void {
                    $inner->where('email', 'like', '%'.$query.'%')
                        ->orWhere('name', 'like', '%'.$query.'%')
                        ->orWhere('google_sub', 'like', '%'.$query.'%');
                });
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'query' => $query,
        ]);
    }

    public function show(Student $student): View
    {
        $student->load([
            'challengeQuestions',
            'photos',
            'passwordResetRequests' => fn ($query) => $query->with('kiosk')->latest('requested_at')->limit(20),
        ]);

        $registrationPhoto = $student->photos
            ->firstWhere('type', StudentPhotoType::Registration);

        return view('admin.students.show', [
            'student' => $student,
            'registrationPhoto' => $registrationPhoto,
            'failedAttemptsToday' => $this->attemptLimiter->failedAttemptsForStudentToday($student),
            'isLockedOut' => $this->attemptLimiter->isStudentLockedOut($student),
        ]);
    }

    public function disableReset(Request $request, Student $student): RedirectResponse
    {
        $this->adminStudents->setResetEnabled($student, false, (int) $request->user()->id);

        return redirect()
            ->route('admin.students.show', $student)
            ->with('status', 'Kiosk password reset disabled for this student.');
    }

    public function enableReset(Request $request, Student $student): RedirectResponse
    {
        $this->adminStudents->setResetEnabled($student, true, (int) $request->user()->id);

        return redirect()
            ->route('admin.students.show', $student)
            ->with('status', 'Kiosk password reset enabled for this student.');
    }
}
