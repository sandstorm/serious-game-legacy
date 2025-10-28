<x-layout>
    <h1>Login</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label class="form-group__label" for="soscisurveyId">SoSciSurvey ID</label>
            <input class="form-group__input form__textfield" id="soscisurveyId" type="text" name="soscisurveyId" value="{{ old('soscisurveyId') }}" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-group__label" for="password">Passwort</label>
            <input class="form-group__input form__textfield" id="password" type="password" name="password" required>
        </div>
        <hr />
        <div class="form-group">
            @error('soscisurveyId')
                <span class="form-error">{{ $message }}</span>
            @enderror
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
            <button type="submit" class="button button--type-primary">
                Login
            </button>
        </div>
    </form>
</x-layout>
