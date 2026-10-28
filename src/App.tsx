import React, { useState } from 'react';
import { Header } from './components/Header';
import { Storefront } from './components/Storefront';
import { AdminPanel } from './components/AdminPanel';
import { DevOpsHub } from './components/DevOpsHub';
import { ProductDetailModal } from './components/ProductDetailModal';
import { CartDrawer } from './components/CartDrawer';
import { INITIAL_SHOES, INITIAL_CATEGORIES } from './data/initialData';
import { Shoe, Category, CartItem } from './types';
import { Cloud, Server, Database, CheckCircle2 } from 'lucide-react';

export default function App() {
  const [shoes, setShoes] = useState<Shoe[]>(INITIAL_SHOES);
  const [categories] = useState<Category[]>(INITIAL_CATEGORIES);
  const [currentView, setCurrentView] = useState<'store' | 'admin' | 'devops'>('store');
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [detailShoe, setDetailShoe] = useState<Shoe | null>(null);
  const [cart, setCart] = useState<CartItem[]>([]);
  const [isCartOpen, setIsCartOpen] = useState<boolean>(false);
  const [isAdminLoggedIn, setIsAdminLoggedIn] = useState<boolean>(false);
  const [toastMessage, setToastMessage] = useState<string | null>(null);

  const showToast = (msg: string) => {
    setToastMessage(msg);
    setTimeout(() => setToastMessage(null), 3000);
  };

  // Cart operations
  const handleAddToCart = (shoe: Shoe, size: string) => {
    setCart((prev) => {
      const existing = prev.find(
        (item) => item.shoe.id === shoe.id && item.size === size
      );
      if (existing) {
        return prev.map((item) =>
          item.shoe.id === shoe.id && item.size === size
            ? { ...item, quantity: item.quantity + 1 }
            : item
        );
      }
      return [...prev, { shoe, size, quantity: 1 }];
    });
    showToast(`Added "${shoe.name}" (Size US ${size}) to your bag!`);
  };

  const handleQuickAdd = (shoe: Shoe) => {
    const defaultSize = shoe.sizes_available
      ? shoe.sizes_available.split(',')[0].trim()
      : '9';
    handleAddToCart(shoe, defaultSize);
  };

  const handleUpdateCartQuantity = (index: number, quantity: number) => {
    if (quantity <= 0) {
      handleRemoveCartItem(index);
      return;
    }
    setCart((prev) =>
      prev.map((item, i) => (i === index ? { ...item, quantity } : item))
    );
  };

  const handleRemoveCartItem = (index: number) => {
    setCart((prev) => prev.filter((_, i) => i !== index));
  };

  // Admin CRUD operations
  const handleAdminLogin = (): boolean => false;

  const handleAdminLogout = () => {
    setIsAdminLoggedIn(false);
    showToast('Admin logged out successfully.');
  };

  const handleCreateShoe = (shoeData: Omit<Shoe, 'id'>) => {
    const newId = Math.max(...shoes.map((s) => s.id), 0) + 1;
    const newShoe: Shoe = {
      ...shoeData,
      id: newId,
    };
    setShoes((prev) => [newShoe, ...prev]);
    showToast(`New shoe "${shoeData.name}" created and synced to cloud!`);
  };

  const handleUpdateShoe = (id: number, shoeData: Partial<Shoe>) => {
    setShoes((prev) =>
      prev.map((s) => (s.id === id ? { ...s, ...shoeData } : s))
    );
    showToast(`Shoe #${id} updated successfully.`);
  };

  const handleDeleteShoe = (id: number) => {
    const target = shoes.find((s) => s.id === id);
    setShoes((prev) => prev.filter((s) => s.id !== id));
    showToast(`Shoe "${target?.name || id}" removed from inventory.`);
  };

  const handleEditInAdmin = (shoe: Shoe) => {
    setCurrentView('admin');
  };

  const totalCartCount = cart.reduce((acc, item) => acc + item.quantity, 0);

  return (
    <div className="min-h-screen flex flex-col bg-stone-50 text-stone-900 font-sans selection:bg-amber-500 selection:text-stone-950">
      {/* Toast notification */}
      {toastMessage && (
        <div className="fixed bottom-6 right-6 z-50 bg-stone-900 text-white text-xs font-semibold px-4 py-3 rounded-2xl shadow-2xl border border-stone-800 flex items-center gap-2 animate-in fade-in slide-in-from-bottom-4 duration-200">
          <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
          <span>{toastMessage}</span>
        </div>
      )}

      {/* Navigation Header */}
      <Header
        currentView={currentView}
        setCurrentView={setCurrentView}
        selectedCategory={selectedCategory}
        setSelectedCategory={setSelectedCategory}
        cartCount={totalCartCount}
        onOpenCart={() => setIsCartOpen(true)}
        isAdminLoggedIn={isAdminLoggedIn}
      />

      {/* View Switcher Bar on Sub-pages */}
      <div className="bg-stone-100/80 border-b border-stone-200 py-2 px-4 sm:px-8">
        <div className="max-w-7xl mx-auto flex flex-wrap items-center justify-between text-xs gap-3">
          <div className="flex items-center gap-2 text-stone-600 font-medium">
            <span>Current Mode:</span>
            <span className="font-bold uppercase tracking-wider text-stone-900 bg-white px-2 py-0.5 rounded border border-stone-200">
              {currentView === 'store'
                ? 'Storefront Catalog'
                : currentView === 'admin'
                ? 'Admin Inventory CRUD'
                : 'DevOps & CI4 Serverless Hub'}
            </span>
          </div>

          <div className="flex items-center gap-4 text-stone-500 text-[11px]">
            <span className="hidden sm:flex items-center gap-1">
              <Server className="w-3 h-3 text-amber-600" />
              <span>vercel-php@0.7.0</span>
            </span>
            <span className="hidden sm:flex items-center gap-1">
              <Cloud className="w-3 h-3 text-sky-500" />
              <span>Cloudinary Direct REST</span>
            </span>
            <span className="hidden sm:flex items-center gap-1">
              <Database className="w-3 h-3 text-purple-500" />
              <span>getenv() Cloud SQL</span>
            </span>
          </div>
        </div>
      </div>

      {/* Main View Body */}
      <main className="flex-grow">
        {currentView === 'store' && (
          <Storefront
            shoes={shoes}
            categories={categories}
            selectedCategory={selectedCategory}
            setSelectedCategory={setSelectedCategory}
            onSelectShoe={(shoe) => setDetailShoe(shoe)}
            onQuickAdd={handleQuickAdd}
            onGoToAdmin={() => setCurrentView('admin')}
          />
        )}

        {currentView === 'admin' && (
          <AdminPanel
            shoes={shoes}
            categories={categories}
            isLoggedIn={isAdminLoggedIn}
            onLogin={handleAdminLogin}
            onLogout={handleAdminLogout}
            onCreateShoe={handleCreateShoe}
            onUpdateShoe={handleUpdateShoe}
            onDeleteShoe={handleDeleteShoe}
            onViewProduct={(shoe) => setDetailShoe(shoe)}
            onGoToStorefront={() => setCurrentView('store')}
          />
        )}

        {currentView === 'devops' && <DevOpsHub />}
      </main>

      {/* Product Detail Modal */}
      <ProductDetailModal
        shoe={detailShoe}
        onClose={() => setDetailShoe(null)}
        onAddToCart={handleAddToCart}
        onEditInAdmin={handleEditInAdmin}
      />

      {/* Slide-out Cart Drawer */}
      <CartDrawer
        isOpen={isCartOpen}
        onClose={() => setIsCartOpen(false)}
        items={cart}
        onUpdateQuantity={handleUpdateCartQuantity}
        onRemoveItem={handleRemoveCartItem}
        onClearCart={() => setCart([])}
      />

      {/* Footer */}
      <footer className="bg-stone-950 text-stone-400 text-xs border-t border-stone-900 mt-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-4 gap-8">
          <div className="space-y-3">
            <span className="text-white font-black tracking-wider text-base">APEXSTRIDE</span>
            <p className="text-stone-500 leading-relaxed text-[11px]">
              Engineered with CodeIgniter 4 (CI4) MVC framework for serverless Vercel deployment. Full cloud database support and direct Cloudinary image streaming.
            </p>
          </div>

          <div>
            <h4 className="text-white font-bold uppercase tracking-wider mb-3 text-[11px]">
              Categories
            </h4>
            <ul className="space-y-1.5 text-[11px]">
              <li>
                <button
                  onClick={() => {
                    setCurrentView('store');
                    setSelectedCategory('sneakers');
                  }}
                  className="hover:text-white"
                >
                  Lifestyle & Sneakers
                </button>
              </li>
              <li>
                <button
                  onClick={() => {
                    setCurrentView('store');
                    setSelectedCategory('running');
                  }}
                  className="hover:text-white"
                >
                  Performance Running
                </button>
              </li>
              <li>
                <button
                  onClick={() => {
                    setCurrentView('store');
                    setSelectedCategory('boots');
                  }}
                  className="hover:text-white"
                >
                  Rugged Leather Boots
                </button>
              </li>
              <li>
                <button
                  onClick={() => {
                    setCurrentView('store');
                    setSelectedCategory('formal');
                  }}
                  className="hover:text-white"
                >
                  Handcrafted Formal
                </button>
              </li>
            </ul>
          </div>

          <div>
            <h4 className="text-white font-bold uppercase tracking-wider mb-3 text-[11px]">
              Vercel Architecture Specs
            </h4>
            <ul className="space-y-1.5 text-[11px]">
              <li className="flex items-center gap-1.5">
                <span className="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>vercel-php@0.7.0 runtime</span>
              </li>
              <li className="flex items-center gap-1.5">
                <span className="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                <span>/tmp/ci4_writable redirection</span>
              </li>
              <li className="flex items-center gap-1.5">
                <span className="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
                <span>Cloudinary & S3 direct uploads</span>
              </li>
              <li className="flex items-center gap-1.5">
                <span className="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                <span>getenv() DB connection</span>
              </li>
            </ul>
          </div>

          <div>
            <h4 className="text-white font-bold uppercase tracking-wider mb-3 text-[11px]">
              Developer Tools
            </h4>
            <p className="text-stone-500 mb-3 text-[11px]">
              Inspect CI4 source files or download the complete production ZIP package.
            </p>
            <button
              onClick={() => setCurrentView('devops')}
              className="inline-block px-4 py-2 bg-stone-800 text-white rounded-xl text-xs font-semibold hover:bg-stone-700 transition-colors"
            >
              Open CI4 Code Inspector &rarr;
            </button>
          </div>
        </div>

        <div className="border-t border-stone-900 py-4 text-center text-[11px] text-stone-600">
          &copy; {new Date().getFullYear()} ApexStride Shoe E-Commerce. CodeIgniter 4 on Vercel Serverless.
        </div>
      </footer>
    </div>
  );
}
