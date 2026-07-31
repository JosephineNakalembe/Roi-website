@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button', ['fallback' => route('admin.dashboard')])
            <h1 class="mb-0">Delivery Areas</h1>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#d1fae5;color:#065f46;padding:12px 16px;border-radius:10px;margin:16px 0;font-size:0.95rem;">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:10px;margin:16px 0;font-size:0.95rem;">
            @foreach($errors->all() as $error)
                <p style="margin:0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px;">
            <div class="stat-card">
                <div class="stat-value">{{ $deliveryAreas->total() }}</div>
                <div class="stat-label">Total Areas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $cities->count() }}</div>
                <div class="stat-label">Cities</div>
            </div>
        </div>

        <!-- Add Delivery Area Form -->
        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:20px;">
            <h3 style="margin:0 0 12px 0;font-size:1.1rem;">Add Delivery Area</h3>
            <form method="POST" action="{{ route('admin.delivery-areas.store') }}" style="display:grid;gap:12px;">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
                    <div>
                        <label style="font-size:0.95rem;font-weight:600;">City <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="city" class="input" list="cityList" placeholder="e.g. Kampala" required style="width:100%;">
                        <datalist id="cityList">
                            @foreach($cities as $city)
                                <option value="{{ $city }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label style="font-size:0.95rem;font-weight:600;">Place Name <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="name" class="input" placeholder="e.g. Ntinda" required style="width:100%;">
                    </div>
                    <div>
                        <label style="font-size:0.95rem;font-weight:600;">Shipping Cost (UGX) <span style="color:#dc2626;">*</span></label>
                        <input type="number" name="fee" class="input" min="0" step="100" required style="width:100%;">
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;">
                    <button class="btn" type="submit">Add Area</button>
                </div>
            </form>
        </div>

        <!-- Filter -->
        <form method="GET" action="{{ route('admin.delivery-areas.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:16px;padding:12px 16px;background:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;">
            <div>
                <select name="city" class="input">
                    <option value="">All Cities</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:8px;">
                <button class="btn btn-secondary" type="submit">Filter</button>
                @if(request('city'))
                    <a href="{{ route('admin.delivery-areas.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        <!-- Delivery Areas Table -->
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.95rem;">
                <thead>
                    <tr style="border-bottom:2px solid #e5e7eb;text-align:left;">
                        <th style="padding:12px 16px;font-weight:600;">City</th>
                        <th style="padding:12px 16px;font-weight:600;">Place</th>
                        <th style="padding:12px 16px;font-weight:600;text-align:right;">Shipping Cost</th>
                        <th style="padding:12px 16px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveryAreas as $area)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:12px 16px;">
                                <span style="background:#f3f4f6;padding:2px 8px;border-radius:4px;font-size:0.85rem;">{{ $area->city }}</span>
                            </td>
                            <td style="padding:12px 16px;font-weight:600;">{{ $area->name }}</td>
                            <td style="padding:12px 16px;text-align:right;font-weight:600;">UGX{{ number_format($area->fee, 0) }}</td>
                            <td style="padding:12px 16px;white-space:nowrap;">
                                <button type="button" onclick="editArea({{ $area->id }}, '{{ addslashes($area->city) }}', '{{ addslashes($area->name) }}', {{ $area->fee }})" style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:0.9rem;margin-right:8px;">Edit</button>
                                <form method="POST" action="{{ route('admin.delivery-areas.destroy', $area) }}" onsubmit="return confirm('Delete this delivery area?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:0.9rem;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;padding:40px;color:#9ca3af;">No delivery areas added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deliveryAreas->hasPages())
            <div style="margin-top:16px;text-align:center;">
                {{ $deliveryAreas->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Edit Modal -->
    <div id="editModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:24px;max-width:500px;width:90%;margin:auto;">
            <h2 style="margin:0 0 16px 0;">Edit Delivery Area</h2>
            <form method="POST" id="editForm" style="display:grid;gap:12px;">
                @csrf
                @method('PATCH')
                <div>
                    <label style="font-size:0.95rem;font-weight:600;">City <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="city" id="editCity" class="input" required style="width:100%;">
                </div>
                <div>
                    <label style="font-size:0.95rem;font-weight:600;">Place Name <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="name" id="editName" class="input" required style="width:100%;">
                </div>
                <div>
                    <label style="font-size:0.95rem;font-weight:600;">Shipping Cost (UGX) <span style="color:#dc2626;">*</span></label>
                    <input type="number" name="fee" id="editFee" class="input" min="0" step="100" required style="width:100%;">
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button class="btn" type="submit">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editArea(id, city, name, fee) {
            document.getElementById('editCity').value = city;
            document.getElementById('editName').value = name;
            document.getElementById('editFee').value = fee;
            document.getElementById('editForm').action = '{{ url('admin/delivery-areas') }}/' + id;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    </script>
@endsection
