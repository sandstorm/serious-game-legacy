<x-layout :remove-padding="true">
    <x-slot:title>Anmelden</x-slot:title>
    <x-slot:footer>
        <x-footer.footer />
    </x-slot:footer>
    <div class="login">
        <div class="login__header">
            <h1>Hallo bei Legacy</h1>
            <p>
                Ein Spiel zur Förderung finanzieller Bildung durch spielbasiertes Lernen
            </p>
        </div>

        <form class="login__form" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-group__label" for="soscisurveyId">SoSciSurvey ID</label>
                <input class="form-group__input form__textfield" id="soscisurveyId" type="text" name="soscisurveyId" value="{{ old('soscisurveyId') }}" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-group__label" for="password">Passwort</label>
                <input class="form-group__input form__textfield" id="password" type="password" name="password" required>
            </div>
            @error('soscisurveyId') <span class="form-error">{{ $message }}</span> @enderror
            @error('password') <span class="form-error">{{ $message }}</span> @enderror

            <button type="submit" class="button button--type-primary">Anmelden</button>
        </form>
    </div>
</x-layout>
