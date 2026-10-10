@if (session('success'))
    <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
@endif
@if (session('error'))
    <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
@endif
