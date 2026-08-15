 <header class="topbar">
    <div class="d-flex align-items-center">
        <button id="toggleSidebar" class="btn btn-link link-dark p-0 me-3 d-lg-none">
            <i class="bi bi-list fs-3"></i>
        </button>
        <div class="search-box d-none d-md-flex">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control border-0 shadow-none px-0" placeholder="Search anything...">
        </div>
    </div>

    <div class="topbar-actions d-flex align-items-center">
        <button id="themeToggle" class="btn btn-icon btn-light me-3" title="Toggle Theme">
            <i class="bi bi-moon-stars"></i>
        </button>
        
        <button id="fullscreenToggle" class="btn btn-icon btn-light me-3 d-none d-sm-flex" title="Toggle Fullscreen">
            <i class="bi bi-arrows-fullscreen"></i>
        </button>

        <div class="dropdown user-dropdown">
            <button class="btn dropdown-toggle d-flex align-items-center p-0 border-0 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-info d-none d-sm-block me-3 text-end">
                    <span class="d-block fw-bold small text-body">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <span class="d-block extra-small text-muted" style="font-size: 11px;">Administrator</span>
                </div>
                <div class="avatar-box">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=2563eb&color=fff" alt="Avatar" class="rounded-circle shadow-sm" width="40" height="40">
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 animate-up p-2" style="min-width: 200px;">
                <li><span class="dropdown-header text-uppercase extra-small fw-bold">Manage Account</span></li>
                <li><a class="dropdown-item rounded-2 py-2" href="#"><i class="bi bi-person me-2"></i> Profile</a></li>
                <li><a class="dropdown-item rounded-2 py-2" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
                <li><hr class="dropdown-divider mx-2"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger d-flex align-items-center rounded-2 py-2" type="submit">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

