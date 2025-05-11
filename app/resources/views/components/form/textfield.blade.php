<input
    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
    name="{{ $name }}"
    type="{{ $type ?? 'text' }}"
    placeholder="{{ $placeholder ?? '' }}"
    value="{{ $value ?? old($name) }}"
    {{ $attributes }}
/>
