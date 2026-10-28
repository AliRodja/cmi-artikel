@props(['status'])

@if ((int) $status === \App\Models\Article::STATUS_PUBLISHED)
    <span class="badge bg-green-100 text-green-700">Published</span>
@else
    <span class="badge bg-gray-100 text-gray-600">Draft</span>
@endif