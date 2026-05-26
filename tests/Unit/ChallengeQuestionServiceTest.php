<?php

namespace Tests\Unit;

use App\Models\Student;
use App\Models\StudentChallengeQuestion;
use App\Services\ChallengeQuestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChallengeQuestionServiceTest extends TestCase
{
    use RefreshDatabase;

    private ChallengeQuestionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ChallengeQuestionService;
    }

    public function test_stores_hashed_answers_not_plaintext(): void
    {
        $student = Student::factory()->create();

        $this->service->storeAnswers($student, [
            ['question' => 'Favorite color?', 'answer' => 'Blue'],
            ['question' => 'First pet name?', 'answer' => 'Rex'],
            ['question' => 'Street you grew up on?', 'answer' => 'Oak'],
        ]);

        $stored = StudentChallengeQuestion::query()->first();

        $this->assertNotSame('Blue', $stored->answer_hash);
        $this->assertTrue(Hash::isHashed($stored->answer_hash));
        $this->assertTrue($this->service->answerMatches('blue', $stored->answer_hash));
    }

    public function test_validate_answers_returns_correct_count(): void
    {
        $student = Student::factory()->create();

        $this->service->storeAnswers($student, [
            ['question' => 'Q1', 'answer' => 'A1'],
            ['question' => 'Q2', 'answer' => 'A2'],
            ['question' => 'Q3', 'answer' => 'A3'],
        ]);

        $questions = $student->challengeQuestions()->pluck('id')->all();

        $score = $this->service->validateAnswers($student, $questions, [
            $questions[0] => 'A1',
            $questions[1] => 'wrong',
            $questions[2] => 'A3',
        ]);

        $this->assertSame(2, $score);
    }

    public function test_rejects_too_few_questions(): void
    {
        $student = Student::factory()->create();

        $this->expectException(\InvalidArgumentException::class);

        $this->service->storeAnswers($student, [
            ['question' => 'Only one?', 'answer' => 'Yes'],
        ]);
    }
}
