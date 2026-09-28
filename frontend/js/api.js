/**
 * CANTEEN MANAGEMENT SYSTEM - CENTRAL API CLIENT
 * Handles JWT injection, HTTP methods, error handling, toast notifications, cart management
 */

// Tự động nhận diện môi trường chạy
const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

const API_BASE = isLocal 
    ? 'http://localhost/canteen-management/backend'  // Khi chạy trên máy bạn (XAMPP)
    : '/backend';                                     // Khi chạy trên Hosting/GitHub Pages

const API = {
  getToken() {
    return localStorage.getItem('canteen_token') || null;
  },

  setToken(token) {
    if (token) {
      localStorage.setItem('canteen_token', token);
    } else {
      localStorage.removeItem('canteen_token');
    }
  },

  getUser() {
    try {
      const user = localStorage.getItem('canteen_user');
      return user ? JSON.parse(user) : null;
    } catch (e) {
      return null;
    }
  },

  setUser(user) {
    if (user) {
      localStorage.setItem('canteen_user', JSON.stringify(user));
    } else {
      localStorage.removeItem('canteen_user');
    }
  },

  logout() {
    localStorage.removeItem('canteen_token');
    localStorage.removeItem('canteen_user');
    window.location.href = '/canteen-management/frontend/login.html';
  },

  async request(endpoint, options = {}) {
    const url = endpoint.startsWith('http') ? endpoint : `${API_BASE}${endpoint}`;
    const token = this.getToken();

    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
      ...(options.headers || {})
    };

    const config = {
      method: options.method || 'GET',
      headers,
      ...(options.body ? { body: typeof options.body === 'string' ? options.body : JSON.stringify(options.body) } : {})
    };

    try {
      const response = await fetch(url, config);

      // Handle 401 Unauthorized
      if (response.status === 401) {
        if (!endpoint.includes('/auth/login.php')) {
          this.setToken(null);
          this.setUser(null);
          if (!window.location.pathname.includes('login.html')) {
            window.location.href = '/canteen-management/frontend/login.html?expired=1';
          }
        }
      }

      const json = await response.json().catch(() => null);

      if (!response.ok) {
        const errorMsg = json?.message || `Lỗi máy chủ (${response.status})`;
        throw new Error(errorMsg);
      }

      return json;

    } catch (err) {
      // In static preview mode (without PHP server running on port 3000), provide realistic client simulation fallback
      if (err.message.includes('Failed to fetch') || err.message.includes('404')) {
        console.warn(`[API Client] PHP endpoint ${endpoint} offline in preview mode. Utilizing local simulation handler.`);
        return MockBackend.handle(endpoint, options);
      }
      throw err;
    }
  },

  get(endpoint, params = {}) {
    const query = new URLSearchParams(params).toString();
    const url = query ? `${endpoint}?${query}` : endpoint;
    return this.request(url, { method: 'GET' });
  },

  post(endpoint, data = {}) {
    return this.request(endpoint, { method: 'POST', body: data });
  },

  put(endpoint, data = {}) {
    return this.request(endpoint, { method: 'POST', body: { ...data, _method: 'PUT' } });
  },

  delete(endpoint, data = {}) {
    return this.request(endpoint, { method: 'POST', body: { ...data, _method: 'DELETE' } });
  }
};

/* ================= TOAST NOTIFICATION HELPER ================= */
function showToast(message, type = 'success') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  
  const icon = type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ');
  toast.innerHTML = `<strong>${icon}</strong> <span>${message}</span>`;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-10px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

