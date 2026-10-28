@props(['status'])

@if ((int) $status === \App\Models\Article::STATUS_PUBLISHED)
    <span class="badge gap-1.5 bg-green-50 text-green-700 ring-1 ring-green-600/20">
        <span class="size-1.5 rounded-full bg-green-500"></span> Published
    </span>
@else
    <span class="badge gap-1.5 bg-amber-50 text-amber-700 ring-1 ring-amber-600/20">
        <span class="size-1.5 rounded-full bg-amber-500"></span> Draft
    </span>
@endif
