@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>Users</h1>
        <form method="GET" style="margin-bottom:18px;display:flex;gap:10px;flex-wrap:wrap;">
            <input class="input" type="search" name="search" value="{{ $search ?? '' }}" placeholder="Search users">
            <button class="btn">Search</button>
        </form>
        <div style="display:grid;gap:14px;">
            @foreach($users as $user)
                <div style="border:1px solid #e5e7eb;padding:16px;border-radius:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                    <div>
                        <strong>{{ $user->name }}</strong>
                        <p class="text-muted">{{ $user->email }} • {{ ucfirst($user->role) }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.users.status', $user) }}" style="display:flex;gap:10px;align-items:center;">
                        @csrf
                        @method('PATCH')
                        <select class="input" name="status">
                            <option value="active"{{ $user->status === 'active' ? ' selected' : '' }}>Active</option>
                            <option value="blocked"{{ $user->status === 'blocked' ? ' selected' : '' }}>Blocked</option>
                        </select>
                        <button class="btn">Save</button>
                    </form>
                </div>
            @endforeach
        </div>
        <div style="margin-top:20px;">{{ $users->links() }}</div>
    </div>
@endsection
