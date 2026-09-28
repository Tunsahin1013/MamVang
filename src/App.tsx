/**
 * CANTEEN MANAGEMENT SYSTEM - INTERACTIVE RUNTIME & PORTAL PREVIEW
 * Mirrors complete system functionality for live testing in AI Studio preview.
 */

import { useState } from 'react';
import { 
  Utensils, 
  ChefHat, 
  ShieldCheck, 
  Code, 
  BookOpen, 
  Database as DbIcon, 
  ShoppingCart, 
  Plus, 
  Trash2, 
  Wallet, 
  Tag, 
  Clock, 
  TrendingUp,
  CheckCircle,
  ExternalLink
} from 'lucide-react';

interface Product {
  id: number;
  name: string;
  category: string;
  price: number;
  stock: number;
  image: string;
  rating: number;
  reviews: number;
}

interface CartItem extends Product {
  quantity: number;
}

interface Order {
  id: string;
  customer: string;
  items: string;
  total: number;
  status: 'PENDING' | 'CONFIRMED' | 'PREPARING' | 'READY' | 'COMPLETED' | 'CANCELLED';
  table: string;
  time: string;
}

const INITIAL_PRODUCTS: Product[] = [
  { id: 1, name: 'Cơm Tấm Sườn Bì Chả Đặc Biệt', category: 'Cơm Trưa & Món Mặn', price: 45000, stock: 50, image: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80', rating: 4.9, reviews: 18 },
  { id: 2, name: 'Cơm Gà Xối Mỡ Da Giòn', category: 'Cơm Trưa & Món Mặn', price: 48000, stock: 45, image: 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?auto=format&fit=crop&w=600&q=80', rating: 4.8, reviews: 14 },
  { id: 6, name: 'Phở Bò Tái Nạm Hà Nội', category: 'Món Nước & Bún Phở', price: 45000, stock: 60, image: 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?auto=format&fit=crop&w=600&q=80', rating: 5.0, reviews: 22 },
  { id: 10, name: 'Bánh Mì Thịt Nướng Sốt Đặc Biệt', category: 'Bánh Mì & Đồ Ăn Sáng', price: 25000, stock: 80, image: 'https://images.unsplash.com/photo-1621852004158-f3bc188ace2d?auto=format&fit=crop&w=600&q=80', rating: 4.9, reviews: 31 },
  { id: 13, name: 'Trà Đào Cam Sả Tươi Mát', category: 'Đồ Uống & Trà Sữa', price: 28000, stock: 90, image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80', rating: 5.0, reviews: 27 },
  { id: 14, name: 'Trà Sữa Trân Châu Đường Đen', category: 'Đồ Uống & Trà Sữa', price: 32000, stock: 85, image: 'https://images.unsplash.com/photo-1558857563-b371033873b8?auto=format&fit=crop&w=600&q=80', rating: 4.9, reviews: 19 },
  { id: 17, name: 'Cà Phê Sữa Đá Sài Gòn', category: 'Cà Phê & Nước Ép', price: 20000, stock: 100, image: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80', rating: 4.9, reviews: 45 },
  { id: 21, name: 'Khoai Tây Chiên Lắc Phô Mai', category: 'Tráng Miệng & Ăn Vặt', price: 25000, stock: 60, image: 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?auto=format&fit=crop&w=600&q=80', rating: 4.8, reviews: 16 }
];

export default function App() {
  const [activeTab, setActiveTab] = useState<'customer' | 'employee' | 'admin' | 'code' | 'docs'>('customer');
  const [products] = useState<Product[]>(INITIAL_PRODUCTS);
  const [cart, setCart] = useState<CartItem[]>([]);
  const [voucherCode, setVoucherCode] = useState('');
  const [discount, setDiscount] = useState(0);
  const [walletBalance, setWalletBalance] = useState(450000);
  const [checkedIn, setCheckedIn] = useState(true);

  const [orders, setOrders] = useState<Order[]>([
    { id: 'CT-260901', customer: 'Hoàng Sinh Viên', items: 'Cơm Tấm Sườn Bì Chả (x1), Trà Đào (x1)', total: 58400, status: 'COMPLETED', table: 'Bàn 08', time: '11:30' },
    { id: 'CT-260902', customer: 'Vũ Sinh Viên', items: 'Phở Bò Tái Nạm (x1)', total: 45000, status: 'READY', table: 'Bàn 03', time: '12:15' },
    { id: 'CT-260903', customer: 'Đỗ Giảng Viên', items: 'Cơm Gà Xối Mỡ (x1), Trà Sữa (x1)', total: 72000, status: 'PREPARING', table: 'Bàn 12', time: '12:40' },
    { id: 'CT-260904', customer: 'Bùi Văn Nam', items: 'Bánh Mì Thịt Nướng (x2)', total: 50000, status: 'PENDING', table: 'Mang đi', time: '12:55' }
  ]);

  const addToCart = (p: Product) => {
    setCart(prev => {
      const exist = prev.find(item => item.id === p.id);
      if (exist) {
        return prev.map(item => item.id === p.id ? { ...item, quantity: item.quantity + 1 } : item);
      }
      return [...prev, { ...p, quantity: 1 }];
    });
  };

  const removeFromCart = (id: number) => {
    setCart(prev => prev.filter(i => i.id !== id));
  };

  const subtotal = cart.reduce((sum, i) => sum + (i.price * i.quantity), 0);
  const finalTotal = Math.max(0, subtotal - discount);

  const applyVoucher = () => {
    if (voucherCode.trim().toUpperCase() === 'CHAOBANMOI') {
      const disc = Math.round(subtotal * 0.2);
      setDiscount(disc);
      alert(`✓ Áp dụng mã CHAOBANMOI thành công! Giảm ${disc.toLocaleString('vi-VN')}đ (20%)`);
    } else {
      alert('Mã không hợp lệ! Hãy thử nhập mã: CHAOBANMOI');
    }
  };

  const handleCheckout = () => {
    if (cart.length === 0) return;
    if (walletBalance < finalTotal) {
      alert('Số dư ví không đủ! Đã chuyển sang thanh toán tiền mặt.');
    } else {
      setWalletBalance(prev => prev - finalTotal);
    }

    const newOrder: Order = {
      id: `CT-${Math.floor(100000 + Math.random() * 900000)}`,
      customer: 'Hoàng Sinh Viên (Tài khoản mẫu)',
      items: cart.map(i => `${i.name} (x${i.quantity})`).join(', '),
      total: finalTotal,
      status: 'PENDING',
      table: 'Bàn 05',
      time: new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
    };

    setOrders(prev => [newOrder, ...prev]);
    setCart([]);
    setDiscount(0);
    setVoucherCode('');
    alert(`🎉 Đặt đơn #${newOrder.id} thành công! Đã gửi đến nhà bếp căn tin.`);
  };

  const updateOrderStatus = (id: string, newStatus: Order['status']) => {
    setOrders(prev => prev.map(o => o.id === id ? { ...o, status: newStatus } : o));
  };

  return (
    <div className="min-h-screen bg-slate-50 text-slate-900 flex flex-col font-sans">
      {/* Header */}
      <header className="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div className="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <span className="text-3xl">🍜</span>
            <div>
              <span className="font-extrabold text-xl tracking-tight text-orange-600 block">Mâm Vàng</span>
              <span className="text-xs text-slate-500 font-medium">PHP 8.x • MySQL PDO • JWT • Pure Vanilla</span>
            </div>
          </div>

          {/* Module Selector Tabs */}
          <div className="flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
            <button 
              onClick={() => setActiveTab('customer')}
              className={`flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-bold transition-all ${
                activeTab === 'customer' ? 'bg-orange-500 text-white shadow' : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              <Utensils size={16} /> Sinh Viên
            </button>
            <button 
              onClick={() => setActiveTab('employee')}
              className={`flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-bold transition-all ${
                activeTab === 'employee' ? 'bg-purple-600 text-white shadow' : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              <ChefHat size={16} /> Nhân Viên POS
            </button>
            <button 
              onClick={() => setActiveTab('admin')}
              className={`flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-bold transition-all ${
                activeTab === 'admin' ? 'bg-sky-600 text-white shadow' : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              <ShieldCheck size={16} /> Quản Trị Admin
            </button>
            <button 
              onClick={() => setActiveTab('code')}
              className={`flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-bold transition-all ${
                activeTab === 'code' ? 'bg-slate-800 text-white shadow' : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              <Code size={16} /> Backend & SQL
            </button>
            <button 
              onClick={() => setActiveTab('docs')}
              className={`flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-bold transition-all ${
                activeTab === 'docs' ? 'bg-emerald-600 text-white shadow' : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              <BookOpen size={16} /> API Docs
            </button>
          </div>

          {/* Quick link to raw frontend */}
          <div className="flex items-center gap-2">
            <a 
              href="/canteen-management/frontend/index.html" 
              target="_blank"
              rel="noreferrer"
              className="text-xs font-semibold text-orange-600 flex items-center gap-1 hover:underline bg-orange-50 px-2.5 py-1.5 rounded-md border border-orange-200"
            >
              Mở Frontend Gốc <ExternalLink size={12} />
            </a>
          </div>
        </div>
      </header>

      {/* Main View Area */}
      <main className="max-w-7xl mx-auto px-4 py-6 flex-grow w-full">
        {/* ================= CUSTOMER PORTAL TAB ================= */}
        {activeTab === 'customer' && (
          <div className="space-y-6">
            {/* Top User Bar */}
            <div className="bg-white border border-slate-200 rounded-2xl p-4 flex items-center justify-between shadow-sm">
              <div className="flex items-center gap-3">
                <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=120&q=80" alt="avatar" className="w-10 h-10 rounded-full object-cover border-2 border-orange-500" />
                <div>
                  <h3 className="font-bold text-sm">Hoàng Sinh Viên</h3>
                  <span className="text-xs text-slate-500">customer@canteen.com • Điểm tích lũy: 280 pts</span>
                </div>
              </div>

              <div className="flex items-center gap-4">
                <div className="flex items-center gap-2 bg-emerald-50 text-emerald-800 px-3 py-1.5 rounded-xl border border-emerald-200 text-sm font-bold">
                  <Wallet size={16} /> Ví Căn Tin: {walletBalance.toLocaleString('vi-VN')}đ
                  <button 
                    onClick={() => { setWalletBalance(b => b + 100000); alert('Đã nạp +100.000đ vào ví!'); }}
                    className="ml-2 text-xs bg-emerald-600 text-white px-2 py-0.5 rounded hover:bg-emerald-700"
                  >
                    + Nạp 100k
                  </button>
                </div>
              </div>
            </div>

            {/* Menu Grid & Cart Columns */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              {/* Product list (2 cols) */}
              <div className="lg:col-span-2 space-y-4">
                <div className="flex items-center justify-between">
                  <h2 className="text-xl font-extrabold text-slate-900">Thực Đơn Căn Tin Hôm Nay</h2>
                  <span className="text-xs bg-orange-100 text-orange-800 font-bold px-2.5 py-1 rounded-full">
                    {products.length} Món phục vụ
                  </span>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {products.map(p => (
                    <div key={p.id} className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col">
                      <div className="h-44 overflow-hidden relative">
                        <img src={p.image} alt={p.name} className="w-full h-full object-cover hover:scale-105 transition-transform duration-300" />
                        <span className="absolute top-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded-full backdrop-blur-sm">
                          ⭐ {p.rating} ({p.reviews})
                        </span>
                        <span className="absolute top-2 right-2 bg-slate-900/70 text-white text-xs px-2 py-1 rounded-full backdrop-blur-sm">
                          Còn {p.stock} phần
                        </span>
                      </div>
                      <div className="p-4 flex flex-col flex-grow">
                        <span className="text-xs font-semibold text-slate-400 mb-1">{p.category}</span>
                        <h4 className="font-bold text-base text-slate-900 line-clamp-1 mb-2">{p.name}</h4>
                        <div className="mt-auto flex items-center justify-between pt-2 border-t border-slate-100">
                          <span className="text-lg font-extrabold text-orange-600">{p.price.toLocaleString('vi-VN')}đ</span>
                          <button 
                            onClick={() => addToCart(p)}
                            className="bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg flex items-center gap-1 shadow-sm transition-colors"
                          >
                            <Plus size={14} /> Thêm món
                          </button>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Cart Drawer (1 col) */}
              <div className="space-y-4">
                <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm sticky top-24">
                  <div className="flex items-center justify-between pb-3 border-b border-slate-200">
                    <h3 className="font-extrabold text-base flex items-center gap-2">
                      <ShoppingCart size={18} className="text-orange-500" /> Giỏ Hàng Của Bạn
                    </h3>
                    <span className="text-xs font-bold text-slate-500">{cart.reduce((s, i) => s + i.quantity, 0)} Món</span>
                  </div>

                  {cart.length === 0 ? (
                    <div className="py-12 text-center text-slate-400 text-sm">
                      <ShoppingCart size={40} className="mx-auto text-slate-300 mb-2" />
                      Giỏ hàng đang trống. Hãy chọn món ăn bên trái!
                    </div>
                  ) : (
                    <div className="space-y-3 my-4 max-h-72 overflow-y-auto pr-1">
                      {cart.map(item => (
                        <div key={item.id} className="flex items-center justify-between text-sm py-1.5 border-b border-slate-100">
                          <div className="flex-1 pr-2">
                            <span className="font-bold text-slate-900 block line-clamp-1">{item.name}</span>
                            <span className="text-xs text-slate-500">{item.price.toLocaleString('vi-VN')}đ × {item.quantity}</span>
                          </div>
                          <span className="font-bold text-orange-600 mr-2">{(item.price * item.quantity).toLocaleString('vi-VN')}đ</span>
                          <button onClick={() => removeFromCart(item.id)} className="text-slate-400 hover:text-red-500 p-1">
                            <Trash2 size={15} />
                          </button>
                        </div>
                      ))}
                    </div>
                  )}

                  {/* Voucher Apply */}
                  <div className="pt-2 border-t border-slate-200 space-y-2">
                    <div className="flex gap-2">
                      <input 
                        type="text" 
                        value={voucherCode} 
                        onChange={e => setVoucherCode(e.target.value)}
                        placeholder="Mã voucher: CHAOBANMOI" 
                        className="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-mono uppercase"
                      />
                      <button onClick={applyVoucher} className="bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold px-3 py-1.5 rounded-lg">
                        Áp Dụng
                      </button>
                    </div>

                    <div className="space-y-1 text-xs text-slate-600 pt-2">
                      <div className="flex justify-between">
                        <span>Tạm tính:</span>
                        <span className="font-semibold">{subtotal.toLocaleString('vi-VN')}đ</span>
                      </div>
                      {discount > 0 && (
                        <div className="flex justify-between text-emerald-600 font-bold">
                          <span>Giảm voucher (20%):</span>
                          <span>-{discount.toLocaleString('vi-VN')}đ</span>
                        </div>
                      )}
                      <div className="flex justify-between text-base font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                        <span>Tổng thanh toán:</span>
                        <span className="text-orange-600">{finalTotal.toLocaleString('vi-VN')}đ</span>
                      </div>
                    </div>

                    <button 
                      onClick={handleCheckout}
                      disabled={cart.length === 0}
                      className="w-full bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white font-extrabold py-2.5 rounded-xl text-sm shadow-md transition-all mt-3"
                    >
                      Đặt Món Ngay (Trừ Ví / Tiền Mặt)
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* ================= EMPLOYEE POS TAB ================= */}
        {activeTab === 'employee' && (
          <div className="space-y-6">
            {/* Shift banner */}
            <div className="bg-purple-900 text-white rounded-2xl p-5 shadow flex items-center justify-between">
              <div>
                <span className="bg-purple-800 text-purple-200 text-xs px-2.5 py-1 rounded-full font-bold">CA LÀM VIỆC HIỆN TẠI</span>
                <h3 className="text-xl font-bold mt-1">Ca Trưa: 11:00 - 16:30 (Trưởng ca: Trần Nhân Viên)</h3>
                <p className="text-xs text-purple-300 mt-0.5">Trạng thái: {checkedIn ? '● Đã Check-in đúng giờ lúc 10:55' : '○ Chưa điểm danh'}</p>
              </div>

              <div className="flex gap-2">
                <button 
                  onClick={() => { setCheckedIn(true); alert('Check-in thành công!'); }}
                  className="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-3 py-2 rounded-lg"
                >
                  🟢 Check-in
                </button>
                <button 
                  onClick={() => { setCheckedIn(false); alert('Check-out thành công! Tổng thời gian làm: 5.5 giờ.'); }}
                  className="bg-purple-800 hover:bg-purple-700 text-white text-xs font-bold px-3 py-2 rounded-lg border border-purple-700"
                >
                  🔴 Check-out
                </button>
              </div>
            </div>

            {/* Orders Management */}
            <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
              <div className="flex items-center justify-between">
                <div>
                  <h3 className="text-lg font-extrabold text-slate-900">Điều Phối Đơn Hàng & Chế Biến Món Ăn</h3>
                  <p className="text-xs text-slate-500">Chuyển trạng thái để gửi thông báo thời gian thực đến Sinh Viên</p>
                </div>
                <span className="text-xs bg-purple-100 text-purple-800 font-bold px-3 py-1 rounded-full">
                  {orders.length} Đơn trong ngày
                </span>
              </div>

              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm">
                  <thead>
                    <tr className="bg-slate-50 text-slate-500 uppercase text-xs border-b border-slate-200">
                      <th className="p-3 font-bold">Mã Đơn</th>
                      <th className="p-3 font-bold">Thời Gian</th>
                      <th className="p-3 font-bold">Sinh Viên</th>
                      <th className="p-3 font-bold">Chi Tiết Món</th>
                      <th className="p-3 font-bold">Bàn / Vị Trí</th>
                      <th className="p-3 font-bold">Số Tiền</th>
                      <th className="p-3 font-bold">Trạng Thái</th>
                      <th className="p-3 font-bold text-right">Chuyển Trạng Thái</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {orders.map(o => (
                      <tr key={o.id} className="hover:bg-slate-50">
                        <td className="p-3 font-extrabold text-slate-900">{o.id}</td>
                        <td className="p-3 text-xs text-slate-500">{o.time}</td>
                        <td className="p-3 font-semibold">{o.customer}</td>
                        <td className="p-3 text-xs text-slate-600 max-w-xs">{o.items}</td>
                        <td className="p-3"><span className="text-xs bg-slate-100 px-2 py-0.5 rounded font-mono">{o.table}</span></td>
                        <td className="p-3 font-extrabold text-orange-600">{o.total.toLocaleString('vi-VN')}đ</td>
                        <td className="p-3">
                          <span className={`text-xs px-2.5 py-1 rounded-full font-bold ${
                            o.status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' :
                            o.status === 'READY' ? 'bg-teal-100 text-teal-800' :
                            o.status === 'PREPARING' ? 'bg-purple-100 text-purple-800' :
                            o.status === 'CONFIRMED' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800'
                          }`}>
                            {o.status}
                          </span>
                        </td>
                        <td className="p-3 text-right">
                          <select 
                            value={o.status} 
                            onChange={e => updateOrderStatus(o.id, e.target.value as Order['status'])}
                            className="text-xs bg-white border border-slate-300 rounded px-2 py-1 font-semibold"
                          >
                            <option value="PENDING">Chờ duyệt (PENDING)</option>
                            <option value="CONFIRMED">Xác nhận (CONFIRMED)</option>
                            <option value="PREPARING">Đang nấu (PREPARING)</option>
                            <option value="READY">Xong món (READY)</option>
                            <option value="COMPLETED">Đã giao (COMPLETED)</option>
                            <option value="CANCELLED">Hủy (CANCELLED)</option>
                          </select>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}

        {/* ================= ADMIN DASHBOARD TAB ================= */}
        {activeTab === 'admin' && (
          <div className="space-y-6">
            {/* KPI Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <span className="text-xs text-slate-500 font-bold uppercase tracking-wider">Doanh Thu Thuần</span>
                <div className="text-2xl font-black text-orange-600 mt-1">12.500.000đ</div>
                <span className="text-xs text-emerald-600 font-semibold flex items-center gap-1 mt-1">
                  <TrendingUp size={12} /> +18.5% so với tuần trước
                </span>
              </div>

              <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <span className="text-xs text-slate-500 font-bold uppercase tracking-wider">Tổng Đơn Hàng</span>
                <div className="text-2xl font-black text-sky-600 mt-1">142 Đơn</div>
                <span className="text-xs text-slate-500 font-medium">Hoàn tất: 128 • Hủy: 4</span>
              </div>

              <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <span className="text-xs text-slate-500 font-bold uppercase tracking-wider">Món Ăn & Tồn Kho</span>
                <div className="text-2xl font-black text-emerald-600 mt-1">24 Món</div>
                <span className="text-xs text-amber-600 font-semibold">Cảnh báo sắp hết: 2 món</span>
              </div>

              <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <span className="text-xs text-slate-500 font-bold uppercase tracking-wider">Sinh Viên Đăng Ký</span>
                <div className="text-2xl font-black text-purple-600 mt-1">65 Tài Khoản</div>
                <span className="text-xs text-slate-500 font-medium">Nhân viên: 6 tài khoản</span>
              </div>
            </div>

            {/* Quick Admin Actions */}
            <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
              <h3 className="font-extrabold text-base mb-3">Các Mô-đun Quản Trị Hệ Thống</h3>
              <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <a href="/canteen-management/frontend/admin/orders.html" target="_blank" className="p-3 bg-slate-50 hover:bg-orange-50 border border-slate-200 hover:border-orange-300 rounded-xl text-center text-xs font-bold transition-all block">
                  📋 Đơn Hàng
                </a>
                <a href="/canteen-management/frontend/admin/products.html" target="_blank" className="p-3 bg-slate-50 hover:bg-orange-50 border border-slate-200 hover:border-orange-300 rounded-xl text-center text-xs font-bold transition-all block">
                  🍲 Món Ăn
                </a>
                <a href="/canteen-management/frontend/admin/categories.html" target="_blank" className="p-3 bg-slate-50 hover:bg-orange-50 border border-slate-200 hover:border-orange-300 rounded-xl text-center text-xs font-bold transition-all block">
                  🏷️ Danh Mục
                </a>
                <a href="/canteen-management/frontend/admin/inventory.html" target="_blank" className="p-3 bg-slate-50 hover:bg-orange-50 border border-slate-200 hover:border-orange-300 rounded-xl text-center text-xs font-bold transition-all block">
                  📦 Kho Hàng
                </a>
                <a href="/canteen-management/frontend/admin/vouchers.html" target="_blank" className="p-3 bg-slate-50 hover:bg-orange-50 border border-slate-200 hover:border-orange-300 rounded-xl text-center text-xs font-bold transition-all block">
                  🎁 Mã Voucher
                </a>
                <a href="/canteen-management/frontend/admin/reports.html" target="_blank" className="p-3 bg-slate-50 hover:bg-orange-50 border border-slate-200 hover:border-orange-300 rounded-xl text-center text-xs font-bold transition-all block">
                  📈 Báo Cáo Thu
                </a>
              </div>
            </div>
          </div>
        )}

        {/* ================= CODE EXPLORER TAB ================= */}
        {activeTab === 'code' && (
          <div className="space-y-4">
            <div className="bg-slate-900 text-white rounded-2xl p-6 shadow-lg">
              <div className="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
                <div className="flex items-center gap-2">
                  <DbIcon className="text-orange-400" size={20} />
                  <h3 className="font-extrabold text-base">Cấu Trúc Source Code PHP 8.x + MySQL Đã Triển Khai</h3>
                </div>
                <span className="text-xs bg-slate-800 text-slate-300 px-3 py-1 rounded-full font-mono">
                  canteen_management (22 Bảng)
                </span>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
                <div className="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-2">
                  <strong className="text-orange-400 block text-sm">📁 Database Scripts (/database/)</strong>
                  <p className="text-slate-400">• <span className="text-slate-200">create_database.sql</span>: Tạo database utf8mb4</p>
                  <p className="text-slate-400">• <span className="text-slate-200">create_tables.sql</span>: 22 bảng có Khóa ngoại, Index</p>
                  <p className="text-slate-400">• <span className="text-slate-200">insert_data.sql</span>: 25+ món ăn, tài khoản mật khẩu bcrypt</p>
                  <p className="text-slate-400">• <span className="text-slate-200">seed.php</span>: Tự động chạy tạo CSDL qua PDO</p>
                </div>

                <div className="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-2">
                  <strong className="text-sky-400 block text-sm">📁 Backend REST API (/backend/)</strong>
                  <p className="text-slate-400">• <span className="text-slate-200">config/database.php</span>: PDO MySQL Singleton</p>
                  <p className="text-slate-400">• <span className="text-slate-200">config/jwt.php</span>: Mã hóa JWT HS256 thuần PHP</p>
                  <p className="text-slate-400">• <span className="text-slate-200">middleware/auth.php</span>: Kiểm tra Role (ADMIN/EMP/CUST)</p>
                  <p className="text-slate-400">• <span className="text-slate-200">orders/create.php</span>: ACID Transaction (Khóa tồn kho)</p>
                </div>
              </div>

              <div className="mt-4 p-3 bg-slate-800/60 rounded-xl text-xs text-slate-300">
                💡 <strong>Cách chuyển sang XAMPP:</strong> Copy thư mục dự án vào <code>C:\xampp\htdocs\canteen-management</code> và truy cập <code>http://localhost/canteen-management/database/seed.php</code> để chạy khởi tạo CSDL một chạm.
              </div>
            </div>
          </div>
        )}

        {/* ================= DOCS TAB ================= */}
        {activeTab === 'docs' && (
          <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4 text-sm leading-relaxed">
            <h2 className="text-xl font-extrabold text-slate-900 flex items-center gap-2">
              <BookOpen className="text-orange-500" /> Đặc Tả REST API & Hướng Dẫn Vận Hành
            </h2>

            <div className="p-4 bg-orange-50 border border-orange-200 rounded-xl">
              <h4 className="font-bold text-orange-900 mb-1">Tài Khoản Thử Nghiệm:</h4>
              <ul className="list-disc list-inside space-y-1 text-orange-800 font-mono text-xs">
                <li>ADMIN: <strong>admin@canteen.com</strong> / Mật khẩu: <strong>Admin@123</strong></li>
                <li>EMPLOYEE: <strong>employee@canteen.com</strong> / Mật khẩu: <strong>Employee@123</strong></li>
                <li>CUSTOMER: <strong>customer@canteen.com</strong> / Mật khẩu: <strong>Customer@123</strong></li>
              </ul>
            </div>

            <div className="space-y-2">
              <h3 className="font-extrabold text-base text-slate-900">Danh Sách Endpoint Chính:</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs font-mono">
                <div className="p-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                  <span className="text-blue-600 font-bold">POST</span> /backend/auth/login.php
                </div>
                <div className="p-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                  <span className="text-emerald-600 font-bold">GET</span> /backend/products/list.php
                </div>
                <div className="p-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                  <span className="text-blue-600 font-bold">POST</span> /backend/orders/create.php
                </div>
                <div className="p-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                  <span className="text-amber-600 font-bold">POST</span> /backend/orders/update_status.php
                </div>
                <div className="p-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                  <span className="text-emerald-600 font-bold">GET</span> /backend/inventory/list.php
                </div>
                <div className="p-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                  <span className="text-blue-600 font-bold">POST</span> /backend/attendance/check_in.php
                </div>
              </div>
            </div>

            <p className="text-xs text-slate-500 pt-2 border-t border-slate-100">
              Chi tiết đầy đủ các payload, status codes và schema nằm tại file <code>API_DOCUMENTATION.md</code> và hướng dẫn cài đặt tại <code>README.md</code>.
            </p>
          </div>
        )}
      </main>

      {/* Footer */}
      <footer className="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        Canteen Management System © 2026 • PHP 8.x + MySQL + HTML5/CSS3/Vanilla JS ES6+ • XAMPP / Apache Ready
      </footer>
    </div>
  );
}
