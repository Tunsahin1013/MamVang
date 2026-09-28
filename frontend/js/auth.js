/**
 * CANTEEN MANAGEMENT SYSTEM - FRONTEND AUTHENTICATION & ROLE GUARDS
 */

const AuthGuard = {
  checkAccess(requiredRoles = []) {
    const token = API.getToken();
    const user = API.getUser();

    const currentPath = window.location.pathname;

    // Public pages
    if (currentPath.includes('login.html') || currentPath.includes('register.html') || currentPath.includes('forgot-password.html')) {
      if (token && user) {
        this.redirectByRole(user.role);
      }
      return;
    }

    // Protected pages check
    if (!token || !user) {
      window.location.href = 'login.html?redirect=' + encodeURIComponent(currentPath);
      return;
    }

    // Role verification
    if (requiredRoles.length > 0 && !requiredRoles.includes(user.role)) {
      window.location.href = '403.html';
      return;
    }

    this.renderUserInfo(user);
  },

  redirectByRole(role) {
    switch (role) {
      case 'ADMIN':
        window.location.href = 'admin/dashboard.html';
        break;
      case 'EMPLOYEE':
        window.location.href = 'employee/dashboard.html';
        break;
      default:
        window.location.href = 'customer/home.html';
        break;
    }
  },

  renderUserInfo(user) {
    document.querySelectorAll('.auth-user-name').forEach(el => el.textContent = user.name || 'Người Dùng');
    document.querySelectorAll('.auth-user-email').forEach(el => el.textContent = user.email || '');
    document.querySelectorAll('.auth-user-role').forEach(el => el.textContent = user.role || '');
    document.querySelectorAll('.auth-user-avatar').forEach(el => {
      if (el.tagName === 'IMG') {
        el.src = user.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&q=80';
      }
    });

    // Points and wallet balance
    if (user.points !== undefined) {
      document.querySelectorAll('.auth-user-points').forEach(el => el.textContent = user.points);
    }
    if (user.wallet_balance !== undefined) {
      document.querySelectorAll('.auth-user-balance').forEach(el => {
        el.textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(user.wallet_balance);
      });
    }
  }
};

/* ================= DARK MODE TOGGLE HELPER ================= */
const ThemeManager = {
  init() {
    const saved = localStorage.getItem('canteen_theme') || 'light';
    this.set(saved);
  },

  set(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('canteen_theme', theme);
    const togglers = document.querySelectorAll('.theme-toggle-btn');
    togglers.forEach(btn => {
      btn.textContent = theme === 'dark' ? '☀️' : '🌙';
    });
  },

  toggle() {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    this.set(next);
  }
};

document.addEventListener('DOMContentLoaded', () => {
  ThemeManager.init();
});
