<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentNotRegistered
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Student|null $student */
        $student = $request->attributes->get('registration_student');

        if ($student?->isRegistered()) {
            return redirect()->route('register.already-registered');
        }

        return $next($request);
    }
}