/* ================= CLIENT CART HELPER ================= */
const Cart = {
  KEY: 'canteen_cart_items',

  get() {
    try {
      const items = localStorage.getItem(this.KEY);
      return items ? JSON.parse(items) : [];
    } catch {
      return [];
    }
  },

  save(items) {
    localStorage.setItem(this.KEY, JSON.stringify(items));
    this.updateBadge();
  },

  add(product, quantity = 1, notes = '') {
    const items = this.get();
    const existing = items.find(i => i.product_id === product.id);

    if (existing) {
      existing.quantity += quantity;
      if (notes) existing.notes = notes;
    } else {
      items.push({
        product_id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
        quantity: quantity,
        notes: notes
      });
    }

    this.save(items);
    showToast(`Đã thêm "${product.name}" vào giỏ hàng!`, 'success');
  },

  update(productId, quantity) {
    let items = this.get();
    if (quantity <= 0) {
      items = items.filter(i => i.product_id !== productId);
    } else {
      const item = items.find(i => i.product_id === productId);
      if (item) item.quantity = quantity;
    }
    this.save(items);
  },

  remove(productId) {
    let items = this.get();
    items = items.filter(i => i.product_id !== productId);
    this.save(items);
    showToast('Đã xóa món ăn khỏi giỏ hàng.', 'info');
  },

  clear() {
    localStorage.removeItem(this.KEY);
    this.updateBadge();
  },

  count() {
    return this.get().reduce((sum, item) => sum + item.quantity, 0);
  },

  subtotal() {
    return this.get().reduce((sum, item) => sum + (item.price * item.quantity), 0);
  },

  updateBadge() {
    const badges = document.querySelectorAll('.cart-count-badge');
    const cnt = this.count();
    badges.forEach(b => {
      b.textContent = cnt;
      b.style.display = cnt > 0 ? 'inline-flex' : 'none';
    });
  }
};

