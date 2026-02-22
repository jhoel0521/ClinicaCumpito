@props([
    'title',
    'description',
])

<div data-auth-header>
    <h1 data-auth-title>{{ $title }}</h1>
    <p data-auth-description>{{ $description }}</p>
</div>
