@extends ('components.modal.modal', ['closeModal' => null, 'size' => 'medium', 'closeButton' => false])
@section('title')
    Weiterbildungen
@endsection

@section('content')
    <div class="weiterbildung">
        @if ($weiterbildung)
            <h3>{{ $weiterbildung->title }}</h3>
            <p><strong>Beschreibung:</strong><br></p>
            {{ $weiterbildung->description }}

            <hr/>
            <br>

            <div class="answers mt-2">
                <strong>Antwortmöglichkeiten:</strong>
                <form wire:submit="submitAnswerForWeiterbildung">
                    <div class="form-group">
                        @foreach($answerOptions as $option)
                            <label>
                                {{ $option->text }}
                                <input wire:model="weiterbildungForm.answer" type="radio" id="{{ $option->id->value }}" name="weiterbildung" value="{{ $option->id->value }}"/>
                            </label>
                            <br />
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <x-form.submit disabled wire:dirty.remove.attr="disabled">
                            Antwort prüfen
                        </x-form.submit>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endsection

@section('footer')
    {{ $this->validationMessage }}
    <button type="button" class="button button--type-primary" wire:click="closeWeiterbildung()">Schließen</button>
@endsection
