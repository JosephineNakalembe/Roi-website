@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content header-content-between">
            <div class="header-title-row">
                @include('partials.back-button', ['fallback' => route('admin.dashboard')])
                <h1 class="mb-0">Categories</h1>
            </div>
            <button class="btn" onclick="document.getElementById('addCategoryForm').style.display = document.getElementById('addCategoryForm').style.display === 'none' ? 'block' : 'none';">Add Category</button>
        </div>
    </div>

    <div id="addCategoryForm" class="card" style="display:none;max-width:520px;">
        <form method="POST" action="{{ route('admin.categories.store') }}" style="display:flex;gap:8px;">
            @csrf
            <input type="text" name="name" class="input" placeholder="New category name" required style="flex:1;">
            <button class="btn" type="submit">Add</button>
        </form>
        @error('name')<p style="color:#dc2626;font-size:0.9rem;margin-top:4px;">{{ $message }}</p>@enderror
    </div>

    <div class="card">
        @if($categories->isEmpty())
            <p class="text-muted">No categories yet.</p>
        @else
            <div style="display:grid;gap:8px;">
                @foreach($categories as $category)
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px;border:1px solid #e5e7eb;border-radius:10px;background:#fafafa;flex-wrap:wrap;">
                        <div style="flex:1;min-width:0;">
                            <div id="name_{{ $category->id }}" style="font-weight:600;font-size:1rem;">{{ $category->name }}</div>
                            <div id="nameForm_{{ $category->id }}" style="display:none;">
                                <form method="POST" action="{{ route('admin.categories.update', $category) }}" style="display:flex;gap:6px;align-items:center;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="name" class="input" value="{{ $category->name }}" required style="flex:1;padding:4px 8px;font-size:0.95rem;">
                                    <button class="btn" type="submit" style="padding:4px 10px;font-size:0.85rem;">Save</button>
                                    <button type="button" class="btn btn-secondary" onclick="toggleRename({{ $category->id }})" style="padding:4px 10px;font-size:0.85rem;">Cancel</button>
                                </form>
                            </div>
                            <span style="font-size:0.85rem;color:#6c757d;">{{ $category->products_count }} product{{ $category->products_count !== 1 ? 's' : '' }} &middot; {{ $category->slug }}</span>
                        </div>
                        <div style="display:flex;gap:6px;flex-shrink:0;">
                            <button class="btn btn-secondary" onclick="toggleRename({{ $category->id }})" style="padding:4px 10px;font-size:0.85rem;">Rename</button>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? Products will be unlinked but not deleted.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn" style="background:#dc2626;color:#fff;padding:4px 10px;font-size:0.85rem;">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
    function toggleRename(id) {
        const name = document.getElementById('name_' + id);
        const form = document.getElementById('nameForm_' + id);
        if (name.style.display === 'none') {
            name.style.display = 'block';
            form.style.display = 'none';
        } else {
            name.style.display = 'none';
            form.style.display = 'block';
        }
    }
    </script>
@endsection
