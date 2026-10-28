import React, { useState } from 'react';
import { X, Trash2, ShoppingBag, ArrowRight, CheckCircle2 } from 'lucide-react';
import { CartItem } from '../types';

interface CartDrawerProps {
  isOpen: boolean;
  onClose: () => void;
  items: CartItem[];
  onUpdateQuantity: (index: number, quantity: number) => void;
  onRemoveItem: (index: number) => void;
  onClearCart: () => void;
}

export const CartDrawer: React.FC<CartDrawerProps> = ({
  isOpen,
  onClose,
  items,
  onUpdateQuantity,
  onRemoveItem,
  onClearCart,
}) => {
  const [isCheckingOut, setIsCheckingOut] = useState(false);
  const [isSuccess, setIsSuccess] = useState(false);

  if (!isOpen) return null;

  const subtotal = items.reduce(
    (acc, item) => acc + (item.shoe.price * item.quantity),
    0
  );

  const handleCheckout = () => {
    setIsCheckingOut(true);
    setTimeout(() => {
      setIsCheckingOut(false);
      setIsSuccess(true);
      setTimeout(() => {
        setIsSuccess(false);
        onClearCart();
        onClose();
      }, 2500);
    }, 1200);
  };

  return (
    <div className="fixed inset-0 z-50 overflow-hidden bg-stone-950/60 backdrop-blur-xs flex justify-end">
      <div 
        className="w-full max-w-md bg-white h-full shadow-2xl flex flex-col justify-between animate-in slide-in-from-right duration-300"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Top bar */}
        <div className="p-6 border-b border-stone-200 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <ShoppingBag className="w-5 h-5 text-stone-900" />
            <h2 className="text-base font-black text-stone-900 tracking-tight">
              Your Footwear Bag ({items.length})
            </h2>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 rounded-full hover:bg-stone-100 text-stone-500 hover:text-stone-900 transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Content list */}
        <div className="p-6 overflow-y-auto flex-grow space-y-4">
          {isSuccess ? (
            <div className="text-center py-16 space-y-3">
              <CheckCircle2 className="w-12 h-12 text-emerald-600 mx-auto" />
              <h3 className="text-lg font-black text-stone-900">Order Simulated!</h3>
              <p className="text-xs text-stone-500 max-w-xs mx-auto">
                Your serverless footwear order has been confirmed. Thank you for testing ApexStride.
              </p>
            </div>
          ) : items.length === 0 ? (
            <div className="text-center py-20 space-y-3">
              <ShoppingBag className="w-12 h-12 text-stone-300 mx-auto" />
              <h3 className="text-base font-bold text-stone-900">Your bag is empty</h3>
              <p className="text-xs text-stone-500">
                Explore our running shoes, boots, and sneakers collection.
              </p>
            </div>
          ) : (
            items.map((item, idx) => (
              <div
                key={`${item.shoe.id}-${item.size}-${idx}`}
                className="flex items-center gap-4 p-3 rounded-2xl bg-stone-50 border border-stone-200"
              >
                <img
                  src={item.shoe.image_url}
                  alt={item.shoe.name}
                  className="w-16 h-16 object-contain rounded-xl bg-white p-1 border border-stone-200"
                />
                <div className="flex-grow min-w-0">
                  <h4 className="font-bold text-xs text-stone-900 truncate">
                    {item.shoe.name}
                  </h4>
                  <div className="text-[11px] text-stone-500 mt-0.5">
                    Size: <span className="font-bold text-stone-800">US {item.size}</span>
                  </div>
                  <div className="text-xs font-black text-stone-950 mt-1">
                    KSH {(item.shoe.price * item.quantity).toFixed(2)}
                  </div>
                </div>

                <div className="flex flex-col items-end gap-2">
                  <button
                    onClick={() => onRemoveItem(idx)}
                    className="text-stone-400 hover:text-rose-600 p-1"
                    title="Remove item"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                  </button>

                  <div className="flex items-center border border-stone-300 rounded-lg bg-white overflow-hidden text-xs">
                    <button
                      onClick={() => onUpdateQuantity(idx, item.quantity - 1)}
                      className="px-2 py-0.5 hover:bg-stone-100 font-bold"
                    >
                      -
                    </button>
                    <span className="px-2 py-0.5 font-bold text-[11px]">{item.quantity}</span>
                    <button
                      onClick={() => onUpdateQuantity(idx, item.quantity + 1)}
                      className="px-2 py-0.5 hover:bg-stone-100 font-bold"
                    >
                      +
                    </button>
                  </div>
                </div>
              </div>
            ))
          )}
        </div>

        {/* Bottom checkout summary */}
        {items.length > 0 && !isSuccess && (
          <div className="p-6 border-t border-stone-200 bg-stone-50 space-y-4">
            <div className="space-y-1.5 text-xs text-stone-600">
              <div className="flex justify-between">
                <span>Subtotal</span>
                <span className="font-bold text-stone-900">KSH {subtotal.toFixed(2)}</span>
              </div>
              <div className="flex justify-between">
                <span>Serverless Express Shipping</span>
                <span className="text-emerald-700 font-semibold">Free (Edge Delivery)</span>
              </div>
              <div className="flex justify-between pt-2 border-t border-stone-200 text-sm font-black text-stone-950">
                <span>Total</span>
                <span>KSH {subtotal.toFixed(2)}</span>
              </div>
            </div>

            <button
              onClick={handleCheckout}
              disabled={isCheckingOut}
              className="w-full py-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs uppercase tracking-wider transition-all shadow-lg hover:shadow-amber-500/20 flex items-center justify-center gap-2"
            >
              {isCheckingOut ? (
                <span>Authorizing Payment...</span>
              ) : (
                <>
                  <span>Checkout Now &bull; KSH {subtotal.toFixed(2)}</span>
                  <ArrowRight className="w-4 h-4" />
                </>
              )}
            </button>
          </div>
        )}
      </div>
    </div>
  );
};
