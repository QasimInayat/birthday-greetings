// Sidebar Toggle
const sidebar = document.getElementById('sidebar');
const toggleSidebar = document.getElementById('toggleSidebar');
const closeSidebar = document.getElementById('closeSidebar');

if (toggleSidebar) {
    toggleSidebar.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });
}

if (closeSidebar) {
    closeSidebar.addEventListener('click', () => {
        sidebar.classList.remove('active');
    });
}

// Dark Mode Toggle
const themeToggle = document.getElementById('themeToggle');
const htmlElement = document.documentElement;

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        let theme = htmlElement.getAttribute('data-bs-theme') || 'light';
        let newTheme = theme === 'light' ? 'dark' : 'light';
        htmlElement.setAttribute('data-bs-theme', newTheme);

        // Update icon
        const icon = themeToggle.querySelector('i');
        if (icon) {
            icon.className = newTheme === 'light' ? 'bi bi-moon-stars' : 'bi bi-sun';
        }

        // Save preference
        localStorage.setItem('theme', newTheme);
    });
}

// Initialize theme from localStorage
const savedTheme = localStorage.getItem('theme');
if (savedTheme) {
    htmlElement.setAttribute('data-bs-theme', savedTheme);
    const icon = themeToggle?.querySelector('i');
    if (icon) {
        icon.className = savedTheme === 'light' ? 'bi bi-moon-stars' : 'bi bi-sun';
    }
}


// Fullscreen Toggle
const fullscreenToggle = document.getElementById('fullscreenToggle');

fullscreenToggle.addEventListener('click', () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        fullscreenToggle.innerHTML = '<i class="fa-solid fa-compress"></i>';
    } else {
        document.exitFullscreen();
        fullscreenToggle.innerHTML = '<i class="fa-solid fa-expand"></i>';
    }
});

// Toast Notification Function
function showToast(message, type = 'success') {
    const toastEl = document.getElementById('toastAlert');
    const toastBody = toastEl.querySelector('.toast-body');

    toastBody.textContent = message;
    toastEl.className = `toast align-items-center text-bg-${type}`;

    const bsToast = new bootstrap.Toast(toastEl);
    bsToast.show();
}

// DataTable Initialization
$(document).ready(function () {
    if ($('#birthdayTable').length) {
        $('#birthdayTable').DataTable({
            responsive: true,
            dom: 'Bfrtip',
            buttons: ['excel', 'csv', 'pdf', 'print']
        });
    }

    // Summernote Initialization
    if ($('.summernote').length) {
        $('.summernote').summernote({
            height: 180,
            placeholder: 'Write your email template here...'
        });
    }
});
