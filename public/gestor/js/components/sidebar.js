document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const body = document.body;

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            body.classList.toggle('sidebar-collapsed');
            
            // Trigger a custom event for charts to resize
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 300); // 300ms matches CSS transition
        });
    }
});