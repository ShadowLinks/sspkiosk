<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChallengeQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $min = config('student-password-reset.min_challenge_questions_per_student');
        $max = config('student-password-reset.max_challenge_questions_per_student');

        return [
            'questions' => ['required', 'array', "min:{$min}", "max:{$max}"],
            'questions.*.question' => ['required', 'string', 'min:3', 'max:500'],
            'questions.*.answer' => ['required', 'string', 'min:2', 'max:255'],
        ];
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    public function questionsAndAnswers(): array
    {
        return collect($this->validated('questions'))
            ->map(fn (array $item): array => [
                'question' => $item['question'],
                'answer' => $item['answer'],
            ])
            ->values()
            ->all();
    }
}
