// Check if user is authenticated
function checkAuth() {
    console.log('Checking authentication...');
    fetch('../backend/check_auth.php', {
        credentials: 'include'
    })
        .then(response => {
            console.log('Auth response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Auth data:', data);
            if (!data.authenticated) {
                console.log('Not authenticated, redirecting to login...');
                window.location.href = 'login.html';
            } else {
                console.log('Authenticated! User ID:', data.user_id);
            }
        })
        .catch((error) => {
            console.error('Auth check error:', error);
            window.location.href = 'login.html';
        });
}

// Logout function
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        fetch('../backend/logout_handler.php', {
            credentials: 'include'
        })
            .then(response => response.json())
            .then(data => {
                window.location.href = 'login.html';
            });
    }
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('en-US', options);
}