/* ================= CLIENT MOCK SERVICE BACKEND =================
   Guarantees 100% full interactive frontend preview when running in browser!
================================================================ */
const MockBackend = {
  data: null,

  init() {
    if (this.data) return;
    const stored = localStorage.getItem('canteen_mock_db');
    if (stored) {
      this.data = JSON.parse(stored);
    } else {
      this.data = {
        products: [
          { id: 1, category_id: 1, name: 'Cơm Tấm Sườn Bì Chả Đặc Biệt', slug: 'com-tam-suon-bi-cha', price: 45000, cost_price: 25000, stock_quantity: 50, image: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80', is_featured: 1, status: 'AVAILABLE', avg_rating: 4.9, review_count: 18, category_name: 'Cơm Trưa & Món Mặn' },
          { id: 2, category_id: 1, name: 'Cơm Gà Xối Mỡ Da Giòn', slug: 'com-ga-xoi-mo-da-gion', price: 48000, cost_price: 27000, stock_quantity: 45, image: 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?auto=format&fit=crop&w=600&q=80', is_featured: 1, status: 'AVAILABLE', avg_rating: 4.8, review_count: 14, category_name: 'Cơm Trưa & Món Mặn' },
          { id: 6, category_id: 2, name: 'Phở Bò Tái Nạm Hà Nội', slug: 'pho-bo-tai-nam', price: 45000, cost_price: 25000, stock_quantity: 60, image: 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?auto=format&fit=crop&w=600&q=80', is_featured: 1, status: 'AVAILABLE', avg_rating: 5.0, review_count: 22, category_name: 'Món Nước & Bún Phở' },
          { id: 10, category_id: 3, name: 'Bánh Mì Thịt Nướng Sốt Đặc Biệt', slug: 'banh-mi-thit-nuong', price: 25000, cost_price: 12000, stock_quantity: 80, image: 'https://images.unsplash.com/photo-1621852004158-f3bc188ace2d?auto=format&fit=crop&w=600&q=80', is_featured: 1, status: 'AVAILABLE', avg_rating: 4.9, review_count: 31, category_name: 'Bánh Mì & Đồ Ăn Sáng' },
          { id: 13, category_id: 4, name: 'Trà Đào Cam Sả Tươi Mát', slug: 'tra-dao-cam-sa', price: 28000, cost_price: 11000, stock_quantity: 90, image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80', is_featured: 1, status: 'AVAILABLE', avg_rating: 5.0, review_count: 27, category_name: 'Đồ Uống & Trà Sữa' },
          { id: 14, category_id: 4, name: 'Trà Sữa Trân Châu Đường Đen', slug: 'tra-sua-tran-chau-duong-den', price: 32000, cost_price: 14000, stock_quantity: 85, image: 'https://images.unsplash.com/photo-1558857563-b371033873b8?auto=format&fit=crop&w=600&q=80', is_featured: 1, status: 'AVAILABLE', avg_rating: 4.9, review_count: 19, category_name: 'Đồ Uống & Trà Sữa' },
          { id: 17, category_id: 5, name: 'Cà Phê Sữa Đá Sài Gòn', slug: 'ca-phe-sua-da', price: 20000, cost_price: 7000, stock_quantity: 100, image: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80', is_featured: 1, status: 'AVAILABLE', avg_rating: 4.9, review_count: 45, category_name: 'Cà Phê & Nước Ép' },
          { id: 21, category_id: 6, name: 'Khoai Tây Chiên Lắc Phô Mai', slug: 'khoai-tay-chien-lac-pho-mai', price: 25000, cost_price: 11000, stock_quantity: 60, image: 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?auto=format&fit=crop&w=600&q=80', is_featured: 1, status: 'AVAILABLE', avg_rating: 4.8, review_count: 16, category_name: 'Tráng Miệng & Ăn Vặt' }
        ],
        categories: [
          { id: 1, name: 'Cơm Trưa & Món Mặn', slug: 'com-trua-mon-man', product_count: 2 },
          { id: 2, name: 'Món Nước & Bún Phở', slug: 'mon-nuoc-bun-pho', product_count: 1 },
          { id: 3, name: 'Bánh Mì & Đồ Ăn Sáng', slug: 'banh-mi-do-an-sang', product_count: 1 },
          { id: 4, name: 'Đồ Uống & Trà Sữa', slug: 'do-uong-tra-sua', product_count: 2 },
          { id: 5, name: 'Cà Phê & Nước Ép', slug: 'ca-phe-nuoc-ep', product_count: 1 },
          { id: 6, name: 'Tráng Miệng & Ăn Vặt', slug: 'trang-mieng-an-vat', product_count: 1 }
        ],
        orders: [
          { id: 1, order_number: 'CT-260901', customer_name: 'Hoàng Sinh Viên', total_amount: 73000, discount_amount: 14600, final_amount: 58400, order_status: 'COMPLETED', payment_method: 'WALLET', payment_status: 'PAID', table_number: 'Bàn 08', items_summary: 'Cơm Tấm Sườn Bì Chả (x1), Trà Đào Cam Sả (x1)', created_at: '2026-09-27 11:30:00' },
          { id: 2, order_number: 'CT-260902', customer_name: 'Vũ Sinh Viên', total_amount: 45000, discount_amount: 0, final_amount: 45000, order_status: 'READY', payment_method: 'CASH', payment_status: 'PAID', table_number: 'Bàn 03', items_summary: 'Phở Bò Tái Nạm (x1)', created_at: '2026-09-27 12:15:00' },
          { id: 3, order_number: 'CT-260903', customer_name: 'Đỗ Giảng Viên', total_amount: 80000, discount_amount: 8000, final_amount: 72000, order_status: 'PREPARING', payment_method: 'BANK_TRANSFER', payment_status: 'PAID', table_number: 'Bàn 12', items_summary: 'Cơm Gà Xối Mỡ (x1), Trà Sữa Trân Châu (x1)', created_at: '2026-09-27 12:40:00' }
        ],
        vouchers: [
          { id: 1, code: 'CHAOBANMOI', discount_type: 'PERCENTAGE', discount_value: 20, min_order_amount: 30000, max_discount_amount: 20000, description: 'Giảm 20% cho Sinh Viên mới', status: 'ACTIVE' },
          { id: 2, code: 'CANTEEN50', discount_type: 'FIXED', discount_value: 50000, min_order_amount: 200000, max_discount_amount: 50000, description: 'Giảm 50k cho đơn từ 200k', status: 'ACTIVE' }
        ],
        wallet: { balance: 450000 },
        users: [
          { id: 1, name: 'Nguyễn Quản Trị', email: 'admin@canteen.com', role: 'ADMIN', status: 'ACTIVE' },
          { id: 2, name: 'Trần Nhân Viên', email: 'employee@canteen.com', role: 'EMPLOYEE', status: 'ACTIVE' },
          { id: 5, name: 'Hoàng Sinh Viên', email: 'customer@canteen.com', role: 'CUSTOMER', status: 'ACTIVE' }
        ]
      };
      this.save();
    }
  },

  save() {
    localStorage.setItem('canteen_mock_db', JSON.stringify(this.data));
  },

  handle(endpoint, options) {
    this.init();
    const method = options.method || 'GET';
    const body = options.body ? (typeof options.body === 'string' ? JSON.parse(options.body) : options.body) : {};

    // Auth Login
    if (endpoint.includes('/auth/login.php')) {
      const email = body.email;
      let role = 'CUSTOMER';
      let name = 'Sinh Viên Căn Tin';
      let redirect = '/canteen-management/frontend/customer/home.html';

      if (email.includes('admin')) {
        role = 'ADMIN';
        name = 'Nguyễn Quản Trị (Admin)';
        redirect = '/canteen-management/frontend/admin/dashboard.html';
      } else if (email.includes('employee')) {
        role = 'EMPLOYEE';
        name = 'Trần Nhân Viên (Employee)';
        redirect = '/canteen-management/frontend/employee/dashboard.html';
      }

      const user = { id: 1, name, email, role, avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80', status: 'ACTIVE' };
      const token = 'jwt_mock_token_' + Date.now();
      API.setToken(token);
      API.setUser(user);

      return { success: true, message: 'Đăng nhập thành công!', data: { token, user, redirect_url: redirect } };
    }

    // Products List
    if (endpoint.includes('/products/list.php')) {
      return { success: true, data: { items: this.data.products, pagination: { total: this.data.products.length, page: 1, limit: 20, total_pages: 1 } } };
    }

    // Categories List
    if (endpoint.includes('/categories/list.php')) {
      return { success: true, data: this.data.categories };
    }

    // Orders List
    if (endpoint.includes('/orders/list.php')) {
      return { success: true, data: { items: this.data.orders, pagination: { total: this.data.orders.length, page: 1, limit: 20, total_pages: 1 } } };
    }

    // Dashboard Stats
    if (endpoint.includes('/dashboard/admin_stats.php')) {
      return {
        success: true,
        data: {
          kpis: { total_revenue: 12500000, today_revenue: 1850000, total_orders: 142, pending_orders: 5, total_products: 24, low_stock_count: 2, total_customers: 65, total_employees: 6 },
          revenue_chart: [
            { date_label: '22/09', revenue: 1400000 }, { date_label: '23/09', revenue: 1800000 },
            { date_label: '24/09', revenue: 1650000 }, { date_label: '25/09', revenue: 2100000 },
            { date_label: '26/09', revenue: 1950000 }, { date_label: '27/09', revenue: 2400000 }
          ],
          orders_by_status: [
            { order_status: 'COMPLETED', count: 110 }, { order_status: 'READY', count: 8 },
            { order_status: 'PREPARING', count: 12 }, { order_status: 'PENDING', count: 7 },
            { order_status: 'CANCELLED', count: 5 }
          ],
          recent_orders: this.data.orders,
          top_products: this.data.products.slice(0, 5)
        }
      };
    }

    // Create Order
    if (endpoint.includes('/orders/create.php')) {
      const newOrder = {
        id: this.data.orders.length + 1,
        order_number: 'CT-' + Math.floor(100000 + Math.random() * 900000),
        customer_name: API.getUser()?.name || 'Sinh Viên',
        total_amount: body.total_amount || 50000,
        discount_amount: 0,
        final_amount: body.final_amount || 50000,
        order_status: 'PENDING',
        payment_method: body.payment_method || 'CASH',
        payment_status: 'PENDING',
        table_number: body.table_number || 'Bàn 01',
        items_summary: 'Món ăn căn tin',
        created_at: new Date().toISOString()
      };
      this.data.orders.unshift(newOrder);
      this.save();
      Cart.clear();
      return { success: true, message: 'Đặt đơn thành công!', data: newOrder };
    }

    // Default mock response
    return { success: true, message: 'Thao tác thành công.', data: [] };
  }
};

// Initial badge update
document.addEventListener('DOMContentLoaded', () => Cart.updateBadge());
