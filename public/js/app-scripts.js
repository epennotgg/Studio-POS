// Main application JavaScript

// Toggle sidebar on mobile
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // Mobile: toggle open/close
        sidebar.classList.toggle('open');
    } else {
        // Desktop: toggle collapsed/expanded
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
        
        // Save preference to localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
        
        // Update toggle button icon - always show three lines (bars) icon
        const toggleBtn = sidebar.querySelector('.sidebar-toggle');
        if (toggleBtn) {
            toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
        }
    }
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const isMobile = window.innerWidth <= 768;
    
    // Check if the click is on a toggle button (or its children)
    const isToggleBtn = event.target.closest('.sidebar-toggle');
    
    if (isMobile && sidebar.classList.contains('open') && 
        !sidebar.contains(event.target) && 
        !isToggleBtn) {
        sidebar.classList.remove('open');
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    const isMobile = window.innerWidth <= 768;
    
    if (!isMobile) {
        sidebar.classList.remove('open');
    }
});

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = 'auto';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('open');
        document.body.style.overflow = 'auto';
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.open');
        modals.forEach(modal => {
            modal.classList.remove('open');
            document.body.style.overflow = 'auto';
        });
    }
});

// Theme toggle functionality
function toggleTheme() {
    const html = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle.querySelector('i');
    const themeText = themeToggle.querySelector('.theme-text');
    
    if (html.classList.contains('dark-mode')) {
        // Switch to light mode
        html.classList.remove('dark-mode');
        themeIcon.className = 'fas fa-moon';
        themeText.textContent = 'Dark Mode';
        
        // Save to localStorage
        localStorage.setItem('theme', 'light');
        // Save to cookie
        setCookie('theme', 'light', 365);
    } else {
        // Switch to dark mode
        html.classList.add('dark-mode');
        themeIcon.className = 'fas fa-sun';
        themeText.textContent = 'Light Mode';
        
        // Save to localStorage
        localStorage.setItem('theme', 'dark');
        // Save to cookie
        setCookie('theme', 'dark', 365);
    }
}

// Cookie helper functions
function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
}

function getCookie(name) {
    const nameEQ = name + '=';
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Initialize tooltips and other UI components
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
    
    // Restore sidebar state from localStorage
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const isMobile = window.innerWidth <= 768;
    
    if (!isMobile) {
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('collapsed');
            
            // Update toggle button icon - always show three lines (bars) icon
            const toggleBtn = sidebar.querySelector('.sidebar-toggle');
            if (toggleBtn) {
                toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
            }
        }
    }
    
    // Apply dark mode if saved in cookie or localStorage
    const savedTheme = getCookie('theme') || localStorage.getItem('theme') || 'light';
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-mode');
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            const themeIcon = themeToggle.querySelector('i');
            const themeText = themeToggle.querySelector('.theme-text');
            themeIcon.className = 'fas fa-sun';
            themeText.textContent = 'Light Mode';
        }
    }
    
    // Add event listener to theme toggle button
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
    
    // Add tooltips for collapsed sidebar items
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            if (sidebar.classList.contains('collapsed')) {
                const tooltip = document.createElement('div');
                tooltip.className = 'sidebar-tooltip';
                tooltip.textContent = this.querySelector('.nav-text').textContent;
                tooltip.style.position = 'absolute';
                tooltip.style.left = '70px';
                tooltip.style.top = this.offsetTop + 'px';
                tooltip.style.background = '#1e40af';
                tooltip.style.color = 'white';
                tooltip.style.padding = '8px 12px';
                tooltip.style.borderRadius = '4px';
                tooltip.style.zIndex = '1001';
                tooltip.style.whiteSpace = 'nowrap';
                tooltip.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
                
                sidebar.appendChild(tooltip);
                
                this.addEventListener('mouseleave', function() {
                    if (tooltip.parentNode) {
                        tooltip.parentNode.removeChild(tooltip);
                    }
                }, { once: true });
            }
        });
    });
});