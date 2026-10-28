import React from 'react';
import { 
  ShoppingBag, 
  ShieldCheck, 
  Terminal, 
  Store, 
  Cloud,
  CheckCircle2
} from 'lucide-react';

interface HeaderProps {
  currentView: 'store' | 'admin' | 'devops';
  setCurrentView: (view: 'store' | 'admin' | 'devops') => void;
  selectedCategory: string;
  setSelectedCategory: (cat: string) => void;
  cartCount: number;
  onOpenCart: () => void;
  isAdminLoggedIn: boolean;
}

export const Header: React.FC<HeaderProps> = ({
  currentView,
  setCurrentView,
  selectedCategory,
  setSelectedCategory,
  cartCount,
  onOpenCart,
  isAdminLoggedIn,
}) => {
  return (
    <header className="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-stone-200">
      {/* Serverless Architecture Status Pill */}
      <div className="bg-stone-900 text-stone-300 text-xs py-1.5 px-4 text-center flex items-center justify-center gap-2 border-b border-stone-800">
        <span className="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
        <span className="hidden sm:inline">
          CodeIgniter 4 Serverless Core &bull; Runtime:{' '}
          <strong className="text-amber-400 font-mono">vercel-php@0.7.0</strong> &bull; Media:{' '}
          <strong className="text-sky-400">Cloudinary CDN</strong>
        </span>
        <span className="sm:hidden text-[11px]">
          CI4 on Vercel Serverless &bull; Cloudinary &bull; /tmp Bypass
        </span>
        <button
          onClick={() => setCurrentView('devops')}
          className="ml-2 text-[10px] font-bold uppercase tracking-wider text-amber-400 hover:text-amber-300 underline underline-offset-2 flex items-center gap-1"
        >
          <Terminal className="w-3 h-3" />
          <span>Inspect CI4 Code</span>
        </button>
      </div>

      {/* Main Nav */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        {/* Logo */}
        <button
          onClick={() => {
            setCurrentView('store');
            setSelectedCategory('all');
          }}
          className="flex items-center gap-2 text-stone-950 font-black tracking-tighter text-2xl group text-left"
        >
          <div className="w-8 h-8 rounded-lg bg-stone-900 text-white flex items-center justify-center text-xs font-black group-hover:bg-amber-600 transition-colors">
            AS
          </div>
          <span>
            APEX<span className="text-amber-600">STRIDE</span>
          </span>
        </button>

        {/* View Switchers / Category Navigation */}
        <nav className="hidden md:flex items-center gap-6 text-sm font-medium text-stone-600">
          <button
            onClick={() => {
              setCurrentView('store');
              setSelectedCategory('all');
            }}
            className={`transition-colors py-1 ${
              currentView === 'store' && selectedCategory === 'all'
                ? 'text-stone-950 font-bold border-b-2 border-amber-600'
                : 'hover:text-stone-950'
            }`}
          >
            Storefront
          </button>
          <button
            onClick={() => {
              setCurrentView('store');
              setSelectedCategory('sneakers');
            }}
            className={`transition-colors py-1 ${
              currentView === 'store' && selectedCategory === 'sneakers'
                ? 'text-stone-950 font-bold border-b-2 border-amber-600'
                : 'hover:text-stone-950'
            }`}
          >
            Sneakers
          </button>
          <button
            onClick={() => {
              setCurrentView('store');
              setSelectedCategory('running');
            }}
            className={`transition-colors py-1 ${
              currentView === 'store' && selectedCategory === 'running'
                ? 'text-stone-950 font-bold border-b-2 border-amber-600'
                : 'hover:text-stone-950'
            }`}
          >
            Running
          </button>
          <button
            onClick={() => {
              setCurrentView('store');
              setSelectedCategory('boots');
            }}
            className={`transition-colors py-1 ${
              currentView === 'store' && selectedCategory === 'boots'
                ? 'text-stone-950 font-bold border-b-2 border-amber-600'
                : 'hover:text-stone-950'
            }`}
          >
            Boots
          </button>
          <button
            onClick={() => {
              setCurrentView('store');
              setSelectedCategory('formal');
            }}
            className={`transition-colors py-1 ${
              currentView === 'store' && selectedCategory === 'formal'
                ? 'text-stone-950 font-bold border-b-2 border-amber-600'
                : 'hover:text-stone-950'
            }`}
          >
            Formal
          </button>
        </nav>

        {/* Action Controls */}
        <div className="flex items-center gap-3">
          {/* DevOps Inspector Button */}
          <button
            onClick={() => setCurrentView('devops')}
            className={`text-xs font-semibold px-3 py-1.5 rounded-full border transition-all flex items-center gap-1.5 ${
              currentView === 'devops'
                ? 'bg-stone-900 text-white border-stone-900 shadow-sm'
                : 'border-stone-200 hover:border-stone-400 text-stone-700 bg-stone-50'
            }`}
            title="Inspect CI4 files, vercel.json, and download ZIP"
          >
            <Terminal className="w-3.5 h-3.5 text-amber-500" />
            <span className="hidden sm:inline">CI4 & Vercel Files</span>
            <span className="sm:hidden">DevOps</span>
          </button>

          {/* Admin Portal Button */}
          <button
            onClick={() => setCurrentView('admin')}
            className={`text-xs font-semibold px-3 py-1.5 rounded-full border transition-all flex items-center gap-1.5 ${
              currentView === 'admin'
                ? 'bg-amber-600 text-white border-amber-600 shadow-sm'
                : 'border-stone-300 hover:border-stone-900 text-stone-700'
            }`}
          >
            <ShieldCheck className="w-3.5 h-3.5" />
            <span>Admin</span>
            {isAdminLoggedIn && (
              <span className="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            )}
          </button>

          {/* Cart Icon */}
          <button
            onClick={onOpenCart}
            className="relative p-2 text-stone-700 hover:text-stone-950 transition-colors"
            aria-label="Shopping Cart"
          >
            <ShoppingBag className="w-5 h-5" />
            {cartCount > 0 && (
              <span className="absolute top-1 right-0 w-4 h-4 rounded-full bg-amber-600 text-white text-[10px] font-bold flex items-center justify-center">
                {cartCount}
              </span>
            )}
          </button>
        </div>
      </div>
    </header>
  );
};
