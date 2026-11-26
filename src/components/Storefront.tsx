import React, { useState, useMemo, useEffect } from 'react';
import { 
  Search, 
  SlidersHorizontal, 
  ArrowRight, 
  Flame, 
  Eye, 
  Sparkles, 
  Check, 
  Filter,
  Tag,
  X,
  ChevronLeft,
  ChevronRight
} from 'lucide-react';
import { Shoe, Category } from '../types';

interface StorefrontProps {
  shoes: Shoe[];
  categories: Category[];
  selectedCategory: string;
  setSelectedCategory: (slug: string) => void;
  onSelectShoe: (shoe: Shoe) => void;
  onQuickAdd: (shoe: Shoe) => void;
  onGoToAdmin: () => void;
  searchQuery?: string;
  onSearchChange?: (q: string) => void;
}

export const Storefront: React.FC<StorefrontProps> = ({
  shoes,
  categories,
  selectedCategory,
  setSelectedCategory,
  onSelectShoe,
  onQuickAdd,
  onGoToAdmin,
  searchQuery,
  onSearchChange,
}) => {
  const [internalQuery, setInternalQuery] = useState('');
  const activeQuery = searchQuery !== undefined ? searchQuery : internalQuery;
  const setActiveQuery = onSearchChange || setInternalQuery;

  const [sortBy, setSortBy] = useState<'featured' | 'price-asc' | 'price-desc'>('featured');
  const [quickAddedId, setQuickAddedId] = useState<number | null>(null);
  const [currentPage, setCurrentPage] = useState<number>(1);
  const ITEMS_PER_PAGE = 20; // 5 rows x 4 columns on larger screens

  useEffect(() => {
    setCurrentPage(1);
  }, [selectedCategory, activeQuery, sortBy]);

  // Total offers count
  const offersCount = useMemo(() => {
    return shoes.filter(
      (s) => s.sale_price !== null && s.sale_price !== undefined && s.sale_price > 0 && s.sale_price < s.price
    ).length;
  }, [shoes]);

  // Filter and sort shoes
  const filteredShoes = useMemo(() => {
    let result = [...shoes];

    // Filter by category or offers
    if (selectedCategory === 'offers') {
      result = result.filter(
        (s) => s.sale_price !== null && s.sale_price !== undefined && s.sale_price > 0 && s.sale_price < s.price
      );
    } else if (selectedCategory && selectedCategory !== 'all') {
      result = result.filter(
        (s) => s.category_slug === selectedCategory || s.category_name?.toLowerCase() === selectedCategory.toLowerCase()
      );
    }

    // Filter by search query
    if (activeQuery.trim()) {
      const q = activeQuery.toLowerCase();
      result = result.filter(
        (s) =>
          s.name.toLowerCase().includes(q) ||
          s.description.toLowerCase().includes(q) ||
          s.category_name?.toLowerCase().includes(q) ||
          (s.sizes_available && s.sizes_available.toLowerCase().includes(q))
      );
    }

    // Sort
    if (sortBy === 'price-asc') {
      result.sort((a, b) => a.price - b.price);
    } else if (sortBy === 'price-desc') {
      result.sort((a, b) => b.price - a.price);
    } else {
      // featured first
      result.sort((a, b) => b.is_featured - a.is_featured);
    }

    return result;
  }, [shoes, selectedCategory, activeQuery, sortBy]);

  const totalPages = Math.max(1, Math.ceil(filteredShoes.length / ITEMS_PER_PAGE));
  const safePage = Math.min(currentPage, totalPages);
  const paginatedShoes = useMemo(() => {
    const start = (safePage - 1) * ITEMS_PER_PAGE;
    return filteredShoes.slice(start, start + ITEMS_PER_PAGE);
  }, [filteredShoes, safePage]);

  const pageNumbers = useMemo(() => {
    if (totalPages <= 7) {
      return Array.from({ length: totalPages }, (_, i) => i + 1);
    }
    let windowStart = Math.max(2, safePage - 1);
    let windowEnd = Math.min(totalPages - 1, safePage + 1);
    if (safePage <= 2) {
      windowStart = 2;
      windowEnd = 3;
    } else if (safePage >= totalPages - 1) {
      windowStart = totalPages - 2;
      windowEnd = totalPages - 1;
    }
    const items: (number | '...')[] = [1];
    if (windowStart > 2) items.push('...');
    for (let p = windowStart; p <= windowEnd; p++) items.push(p);
    if (windowEnd < totalPages - 1) items.push('...');
    items.push(totalPages);
    return items;
  }, [totalPages, safePage]);

  const goToPage = (page: number) => {
    const clamped = Math.max(1, Math.min(totalPages, page));
    setCurrentPage(clamped);
    const catalogEl = document.getElementById('catalog');
    if (catalogEl) {
      catalogEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  };

  const featuredShoe = shoes.find((s) => s.is_featured === 1) || shoes[0];

  const handleQuickAddClick = (e: React.MouseEvent, shoe: Shoe) => {
    e.stopPropagation();
    onQuickAdd(shoe);
    setQuickAddedId(shoe.id);
    setTimeout(() => setQuickAddedId(null), 1500);
  };

  return (
    <div className="space-y-12 pb-20">
      {/* Hero Section */}
      <section className="relative bg-stone-900 text-white overflow-hidden py-16 lg:py-20 border-b border-stone-800">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div className="space-y-6">
            <span className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
              <Sparkles className="w-3.5 h-3.5" />
              <span>2025 Serverless Collection &bull; CodeIgniter 4 Core</span>
            </span>

            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
              ENGINEERED FOR <br />
              <span className="text-amber-500">EVERY STRIDE.</span>
            </h1>

            <p className="text-stone-400 text-sm sm:text-base max-w-lg leading-relaxed">
              Explore lightweight racing shoes, handcrafted leather formal wear, rugged outdoor boots, and iconic everyday sneakers. Powered by CodeIgniter 4 with direct serverless execution on Vercel.
            </p>

            <div className="flex flex-wrap items-center gap-4 pt-2">
              <a
                href="#catalog"
                className="px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs uppercase tracking-wider transition-all shadow-lg hover:shadow-amber-500/25"
              >
                Explore Footwear ({shoes.length}) &darr;
              </a>
              <button
                onClick={onGoToAdmin}
                className="px-5 py-3 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-200 font-semibold text-xs transition-all border border-stone-700"
              >
                Admin CRUD & Cloud Uploads
              </button>
            </div>
          </div>

          {/* Hero Featured Card */}
          {featuredShoe && (
            <div className="relative flex justify-center">
              <div className="w-72 h-72 sm:w-96 sm:h-96 rounded-full bg-gradient-to-tr from-amber-500/20 to-orange-500/10 absolute -top-4 -left-4 blur-2xl" />
              <div 
                onClick={() => onSelectShoe(featuredShoe)}
                className="relative z-10 w-full max-w-md bg-stone-950/60 border border-stone-800 rounded-3xl p-6 backdrop-blur-md cursor-pointer hover:border-amber-500/50 transition-all group"
              >
                <div className="flex items-center justify-between mb-2">
                  <span className="text-[10px] font-black uppercase tracking-widest text-amber-500 bg-amber-500/10 px-2 py-0.5 rounded">
                    Featured Shoe
                  </span>
                  <span className="text-xs text-stone-400 font-medium">
                    {featuredShoe.category_name}
                  </span>
                </div>
                <div className="h-48 flex items-center justify-center overflow-hidden my-3">
                  <img
                    src={featuredShoe.image_url}
                    alt={featuredShoe.name}
                    className="max-h-full object-contain -rotate-12 group-hover:rotate-0 transition-transform duration-500 drop-shadow-2xl"
                  />
                </div>
                <div className="flex items-center justify-between pt-3 border-t border-stone-800">
                  <div>
                    <h3 className="font-bold text-sm text-white group-hover:text-amber-400 transition-colors">
                      {featuredShoe.name}
                    </h3>
                    <div className="text-sm font-black text-white">
                      ${featuredShoe.price.toFixed(2)}
                    </div>
                  </div>
                  <span className="text-xs font-bold text-amber-400 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                    View <ArrowRight className="w-3.5 h-3.5" />
                  </span>
                </div>
              </div>
            </div>
          )}
        </div>
      </section>

      {/* Catalog & Filter Bar */}
      <section id="catalog" className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-stone-200 pb-6">
          <div>
            <h2 className="text-2xl font-black tracking-tight text-stone-900">
              {selectedCategory === 'offers'
                ? 'Special Offers & Discounts'
                : selectedCategory !== 'all'
                ? `${categories.find((c) => c.slug === selectedCategory)?.name || selectedCategory} Collection`
                : 'Footwear Collection'}
            </h2>
            <p className="text-xs text-stone-500 mt-0.5">
              {selectedCategory === 'offers'
                ? `Showing ${filteredShoes.length} on-sale shoes with promotional discounts`
                : `Showing ${filteredShoes.length} of ${shoes.length} shoes in catalog`}
            </p>
          </div>

          {/* Search & Sort (mobile-responsive) */}
          <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
            <div className="relative flex-1 sm:w-64">
              <Search className="w-4 h-4 text-stone-400 absolute left-3 top-2.5 pointer-events-none" />
              <input
                type="text"
                value={activeQuery}
                onChange={(e) => setActiveQuery(e.target.value)}
                placeholder="Search shoes, colors, sizes..."
                className="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-stone-300 bg-white focus:outline-hidden focus:border-stone-900 shadow-2xs"
              />
              {activeQuery && (
                <button
                  onClick={() => setActiveQuery('')}
                  className="absolute right-2.5 top-2.5 text-stone-400 hover:text-stone-600"
                >
                  <X className="w-3.5 h-3.5" />
                </button>
              )}
            </div>

            <div className="flex items-center gap-1.5 bg-white border border-stone-300 rounded-xl px-2.5 py-1.5 text-xs text-stone-700 justify-between sm:justify-start">
              <SlidersHorizontal className="w-3.5 h-3.5 text-stone-500" />
              <select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value as any)}
                className="bg-transparent focus:outline-hidden text-xs font-medium cursor-pointer"
              >
                <option value="featured">Featured First</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
              </select>
            </div>
          </div>
        </div>

        {/* Category Pills */}
        <div className="flex flex-wrap items-center gap-2 pt-4">
          {/* 1. All Footwear */}
          <button
            onClick={() => setSelectedCategory('all')}
            className={`px-4 py-2 rounded-xl text-xs font-bold transition-all ${
              selectedCategory === 'all'
                ? 'bg-stone-950 text-white shadow-xs'
                : 'bg-stone-100 hover:bg-stone-200 text-stone-700'
            }`}
          >
            All Footwear ({shoes.length})
          </button>

          {/* 2. Offers Pill (Immediately after All Footwear) */}
          <button
            onClick={() => setSelectedCategory('offers')}
            className={`px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 ${
              selectedCategory === 'offers'
                ? 'bg-rose-600 text-white shadow-md ring-2 ring-rose-500/30'
                : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200'
            }`}
          >
            <Tag className="w-3.5 h-3.5" />
            <span>Offers ({offersCount})</span>
          </button>
          {categories.map((cat) => {
            const count = shoes.filter(
              (s) => s.category_slug === cat.slug || s.category_name?.toLowerCase() === cat.slug.toLowerCase()
            ).length;
            const isSelected = selectedCategory === cat.slug;
            return (
              <button
                key={cat.id}
                onClick={() => setSelectedCategory(cat.slug)}
                className={`px-4 py-2 rounded-xl text-xs font-bold transition-all ${
                  isSelected
                    ? 'bg-amber-600 text-white shadow-xs'
                    : 'bg-stone-100 hover:bg-stone-200 text-stone-700'
                }`}
              >
                {cat.name} ({count})
              </button>
            );
          })}
        </div>
      </section>

      {/* Product Grid */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {filteredShoes.length === 0 ? (
          <div className="bg-white rounded-3xl border border-stone-200 p-12 text-center my-6">
            <Filter className="w-10 h-10 text-stone-300 mx-auto mb-3" />
            <h3 className="text-base font-bold text-stone-900">No Shoes Found</h3>
            <p className="text-xs text-stone-500 mt-1 max-w-sm mx-auto">
              {selectedCategory === 'offers'
                ? 'No shoes currently have active promotional markdowns.'
                : `No products match "${activeQuery}" in category "${selectedCategory}".`}
            </p>
            <button
              onClick={() => {
                setActiveQuery('');
                setSelectedCategory('all');
              }}
              className="mt-4 px-4 py-2 rounded-xl bg-stone-900 text-white text-xs font-bold hover:bg-amber-600 transition-colors"
            >
              Reset Filters
            </button>
          </div>
        ) : (
          <>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              {paginatedShoes.map((shoe) => {
              const isAdded = quickAddedId === shoe.id;
              const isOnSale = shoe.sale_price !== null && shoe.sale_price !== undefined && shoe.sale_price > 0 && shoe.sale_price < shoe.price;
              const discountPercent = isOnSale ? Math.round(((shoe.price - shoe.sale_price!) / shoe.price) * 100) : 0;
              return (
                <div
                  key={shoe.id}
                  onClick={() => onSelectShoe(shoe)}
                  className="group bg-white rounded-3xl border border-stone-200 overflow-hidden hover:shadow-xl hover:border-stone-300 transition-all flex flex-col cursor-pointer"
                >
                  {/* Image Display */}
                  <div className="relative bg-stone-100 p-6 flex items-center justify-center h-64 overflow-hidden">
                    {shoe.is_featured === 1 && (
                      <span className="absolute top-3 left-3 z-10 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-amber-500 text-stone-950 flex items-center gap-1 shadow-xs">
                        <Flame className="w-3 h-3" />
                        Featured
                      </span>
                    )}

                    {isOnSale && (
                      <span className={`absolute ${shoe.is_featured === 1 ? 'top-10' : 'top-3'} left-3 z-10 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-rose-600 text-white shadow-xs flex items-center gap-1`}>
                        <Tag className="w-3 h-3" />
                        {discountPercent}% OFF
                      </span>
                    )}

                    <span className="absolute top-3 right-3 z-10 px-2.5 py-1 rounded-md text-[10px] font-semibold bg-white/90 backdrop-blur-xs text-stone-700 border border-stone-200">
                      {shoe.category_name || 'Lifestyle'}
                    </span>

                    <img
                      src={shoe.image_url}
                      alt={shoe.name}
                      className="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300 drop-shadow-md"
                      loading="lazy"
                      onError={(e) => {
                        (e.target as HTMLImageElement).src =
                          'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80';
                      }}
                    />

                    {/* Hover quick preview button */}
                    <div className="absolute inset-x-0 bottom-3 px-4 flex justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          onSelectShoe(shoe);
                        }}
                        className="px-3.5 py-1.5 rounded-full bg-stone-950/80 backdrop-blur-md text-white text-xs font-semibold hover:bg-stone-950 transition-colors flex items-center gap-1.5 shadow-md"
                      >
                        <Eye className="w-3.5 h-3.5" />
                        <span>Quick View</span>
                      </button>
                    </div>
                  </div>

                  {/* Details */}
                  <div className="p-5 flex flex-col flex-grow justify-between">
                    <div>
                      <span className="text-[11px] font-bold text-amber-600 uppercase tracking-wider">
                        {shoe.category_name || 'Footwear'}
                      </span>
                      <h3 className="font-bold text-stone-900 text-base line-clamp-1 mt-0.5 group-hover:text-amber-600 transition-colors">
                        {shoe.name}
                      </h3>
                      <p className="text-xs text-stone-500 line-clamp-2 mt-1 leading-relaxed">
                        {shoe.description}
                      </p>
                    </div>

                    <div className="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between">
                      <div>
                        <span className="text-[11px] text-stone-400 block font-medium">Price</span>
                        {isOnSale ? (
                          <div className="flex items-baseline gap-1.5">
                            <span className="text-lg font-black text-rose-600">
                              ${shoe.sale_price!.toFixed(2)}
                            </span>
                            <span className="text-xs text-stone-400 line-through">
                              ${shoe.price.toFixed(2)}
                            </span>
                          </div>
                        ) : (
                          <span className="text-lg font-black text-stone-950">
                            ${shoe.price.toFixed(2)}
                          </span>
                        )}
                      </div>

                      <button
                        onClick={(e) => handleQuickAddClick(e, shoe)}
                        className={`px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 ${
                          isAdded
                            ? 'bg-emerald-600 text-white'
                            : 'bg-stone-900 hover:bg-amber-600 text-white shadow-xs'
                        }`}
                      >
                        {isAdded ? (
                          <>
                            <Check className="w-3.5 h-3.5" />
                            <span>Added</span>
                          </>
                        ) : (
                          <>
                            <span>Add</span>
                            <ArrowRight className="w-3 h-3" />
                          </>
                        )}
                      </button>
                    </div>
                  </div>
                </div>
              );
            })}
            </div>

            {totalPages > 1 && (
              <nav
                aria-label="Catalog pagination"
                className="mt-12 pt-8 border-t border-stone-200 flex flex-col sm:flex-row items-center justify-between gap-4"
              >
                <p className="text-xs text-stone-500 font-medium">
                  Showing{' '}
                  <strong className="text-stone-900">
                    {(safePage - 1) * ITEMS_PER_PAGE + 1}&ndash;
                    {Math.min(filteredShoes.length, safePage * ITEMS_PER_PAGE)}
                  </strong>{' '}
                  of <strong className="text-stone-900">{filteredShoes.length}</strong> shoes{' '}
                  <span className="text-stone-400">
                    &bull; Page {safePage} of {totalPages}
                  </span>
                </p>

                <div className="flex flex-wrap items-center justify-center gap-1.5">
                  <button
                    type="button"
                    disabled={safePage <= 1}
                    onClick={() => goToPage(safePage - 1)}
                    className={`px-3.5 py-2 rounded-xl border text-xs font-bold flex items-center gap-1.5 transition-colors ${
                      safePage <= 1
                        ? 'border-stone-200 bg-stone-100 text-stone-400 cursor-not-allowed'
                        : 'border-stone-300 bg-white hover:bg-stone-100 text-stone-700 shadow-2xs'
                    }`}
                  >
                    <ChevronLeft className="w-3.5 h-3.5" />
                    <span>Prev</span>
                  </button>

                  {pageNumbers.map((item, idx) =>
                    item === '...' ? (
                      <span key={`ellipsis-${idx}`} className="px-2 py-2 text-xs font-bold text-stone-400 select-none">
                        &hellip;
                      </span>
                    ) : (
                      <button
                        key={item}
                        type="button"
                        onClick={() => goToPage(item)}
                        aria-current={item === safePage ? 'page' : undefined}
                        className={`min-w-[36px] px-3 py-2 rounded-xl text-xs font-bold text-center transition-colors ${
                          item === safePage
                            ? 'bg-stone-950 text-white font-black shadow-sm'
                            : 'border border-stone-300 bg-white hover:bg-amber-50 hover:border-amber-400 hover:text-amber-700 text-stone-700 shadow-2xs'
                        }`}
                      >
                        {item}
                      </button>
                    )
                  )}

                  <button
                    type="button"
                    disabled={safePage >= totalPages}
                    onClick={() => goToPage(safePage + 1)}
                    className={`px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors ${
                      safePage >= totalPages
                        ? 'border border-stone-200 bg-stone-100 text-stone-400 cursor-not-allowed'
                        : 'bg-stone-900 hover:bg-amber-600 text-white shadow-xs'
                    }`}
                  >
                    <span>Next</span>
                    <ChevronRight className="w-3.5 h-3.5" />
                  </button>
                </div>
              </nav>
            )}
          </>
        )}
      </section>
    </div>
  );
};
