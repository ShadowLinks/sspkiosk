@extends('layouts.kiosk')

@section('title', 'Security questions')

@section('content')
    <h1>Security questions</h1>

    <p>Signed in as <strong>{{ $student->name }}</strong>.</p>

    @if (session('error'))
        <div class="alert-error" role="alert">{{ session('error') }}</div>
    @endif

    <div class="notice" role="note">{{ $notice }}</div>

    <p>
        Create between <strong>{{ $minQuestions }}</strong> and <strong>{{ $maxQuestions }}</strong>
        questions only you would know. Answers are stored securely and are not shown to you again.
    </p>

    <form method="post" action="{{ route('register.questions.store') }}" id="questions-form">
        @csrf
        <div id="questions-list">
            @php
                $rows = old('questions', $existingQuestions->isNotEmpty()
                    ? $existingQuestions->map(fn ($q) => ['question' => $q->question_text, 'answer' => ''])->values()->all()
                    : array_fill(0, $minQuestions, ['question' => '', 'answer' => '']));
            @endphp
            @foreach ($rows as $index => $row)
                <fieldset class="question-block">
                    <legend>Question {{ $index + 1 }}</legend>
                    <label>
                        Question
                        <input type="text" name="questions[{{ $index }}][question]" value="{{ $row['question'] ?? '' }}" required maxlength="500">
                    </label>
                    <label>
                        Answer
                        <input type="password" name="questions[{{ $index }}][answer]" autocomplete="off" required maxlength="255" @if($existingQuestions->isNotEmpty()) placeholder="Re-enter answer to confirm" @endif>
                    </label>
                </fieldset>
            @endforeach
        </div>

        @if ($errors->any())
            <div class="alert-error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <button type="button" class="btn btn-secondary" id="add-question" @if(count($rows) >= $maxQuestions) hidden @endif>
            Add another question
        </button>

        <button type="submit" class="btn btn-primary">Save questions and continue</button>
    </form>

    <script>
        (function () {
            const min = {{ $minQuestions }};
            const max = {{ $maxQuestions }};
            const list = document.getElementById('questions-list');
            const addBtn = document.getElementById('add-question');
            let index = list.querySelectorAll('.question-block').length;

            addBtn?.addEventListener('click', function () {
                if (index >= max) return;
                const fieldset = document.createElement('fieldset');
                fieldset.className = 'question-block';
                fieldset.innerHTML = `
                    <legend>Question ${index + 1}</legend>
                    <label>Question <input type="text" name="questions[${index}][question]" required maxlength="500"></label>
                    <label>Answer <input type="password" name="questions[${index}][answer]" autocomplete="off" required maxlength="255"></label>
                `;
                list.appendChild(fieldset);
                index++;
                if (index >= max) addBtn.hidden = true;
            });
        })();
    </script>
@endsection
