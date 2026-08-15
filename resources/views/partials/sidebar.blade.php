<nav id="sidebar" class="sidebar">
    <div class="sidebar-brand">
        <a href="{{ url('/dashboard') }}">
            <img src="{{ asset('assets/img/logo.png') }}" alt="Logo">
        </a>
    </div>
    
    <button id="closeSidebar" class="close-btn"><i class="bi bi-x"></i></button>

    <ul class="sidebar-menu">
        <li>
            <a href="{{ route('dashboard.index') }}" class="{{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
        </li>

        <li class="menu-title">Employee</li>
        <li>
            <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.index') || request()->routeIs('employees.create') || request()->routeIs('employees.edit') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Management
            </a>
        </li>
        <li>
            <a href="{{ route('employees.bulk') }}" class="{{ request()->routeIs('employees.bulk*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-arrow-up-fill"></i> Bulk Import
            </a>
        </li>

        <li class="menu-title">Birthdays</li>
        <li>
            <a href="{{ route('employees.upcoming-birthdays') }}" class="{{ request()->routeIs('employees.upcoming-birthdays') ? 'active' : '' }}">
                <i class="bi bi-calendar-heart-fill"></i> Upcoming
            </a>
        </li>

        <li class="menu-title">Templates</li>
        <li>
            <a href="{{ route('email-templates.index') }}" class="{{ request()->routeIs('email-templates.*') ? 'active' : '' }}">
                <i class="bi bi-envelope-paper-fill"></i> Email Layouts
            </a>
        </li>
        <li>
            <a href="{{ route('sms-templates.index') }}" class="{{ request()->routeIs('sms-templates.*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots-fill"></i> SMS Layouts
            </a>
        </li>

        <li class="menu-title">Configuration</li>

        <li>
            <a href="{{ route('email-config.index') }}" class="{{ request()->routeIs('email-config.index') ? 'active' : '' }}">
                <i class="bi bi-hdd-network-fill"></i> SMTP Server
            </a>
        </li>
        <li>
            <a href="{{ route('sms-config.index') }}" class="{{ request()->routeIs('sms-config.index') ? 'active' : '' }}">
                <i class="bi bi-broadcast-pin"></i> Gateway Config
            </a>
        </li>
        <li>
            <a href="{{ route('sms-settings.index') }}" class="{{ request()->routeIs('sms-settings.*') ? 'active' : '' }}">
                <i class="bi bi-sliders"></i> SMS Settings
            </a>
        </li>
        <li>
            <a href="{{ route('email-settings.index') }}" class="{{ request()->routeIs('email-settings.*') ? 'active' : '' }}">
                <i class="bi bi-envelope-gear-fill"></i> Email Settings
            </a>
        </li>

        <li class="menu-title">Analytics & Cron</li>
        <li>
            <a href="{{ route('reports.summary') }}" class="{{ request()->routeIs('reports.summary') ? 'active' : '' }}">
                <i class="bi bi-pie-chart-fill"></i> Usage Reports
            </a>
        </li>
        <li>
            <a href="{{ route('logs.index') }}" class="{{ request()->routeIs('logs.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data-fill"></i> Delivery Logs
            </a>
        </li>
        <li>
            <a href="{{ route('cron-settings.index') }}" class="{{ request()->routeIs('cron-settings.*') ? 'active' : '' }}">
                <i class="bi bi-clock-fill"></i> Automation
            </a>
        </li>
    </ul>
</nav>
