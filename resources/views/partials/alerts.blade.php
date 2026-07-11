@if(session('success'))
    <div class="card" style="border-color:#34d399;background:#ecfdf5;color:#065f46;">
        {{ session('success') }}
    </div>
@endif

@if(session('warning'))
    <div class="card" style="border-color:#fcd34d;background:#fffbeb;color:#92400e;">
        {{ session('warning') }}
    </div>
@endif

@if($errors->any())
    <div class="card" style="border-color:#fca5a5;background:#fef2f2;color:#991b1b;">
        <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
