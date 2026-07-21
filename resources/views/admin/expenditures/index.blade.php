@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button', ['fallback' => route('admin.dashboard')])
            <h1 class="mb-0">Expenditures</h1>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#d1fae5;color:#065f46;padding:12px 16px;border-radius:10px;margin:16px 0;font-size:0.95rem;">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px;">
            <div class="stat-card">
                <div class="stat-value">UGX{{ number_format($totalAmount, 0) }}</div>
                <div class="stat-label">Total Expenditures</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $expenditures->total() }}</div>
                <div class="stat-label">Total Entries</div>
            </div>
        </div>

        <!-- Add Expenditure Form -->
        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:20px;">
            <h3 style="margin:0 0 12px 0;font-size:1.1rem;">Add Expenditure</h3>
            <form method="POST" action="{{ route('admin.expenditures.store') }}" style="display:grid;gap:12px;">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
                    <div>
                        <label style="font-size:0.95rem;font-weight:600;">Name <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="name" class="input" placeholder="e.g. Rent, Utilities, Salaries" required style="width:100%;">
                    </div>
                    <div>
                        <label style="font-size:0.95rem;font-weight:600;">Amount (UGX) <span style="color:#dc2626;">*</span></label>
                        <input type="number" name="amount" class="input" min="0" step="100" required style="width:100%;">
                    </div>
                    <div>
                        <label style="font-size:0.95rem;font-weight:600;">Date <span style="color:#dc2626;">*</span></label>
                        <input type="date" name="date" class="input" value="{{ date('Y-m-d') }}" required style="width:100%;">
                    </div>
                    <div>
                        <label style="font-size:0.95rem;font-weight:600;">Category</label>
                        <input type="text" name="category" class="input" list="expenditureCategories" placeholder="e.g. Operations, Marketing" style="width:100%;">
                        <datalist id="expenditureCategories">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>
                <div>
                    <label style="font-size:0.95rem;font-weight:600;">Description</label>
                    <textarea name="description" class="input" rows="2" placeholder="Optional notes..." style="width:100%;"></textarea>
                </div>
                <div style="display:flex;justify-content:flex-end;">
                    <button class="btn" type="submit">Add Expenditure</button>
                </div>
            </form>
        </div>

        <!-- Filters -->
        <form method="GET" action="{{ route('admin.expenditures.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:16px;padding:12px 16px;background:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;">
            <div>
                <select name="category" class="input">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="from" class="input" value="{{ request('from') }}" placeholder="From">
            </div>
            <div>
                <input type="date" name="to" class="input" value="{{ request('to') }}" placeholder="To">
            </div>
            <div style="display:flex;gap:8px;">
                <button class="btn btn-secondary" type="submit">Filter</button>
                @if(request('category') || request('from') || request('to'))
                    <a href="{{ route('admin.expenditures.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        <!-- Expenditures Table -->
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.95rem;">
                <thead>
                    <tr style="border-bottom:2px solid #e5e7eb;text-align:left;">
                        <th style="padding:12px 16px;font-weight:600;">Date</th>
                        <th style="padding:12px 16px;font-weight:600;">Name</th>
                        <th style="padding:12px 16px;font-weight:600;">Category</th>
                        <th style="padding:12px 16px;font-weight:600;text-align:right;">Amount</th>
                        <th style="padding:12px 16px;font-weight:600;">Description</th>
                        <th style="padding:12px 16px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenditures as $exp)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:12px 16px;white-space:nowrap;">{{ $exp->date->format('d M Y') }}</td>
                            <td style="padding:12px 16px;font-weight:600;">{{ $exp->name }}</td>
                            <td style="padding:12px 16px;">
                                @if($exp->category)
                                    <span style="background:#f3f4f6;padding:2px 8px;border-radius:4px;font-size:0.85rem;">{{ $exp->category }}</span>
                                @else
                                    <span style="color:#9ca3af;">—</span>
                                @endif
                            </td>
                            <td style="padding:12px 16px;text-align:right;font-weight:600;">UGX{{ number_format($exp->amount, 0) }}</td>
                            <td style="padding:12px 16px;color:#6b7280;max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $exp->description ?: '—' }}</td>
                            <td style="padding:12px 16px;">
                                <form method="POST" action="{{ route('admin.expenditures.destroy', $exp) }}" onsubmit="return confirm('Delete this expenditure?');" style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:0.9rem;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;">No expenditures recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenditures->hasPages())
            <div style="margin-top:16px;text-align:center;">
                {{ $expenditures->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
