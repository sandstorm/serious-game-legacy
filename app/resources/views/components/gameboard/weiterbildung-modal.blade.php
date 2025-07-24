@extends ('components.modal.modal', ['closeModal' => "closeWeiterbildung()", 'size' => 'medium'])

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
                {{-- Livewire-Formular --}}
                <form wire:submit="submitAnswer">
                    @foreach ($weiterbildung->answerOptions as $key => $option)
                        <div class="mt-2">
                            <label>
                                <input
                                    type="radio"
                                    name="selectedAnswer"
                                    value="{{ $option->id->value }}"
                                    wire:model="selectedAnswer"
                                />
                                {{ $option->text }}
                            </label>
                        </div>
                    @endforeach

                    <div class="mt-4">
                        <x-form.submit disabled wire:dirty.remove.attr="disabled">
                            Antwort abgeben
                        </x-form.submit>
                    </div>

                    @if (session()->has('message'))
                        <div class="alert alert-success mt-2">
                            {{ session('message') }}
                        </div>
                    @endif
                </form>
            </div>
        @endif
    </div>
@endsection

@section('footer')
    <button type="button" class="button button--type-primary" wire:click="closeWeiterbildung()">Schließen</button>
@endsection
