<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_loads(): void
    {
        $response = $this->get(route('register.index'));

        $response->assertOk();
        $response->assertSee('Student password reset registration');
    }

    public function test_google_redirect_returns_service_unavailable_when_not_configured(): void
    {
        Config::set('google-workspace.student_domain', null);
        Config::set('google-workspace.oauth.client_id', null);

        $response = $this->get(route('auth.google.redirect'));

        $response->assertStatus(503);
    }

    public function test_continue_requires_registration_session(): void
    {
        $response = $this->get(route('register.continue'));

        $response->assertRedirect(route('register.index'));
        $response->assertSessionHas('error');
    }

    public function test_continue_redirects_to_questions_when_session_present(): void
    {
        $student = Student::factory()->create([
            'registered_at' => null,
        ]);

        $response = $this->withSession([
            config('student-password-reset.registration_session_key') => $student->id,
        ])->get(route('register.continue'));

        $response->assertRedirect(route('register.questions'));
    }
}
