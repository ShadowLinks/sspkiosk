<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentRegistrationCompletionTest extends TestCase
{
    use RefreshDatabase;

    private function sessionFor(Student $student): array
    {
        return [
            config('student-password-reset.registration_session_key') => $student->id,
        ];
    }

    public function test_continue_redirects_to_questions_for_new_student(): void
    {
        $student = Student::factory()->create();

        $response = $this->withSession($this->sessionFor($student))
            ->get(route('register.continue'));

        $response->assertRedirect(route('register.questions'));
    }

    public function test_can_store_challenge_questions(): void
    {
        $student = Student::factory()->create();

        $response = $this->withSession($this->sessionFor($student))
            ->post(route('register.questions.store'), [
                'questions' => [
                    ['question' => 'Favorite teacher?', 'answer' => 'Smith'],
                    ['question' => 'Favorite subject?', 'answer' => 'Math'],
                    ['question' => 'Locker number?', 'answer' => '104'],
                ],
            ]);

        $response->assertRedirect(route('register.photo'));
        $this->assertSame(3, $student->challengeQuestions()->count());
    }

    public function test_can_complete_registration_with_photo(): void
    {
        Storage::fake('local');
        $student = Student::factory()->create();
        $session = $this->sessionFor($student);

        $this->withSession($session)->post(route('register.questions.store'), [
            'questions' => [
                ['question' => 'What is your favorite color?', 'answer' => 'Blue'],
                ['question' => 'What is your first pet name?', 'answer' => 'Rex'],
                ['question' => 'What street did you grow up on?', 'answer' => 'Oak'],
            ],
        ]);

        $this->withSession($session)->post(route('register.photo.store'), [
            'photo' => UploadedFile::fake()->create('registration.jpg', 100, 'image/jpeg'),
        ]);

        $response = $this->withSession($session)->post(route('register.complete.submit'));

        $response->assertRedirect(route('register.complete'));

        $student->refresh();
        $this->assertNotNull($student->registered_at);
        $this->assertTrue($student->hasRegistrationPhoto());
    }

    public function test_complete_fails_without_photo(): void
    {
        $student = Student::factory()->create();
        $session = $this->sessionFor($student);

        $this->withSession($session)->post(route('register.questions.store'), [
            'questions' => [
                ['question' => 'What is your favorite color?', 'answer' => 'Blue'],
                ['question' => 'What is your first pet name?', 'answer' => 'Rex'],
                ['question' => 'What street did you grow up on?', 'answer' => 'Oak'],
            ],
        ]);

        $response = $this->withSession($session)->post(route('register.complete.submit'));

        $response->assertRedirect(route('register.photo'));
        $this->assertNull($student->fresh()->registered_at);
    }

    public function test_registered_student_redirected_from_questions(): void
    {
        $student = Student::factory()->registered()->create();

        $response = $this->withSession($this->sessionFor($student))
            ->get(route('register.questions'));

        $response->assertRedirect(route('register.already-registered'));
    }
}
