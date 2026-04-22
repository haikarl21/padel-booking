<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons for sidebar icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="d-flex">
    @include('admin.layouts.sidebar')
    <div class="flex-grow-1 p-4">
        {{-- header row with hamburger and optional breadcrumb/title --}}
        <div class="d-flex align-items-center mb-3">
            <button class="btn btn-link text-dark p-0 me-2" id="sidebarToggle">
                <i class="bi bi-list" style="font-size:1.25rem;"></i>
            </button>

            @hasSection('breadcrumb')
                <nav aria-label="breadcrumb" class="flex-grow-1">
                    <ol class="breadcrumb mb-0">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            @endif
        </div>

        {{-- page title (below header row) --}}
        @hasSection('page-title')
            <h1 class="h4 mb-4">@yield('page-title')</h1>
        @endif

        @yield('content')
    </div>
</div>

<!-- simple script to toggle sidebar collapse -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('sidebarToggle');
        var sidebar = document.getElementById('sidebar');
        if (btn && sidebar) {
            btn.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
            });
        }
    });
</script>

<style>
    /* collapse sidebar by shrinking width and hiding links */
    #sidebar.collapsed {
        width: 0 !important;
        overflow: hidden;
        transition: width .3s;
    }
    #sidebar.collapsed .nav-link,
    #sidebar.collapsed hr,
    #sidebar.collapsed h6,
    #sidebar.collapsed .mt-auto {
        display: none;
    }
</style>
</body>
</html>
