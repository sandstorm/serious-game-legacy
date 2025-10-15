<x-layout>
    <h1>Login</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label class="form-group__label" for="soscisurveyId">SoSciSurvey ID</label>
            <input class="form-group__input form__textfield" id="soscisurveyId" type="text" name="soscisurveyId" value="{{ old('soscisurveyId') }}" required autofocus>
            @error('soscisurveyId')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label class="form-group__label" for="password">Passwort</label>
            <input class="form-group__input form__textfield" id="password" type="password" name="password" required>
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>
        <hr />
        <div class="form-group">
            <button type="submit" class="button button--type-primary">
                Login
            </button>
        </div>
    </form>
</x-layout>
