@extends('layouts.app')

@section('title', 'Create Announcement')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('admin.announcements.index') }}" class="btn-ghost mb-4">&larr; Back</a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Create Announcement</h1>

    <div class="card">
        <form method="POST" action="{{ route('admin.announcements.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="title" class="label">Title</label>
                <input id="title" type="text" name="title" class="input-field" required autocomplete="off">
            </div>
            <div>
                <label for="content" class="label">Content</label>
                <textarea id="content" name="content" rows="6" class="input-field" required autocomplete="off"></textarea>
            </div>
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" class="w-4 h-4 rounded border-gray-200 dark:border-gray-700 text-primary-700">
                    <span class="text-sm">Publish immediately</span>
                </label>
            </div>
            <button type="submit" class="btn-primary">Create Announcement</button>
        </form>
    </div>
</div>
@endsection
