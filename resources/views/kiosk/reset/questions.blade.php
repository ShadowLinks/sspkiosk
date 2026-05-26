@extends('layouts.kiosk')

@section('title', 'Security questions')

@section('content')
    <h1>Answer your security questions</h1>

    <p>Answer all questions. You will not be told which answers are wrong.</p>

    <form method="post" action="{{ route('kiosk.reset.submit') }}">
        @csrf
        @foreach ($questions as $index => $question)
            <fieldset class="question-block">
                <legend>Question {{ $index + 1 }}</legend>
                <p>{{ $question['question'] }}</p>
                <label>
                    Your answer
                    <input type="password" name="answers[{{ $question['id'] }}]" required autocomplete="off" maxlength="255">
                </label>
            </fieldset>
        @endforeach

        <button type="submit" class="btn btn-primary">Submit request</button>
    </form>
@endsection
