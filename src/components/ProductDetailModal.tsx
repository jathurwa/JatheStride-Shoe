import React, { useState } from 'react';
import { 
  X, 
  ShoppingBag, 
  Heart, 
  Share2, 
  ShieldAlert, 
  Cloud, 
  Check, 
  Flame, 
  Edit3 
} from 'lucide-react';
import { Shoe } from '../types';

interface ProductDetailModalProps {
  shoe: Shoe | null;
  onClose: () => void;
  onAddToCart: (shoe: Shoe, size: string) => void;
  onEditInAdmin: (shoe: Shoe) => void;
}

export const ProductDetailModal: React.FC<ProductDetailModalProps> = ({
  shoe,
  onClose,
  onAddToCart,
  onEditInAdmin,
}) => {
  if (!shoe) return null;

  const sizes = shoe.sizes_available
    ? shoe.sizes_available.split(',').map((s) => s.trim())
    : ['8', '8.5', '9', '9.5', '10', '10.5', '11', '12'];

  const [selectedSize, setSelectedSize] = useState<string>(sizes[0] || '9');
  const [isAdded, setIsAdded] = useState(false);
  const [isWishlisted, setIsWishlisted] = useState(false);

  const handleAdd = () => {
    onAddToCart(shoe, selectedSize);
    setIsAdded(true);
    setTimeout(() => setIsAdded(false), 2000);
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-stone-950/70 backdrop-blur-xs flex items-center justify-center p-4">
      <div 
        className="relative w-full max-w-4xl bg-white rounded-3xl border border-stone-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition-colors"
          aria-label="Close"
        >
          <X className="w-5 h-5" />
        </button>

        <div className="grid grid-cols-1 md:grid-cols-2">
          {/* Left Column: Image Stage */}
          <div className="bg-stone-100 p-8 sm:p-12 flex flex-col items-center justify-center relative border-b md:border-b-0 md:border-r border-stone-200">
            {shoe.is_featured === 1 && (
              <span className="absolute top-6 left-6 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-500 text-stone-950 flex items-center gap-1 shadow-xs">
                <Flame className="w-3.5 h-3.5" />
                <span>Featured</span>
              </span>
            )}

            <div className="w-full h-72 sm:h-80 flex items-center justify-center">
              <img
                src={shoe.image_url}
                alt={shoe.name}
                className="max-h-full max-w-full object-contain drop-shadow-xl hover:scale-105 transition-transform duration-300"
                onError={(e) => {
                  (e.target as HTMLImageElement).src =
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80';
                }}
              />
            </div>

            {/* Cloudinary CDN Indicator */}
            <div className="mt-4 w-full bg-white/80 backdrop-blur-xs border border-stone-200 rounded-xl px-3 py-2 text-[11px] text-stone-600 flex items-center justify-between">
              <span className="flex items-center gap-1.5 font-medium">
                <Cloud className="w-3.5 h-3.5 text-amber-600" />
                <span>CDN: {shoe.cloudinary_public_id ? 'Cloudinary (Signed Direct)' : 'Cloud Storage CDN'}</span>
              </span>
              <span className="font-mono text-[10px] text-stone-400">ID #{shoe.id}</span>
            </div>
          </div>

          {/* Right Column: Info & Actions */}
          <div className="p-6 sm:p-8 flex flex-col justify-between space-y-6">
            <div>
              {/* Category & Badge */}
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold uppercase tracking-widest text-amber-600">
                  {shoe.category_name || 'Footwear'}
                </span>
                {shoe.stock_quantity <= 5 ? (
                  <span className="text-[11px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">
                    Low Stock: {shoe.stock_quantity} left
                  </span>
                ) : (
                  <span className="text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                    In Stock ({shoe.stock_quantity} pairs)
                  </span>
                )}
              </div>

              {/* Title */}
              <h2 className="text-2xl sm:text-3xl font-black text-stone-900 tracking-tight mt-1">
                {shoe.name}
              </h2>

              {/* Price Display */}
              <div className="flex items-baseline gap-3 mt-3">
                <span className="text-2xl font-black text-stone-950">
                  KSH {shoe.price.toFixed(2)}
                </span>
                {shoe.sale_price && (
                  <span className="text-sm font-semibold text-stone-400 line-through">
                    KSH {shoe.sale_price.toFixed(2)}
                  </span>
                )}
                {shoe.sale_price && (
                  <span className="text-xs font-extrabold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                    Save KSH {(shoe.price - shoe.sale_price).toFixed(2)}
                  </span>
                )}
              </div>

              {/* Description */}
              <div className="mt-4 pt-4 border-t border-stone-100">
                <h4 className="text-xs font-bold uppercase tracking-wider text-stone-400 mb-1.5">
                  Specification & Details
                </h4>
                <p className="text-xs text-stone-600 leading-relaxed">
                  {shoe.description}
                </p>
              </div>

              {/* Size Selector */}
              <div className="mt-5 pt-4 border-t border-stone-100">
                <div className="flex items-center justify-between mb-2">
                  <span className="text-xs font-bold uppercase tracking-wider text-stone-800">
                    Select US Size:
                  </span>
                  <span className="text-[11px] text-stone-500 font-medium">Standard Fit</span>
                </div>
                <div className="grid grid-cols-4 sm:grid-cols-6 gap-2">
                  {sizes.map((sz) => (
                    <button
                      key={sz}
                      type="button"
                      onClick={() => setSelectedSize(sz)}
                      className={`py-2 text-xs font-bold rounded-xl border transition-all ${
                        selectedSize === sz
                          ? 'bg-stone-950 text-white border-stone-950 shadow-xs'
                          : 'bg-stone-50 hover:bg-stone-100 text-stone-800 border-stone-200'
                      }`}
                    >
                      {sz}
                    </button>
                  ))}
                </div>
              </div>
            </div>

            {/* CTAs */}
            <div className="space-y-2.5 pt-2">
              <button
                onClick={handleAdd}
                disabled={shoe.stock_quantity === 0}
                className={`w-full py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow-md ${
                  shoe.stock_quantity === 0
                    ? 'bg-stone-200 text-stone-400 cursor-not-allowed'
                    : isAdded
                    ? 'bg-emerald-600 text-white'
                    : 'bg-amber-500 hover:bg-amber-400 text-stone-950 hover:shadow-amber-500/20'
                }`}
              >
                {isAdded ? (
                  <>
                    <Check className="w-4 h-4" />
                    <span>Added to Bag!</span>
                  </>
                ) : (
                  <>
                    <ShoppingBag className="w-4 h-4" />
                    <span>Add to Bag - KSH {shoe.price.toFixed(2)}</span>
                  </>
                )}
              </button>

              <div className="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  onClick={() => setIsWishlisted(!isWishlisted)}
                  className={`py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 transition-colors ${
                    isWishlisted
                      ? 'border-rose-300 bg-rose-50 text-rose-600'
                      : 'border-stone-200 hover:bg-stone-50 text-stone-700'
                  }`}
                >
                  <Heart
                    className={`w-3.5 h-3.5 ${
                      isWishlisted ? 'fill-rose-500 text-rose-500' : ''
                    }`}
                  />
                  <span>{isWishlisted ? 'Saved' : 'Wishlist'}</span>
                </button>

                <button
                  type="button"
                  onClick={() => {
                    onClose();
                    onEditInAdmin(shoe);
                  }}
                  className="py-2.5 rounded-xl border border-stone-200 hover:bg-stone-50 text-stone-700 text-xs font-bold flex items-center justify-center gap-1.5 transition-colors"
                >
                  <Edit3 className="w-3.5 h-3.5 text-amber-600" />
                  <span>Edit in Admin</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
