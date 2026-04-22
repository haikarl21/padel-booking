<div id="sidebar" class="d-flex flex-column flex-shrink-0 p-3 bg-white" style="width: 250px; min-height: 100vh;">
    <a href="{{ url('/admin/dashboard') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
        <i class="bi bi-globe fs-4 me-2"></i>
        <span class="fs-5 fw-semibold">CourtElite</span>
    </a>
    <hr>
    <h6 class="text-muted px-3">Platform</h6>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ url('/admin/dashboard') }}" class="nav-link {{ request()->is('admin/dashboard*') ? 'active' : 'text-dark' }}">
                <i class="bi bi-speedometer2 me-2"></i> Dasboard
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('/admin/courts') }}" class="nav-link {{ request()->is('admin/courts*') ? 'active' : 'text-dark' }}">
                <i class="bi bi-door-open me-2"></i> Lapangan
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('/admin/timeslots') }}" class="nav-link {{ request()->is('admin/timeslots*') ? 'active' : 'text-dark' }}">
                <i class="bi bi-clock me-2"></i> Slot Waktu
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('/admin/bookings') }}" class="nav-link {{ request()->is('admin/bookings*') ? 'active' : 'text-dark' }}">
                <i class="bi bi-journal-check me-2"></i> Booking
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('/admin/users') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : 'text-dark' }}">
                <i class="bi bi-people me-2"></i> Pengguna
            </a>
        </li>
    </ul>
    <hr>
    <div class="mt-auto px-3">
        <a href="{{ route('admin.profile') }}" class="d-flex align-items-center link-dark text-decoration-none mb-2">
            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="rounded-circle me-2" style="width:32px;height:32px;object-fit:cover;">
            <strong>{{ auth()->user()->name ?? 'Super Admin' }}</strong>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="d-flex">
            @csrf
            <button type="submit" class="btn btn-link p-0 m-0 d-flex align-items-center text-dark" style="text-decoration:none;">
                <i class="bi bi-box-arrow-right me-2"></i> Keluar
            </button>
        </form>
    </div>
</div>
