<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentChallengeQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ChallengeQuestionService
{
    /**
     * @param  array<int, array{question: string, answer: string}>  $questionsAndAnswers
     */
    public function storeAnswers(Student $student, array $questionsAndAnswers): void
    {
        $max = config('student-password-reset.max_challenge_questions_per_student');
        $min = config('student-password-reset.min_challenge_questions_per_student');

        if (count($questionsAndAnswers) < $min || count($questionsAndAnswers) > $max) {
            throw new \InvalidArgumentException(
                "You must provide between {$min} and {$max} challenge questions.",
            );
        }

        DB::transaction(function () use ($student, $questionsAndAnswers): void {
            $student->challengeQuestions()->delete();

            foreach ($questionsAndAnswers as $item) {
                $question = trim((string) ($item['question'] ?? ''));
                $answer = (string) ($item['answer'] ?? '');

                if ($question === '' || $answer === '') {
                    throw new \InvalidArgumentException('Each challenge question and answer is required.');
                }

                $student->challengeQuestions()->create([
                    'question_text' => $question,
                    'answer_hash' => $this->hashAnswer($answer),
                ]);
            }
        });
    }

    /**
     * @param  array<int, int>  $presentedQuestionIds
     * @param  array<int, string>  $submittedAnswers  keyed by question id
     */
    /**
     * @return \Illuminate\Support\Collection<int, StudentChallengeQuestion>
     */
    public function selectRandomQuestions(Student $student, ?int $count = null): \Illuminate\Support\Collection
    {
        $count ??= config('student-password-reset.challenge_questions_to_ask');
        $questions = $student->challengeQuestions()->get();

        if ($questions->count() <= $count) {
            return $questions;
        }

        return $questions->random($count);
    }

    public function validateAnswers(Student $student, array $presentedQuestionIds, array $submittedAnswers): int
    {
        $correct = 0;

        $questions = $student->challengeQuestions()
            ->whereIn('id', $presentedQuestionIds)
            ->get();

        foreach ($questions as $question) {
            $submitted = $submittedAnswers[$question->id] ?? '';

            if ($this->answerMatches($submitted, $question->answer_hash)) {
                $correct++;
            }
        }

        return $correct;
    }

    public function hashAnswer(string $answer): string
    {
        return Hash::make($this->normalizeAnswer($answer));
    }

    public function answerMatches(string $submitted, string $hash): bool
    {
        return Hash::check($this->normalizeAnswer($submitted), $hash);
    }

    private function normalizeAnswer(string $answer): string
    {
        $answer = trim($answer);

        if (config('student-password-reset.challenge_answer_case_insensitive', true)) {
            $answer = mb_strtolower($answer);
        }

        return $answer;
    }
}
