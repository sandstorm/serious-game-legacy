@extends ('components.modal.modal')

@props([
    'weiterbildung' => null,
    'answerOptions' => [],
    'correctAnswerId' => null,
    'selectedAnswerId' => null,
    'isAnswerCorrect' => false,
])

@section('title')
    <div class="weiterbildung__header">
        <div>{{ $weiterbildung->getTitle() }}</div>
        <div class="weiterbildung__header-category">Weiterbildung</div>
    </div>
@endsection

@section('content')
    @if ($weiterbildung)
        {{ $weiterbildung->getDescription() }}
        <form wire:submit="submitAnswerForWeiterbildung">
            <div class="weiterbildung__answer-options">
                @foreach($answerOptions as $index => $option)
                    <label @class([
                       "weiterbildung__answer-option",
                       "weiterbildung__answer-option--correct" => $selectedAnswerId !== null && $option->id->value === $correctAnswerId->value,
                       "weiterbildung__answer-option--not-correct" => $isAnswerCorrect === false && $selectedAnswerId !== null && $option->id->value === $selectedAnswerId->value,
                       $this->getPlayerColorClass(),
                    ])>
                        <strong>
                            @if ($index === 0) A)
                            @elseif ($index === 1) B)
                            @elseif ($index === 2) C)
                            @elseif ($index === 3) D)
                            @endif
                        </strong>
                        {{ $option->text }}
                        @if ($selectedAnswerId !== null && $option->id->value === $correctAnswerId->value)
                            <span class="sr-only">Das ist die richtige Antwort.</span>
                        @endif
                        @if ($isAnswerCorrect === false && $selectedAnswerId !== null && $option->id->value === $selectedAnswerId->value)
                            <span class="sr-only">Diese Antwort war falsch.</span>
                        @endif
                        <x-form.radiofield
                            wire:model="weiterbildungForm.answer"
                            id="{{ $option->id->value }}"
                            name="weiterbildung"
                            value="{{ $option->id->value }}"
                            :disabled="!!$selectedAnswerId"
                        />
                    </label>
                @endforeach
            </div>
            @error('weiterbildungForm.answer') <span class="form__error">{{ $message }}</span> @enderror

            <div class="weiterbildung__footer">
                <div @class([
                    "weiterbildung__footer-icon",
                    "weiterbildung__footer-icon--correct" => $selectedAnswerId && $isAnswerCorrect,
                    "weiterbildung__footer-icon--hidden" => $selectedAnswerId && !$isAnswerCorrect
                ])>
                    <i class="icon-plus" aria-hidden="true"></i>
                    <x-gameboard.kompetenzen.kompetenz-icon-bildung :draw-half-empty="true" />
                    <span class="sr-only">Du bekommst eine halbe Bildungskompetenz</span>
                </div>

                @if ($selectedAnswerId)
                    @if ($isAnswerCorrect)
                        <strong>Super, richtig gelöst! </strong>
                    @else
                        <strong>Schade, das war nicht die richtige Antwort!  </strong>
                    @endif

                    <button type="button"
                            @class([
                                "button",
                                "button--type-primary",
                                $this->getPlayerColorClass(),
                            ])
                            wire:click="closeWeiterbildung()"
                    >
                        Weiter
                    </button>
                @else
                    <x-form.submit>
                        Auswahl bestätigen
                    </x-form.submit>
                @endif
            </div>
        </form>
    @endif
@endsection
