"use client"
import { useState, useEffect } from 'react';
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL!,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!
);

export default function App() {
  const [tab, setTab] = useState('order'); // order, list, inventory
  const [products, setProducts] = useState([]);
  const [cart, setCart] = useState([]);
  const [shopName, setShopName] = useState('');
  const [orders, setOrders] = useState([]);

  useEffect(() => {
    loadData();
  }, []);

  async function loadData() {
    const { data: p } = await supabase.from('products').select('*');
    const { data: o } = await supabase.from('orders').select('*').order('created_at', { ascending: false });
    setProducts(p || []);
    setOrders(o || []);
  }

  const addToCart = (p) => {
    const existing = cart.find(item => item.id === p.id);
    if (existing) {
      setCart(cart.map(item => item.id === p.id ? {...item, qty: item.qty + 1} : item));
    } else {
      setCart([...cart, {...p, qty: 1}]);
    }
  };

  const placeOrder = async () => {
    if (!shopName || cart.length === 0) return alert("দোকানের নাম ও পণ্য সিলেক্ট করুন");
    const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    
    const { data: newOrder } = await supabase.from('orders').insert([{ shop_name: shopName, total_amount: total }]).select();
    
    for (const item of cart) {
      await supabase.from('order_items').insert([{ order_id: newOrder[0].id, product_name: item.name, quantity: item.qty, price: item.price }]);
      await supabase.rpc('decrement_stock', { prod_name: item.name, qty: item.qty });
    }
    
    alert("অর্ডার সম্পন্ন হয়েছে!");
    setCart([]); setShopName(''); loadData();
  };

  return (
    <div className="min-h-screen bg-[#F8FAFC] pb-20 font-sans">
      {/* Header */}
      <div className="bg-[#0F172A] text-white p-6 rounded-b-3xl shadow-xl">
        <h1 className="text-2xl font-bold">ডিলার ম্যানেজমেন্ট</h1>
        <p className="text-slate-400 text-sm mt-1">প্রিমিয়াম সেলস সিস্টেম</p>
      </div>

      {/* Tabs */}
      <div className="flex justify-around p-4 gap-2">
        {['order', 'list', 'inventory'].map((t) => (
          <button key={t} onClick={() => setTab(t)} className={`flex-1 py-3 rounded-xl font-medium transition ${tab === t ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-slate-600 border'}`}>
            {t === 'order' ? 'অর্ডার কাটুন' : t === 'list' ? 'অর্ডার লিস্ট' : 'স্টক'}
          </button>
        ))}
      </div>

      <div className="px-4">
        {tab === 'order' && (
          <div className="space-y-4">
            <input className="w-full p-4 rounded-2xl border-none shadow-sm focus:ring-2 focus:ring-blue-500" placeholder="দোকানের নাম..." value={shopName} onChange={e => setShopName(e.target.value)} />
            <div className="grid grid-cols-2 gap-3">
              {products.map(p => (
                <div key={p.id} onClick={() => addToCart(p)} className="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 active:scale-95 transition">
                  <h3 className="font-bold text-slate-800">{p.name}</h3>
                  <p className="text-blue-600 font-bold">৳{p.price}</p>
                  <p className="text-xs text-slate-400 mt-1">স্টক: {p.stock}</p>
                </div>
              ))}
            </div>
            {cart.length > 0 && (
              <div className="fixed bottom-5 left-4 right-4 bg-white p-4 rounded-2xl shadow-2xl border flex justify-between items-center">
                <div>
                  <p className="text-xs text-slate-400">মোট আইটেম: {cart.length}</p>
                  <p className="text-lg font-bold text-slate-800">৳{cart.reduce((s, i) => s + (i.price*i.qty), 0)}</p>
                </div>
                <button onClick={placeOrder} className="bg-green-600 text-white px-8 py-3 rounded-xl font-bold">অর্ডার দিন</button>
              </div>
            )}
          </div>
        )}

        {tab === 'list' && (
          <div className="space-y-3">
            {orders.map(o => (
              <div key={o.id} className="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center">
                <div>
                  <h3 className="font-bold text-slate-800">{o.shop_name}</h3>
                  <p className="text-sm text-slate-400">{new Date(o.created_at).toLocaleDateString('bn-BD')}</p>
                  <p className="text-blue-600 font-bold mt-1">৳{o.total_amount}</p>
                </div>
                <button onClick={() => window.print()} className="bg-slate-100 text-slate-600 px-4 py-2 rounded-lg text-sm font-bold">প্রিন্ট</button>
              </div>
            ))}
          </div>
        )}

        {tab === 'inventory' && (
          <div className="bg-white rounded-2xl shadow-sm border overflow-hidden">
            <table className="w-full">
              <thead className="bg-slate-50 border-b text-left">
                <tr><th className="p-4">পণ্য</th><th className="p-4">স্টক</th><th className="p-4">দাম</th></tr>
              </thead>
              <tbody>
                {products.map(p => (
                  <tr key={p.id} className="border-b last:border-0">
                    <td className="p-4 font-medium">{p.name}</td>
                    <td className="p-4 text-orange-600 font-bold">{p.stock}</td>
                    <td className="p-4">৳{p.price}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
