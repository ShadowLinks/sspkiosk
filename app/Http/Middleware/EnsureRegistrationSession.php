<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrationSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $studentId = session(config('student-password-reset.registration_session_key'));

        if (! $studentId) {
            return redirect()
                ->route('register.index')
                ->with('error', 'Your registration session expired. Please sign in again.');
        }

        $student = Student::query()->find($studentId);

        if (! $student) {
            session()->forget(config('student-password-reset.registration_session_key'));

            return redirect()
                ->route('register.index')
                ->with('error', 'Your registration session expired. Please sign in again.');
        }

        $request->attributes->set('registration_student', $student);

        return $next($request);
    }
}
