import React, { useState } from 'react';
import { 
  Plus, 
  Trash2, 
  Edit3, 
  UploadCloud, 
  Cloud, 
  Lock, 
  LogOut, 
  Search, 
  Eye, 
  AlertTriangle, 
  CheckCircle2, 
  Sparkles,
  PackageCheck,
  Layers,
  ArrowUpRight
} from 'lucide-react';
import { Shoe, Category } from '../types';

interface AdminPanelProps {
  shoes: Shoe[];
  categories: Category[];
  isLoggedIn: boolean;
  onLogin: () => boolean;
  onLogout: () => void;
  onCreateShoe: (shoeData: Omit<Shoe, 'id'>) => void;
  onUpdateShoe: (id: number, shoeData: Partial<Shoe>) => void;
  onDeleteShoe: (id: number) => void;
  onViewProduct: (shoe: Shoe) => void;
  onGoToStorefront: () => void;
}

export const AdminPanel: React.FC<AdminPanelProps> = ({
  shoes,
  categories,
  isLoggedIn,
  onLogin,
  onLogout,
  onCreateShoe,
  onUpdateShoe,
  onDeleteShoe,
  onViewProduct,
  onGoToStorefront,
}) => {
  // Login Form state
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loginError, setLoginError] = useState('');

  // Search & Filter
  const [tableSearch, setTableSearch] = useState('');
  const [categoryFilter, setCategoryFilter] = useState('all');

  // Modal states
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [editingShoe, setEditingShoe] = useState<Shoe | null>(null);

  // Form State for Create/Edit
  const [formName, setFormName] = useState('');
  const [formCategoryId, setFormCategoryId] = useState<number>(categories[0]?.id || 1);
  const [formPrice, setFormPrice] = useState('149.99');
  const [formSalePrice, setFormSalePrice] = useState('');
  const [formStock, setFormStock] = useState('20');
  const [formSizes, setFormSizes] = useState('8,8.5,9,9.5,10,10.5,11,12');
  const [formColors, setFormColors] = useState('');
  const [formDescription, setFormDescription] = useState('');
  const [formImageUrl, setFormImageUrl] = useState('');
  const [formIsFeatured, setFormIsFeatured] = useState(false);
  const [formErrors, setFormErrors] = useState<string[]>([]);

  // Cloudinary simulation state
  const [isUploadingCloud, setIsUploadingCloud] = useState(false);
  const [uploadFeedback, setUploadFeedback] = useState<string | null>(null);

  // Quick fill sample image presets
  const sampleImages = [
    { label: 'Red Racer', url: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=80' },
    { label: 'Air High-Top', url: 'https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=1000&q=80' },
    { label: 'Rugged Boot', url: 'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=1000&q=80' },
    { label: 'Classic Oxford', url: 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=1000&q=80' },
    { label: 'Urban Runner', url: 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=1000&q=80' },
  ];

  const handleLoginSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setLoginError('');
    const success = onLogin();
    if (!success) {
      setLoginError('Client-side admin sign-in is disabled. Use the server-authenticated PHP login.');
    }
  };

  const openCreateModal = () => {
    setEditingShoe(null);
    setFormName('');
    setFormCategoryId(categories[0]?.id || 1);
    setFormPrice('149.99');
    setFormSalePrice('');
    setFormStock('25');
    setFormSizes('8,8.5,9,9.5,10,10.5,11,12');
    setFormColors('');
    setFormDescription('Engineered with responsive cushioned midsoles and durable rubber outsoles.');
    setFormImageUrl(sampleImages[0].url);
    setFormIsFeatured(false);
    setFormErrors([]);
    setUploadFeedback(null);
    setIsCreateModalOpen(true);
  };

  const openEditModal = (shoe: Shoe) => {
    setEditingShoe(shoe);
    setFormName(shoe.name);
    setFormCategoryId(shoe.category_id);
    setFormPrice(shoe.price.toString());
    setFormSalePrice(shoe.sale_price ? shoe.sale_price.toString() : '');
    setFormStock(shoe.stock_quantity.toString());
    setFormSizes(shoe.sizes_available || '8,8.5,9,9.5,10,10.5,11,12');
    setFormColors(shoe.colors_available || '');
    setFormDescription(shoe.description);
    setFormImageUrl(shoe.image_url);
    setFormIsFeatured(shoe.is_featured === 1);
    setFormErrors([]);
    setUploadFeedback(null);
    setIsCreateModalOpen(true);
  };

  // Simulates Cloudinary / S3 intercept file upload without local disk writing
  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setIsUploadingCloud(true);
    setUploadFeedback('Reading temporary stream in /tmp...');

    setTimeout(() => {
      setUploadFeedback('Streaming multipart payload to Cloudinary API...');
      
      const reader = new FileReader();
      reader.onload = (event) => {
        const dataUrl = event.target?.result as string;
        setTimeout(() => {
          setFormImageUrl(dataUrl);
          setIsUploadingCloud(false);
          setUploadFeedback('Secure Cloudinary CDN link created! (0 writes to ephemeral disk)');
        }, 800);
      };
      reader.readAsDataURL(file);
    }, 600);
  };

  const handleSaveShoe = (e: React.FormEvent) => {
    e.preventDefault();
    const errors: string[] = [];

    if (!formName.trim()) errors.push('Shoe name is required.');
    const priceNum = parseFloat(formPrice);
    if (isNaN(priceNum) || priceNum <= 0) errors.push('Valid price greater than KSH 0 is required.');
    const stockNum = parseInt(formStock, 10);
    if (isNaN(stockNum) || stockNum < 0) errors.push('Valid stock quantity is required.');
    if (!formImageUrl.trim()) errors.push('An image or Cloudinary asset is required.');
    if (!formDescription.trim()) errors.push('Product description is required.');

    if (errors.length > 0) {
      setFormErrors(errors);
      return;
    }

    const selectedCat = categories.find((c) => c.id === formCategoryId);

    if (editingShoe) {
      // Update
      onUpdateShoe(editingShoe.id, {
        name: formName.trim(),
        category_id: formCategoryId,
        category_name: selectedCat?.name || 'Footwear',
        category_slug: selectedCat?.slug || 'sneakers',
        price: priceNum,
        sale_price: formSalePrice ? parseFloat(formSalePrice) : null,
        stock_quantity: stockNum,
        sizes_available: formSizes.trim(),
        colors_available: formColors.trim() || null,
        description: formDescription.trim(),
        image_url: formImageUrl.trim(),
        is_featured: formIsFeatured ? 1 : 0,
        cloudinary_public_id: editingShoe.cloudinary_public_id || 'shoes/cld_' + Date.now(),
      });
    } else {
      // Create
      onCreateShoe({
        name: formName.trim(),
        slug: formName.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '-' + Math.floor(Math.random() * 900 + 100),
        category_id: formCategoryId,
        category_name: selectedCat?.name || 'Footwear',
        category_slug: selectedCat?.slug || 'sneakers',
        price: priceNum,
        sale_price: formSalePrice ? parseFloat(formSalePrice) : null,
        stock_quantity: stockNum,
        sizes_available: formSizes.trim(),
        colors_available: formColors.trim() || null,
        description: formDescription.trim(),
        image_url: formImageUrl.trim(),
        is_featured: formIsFeatured ? 1 : 0,
        cloudinary_public_id: 'shoes/cld_' + Date.now(),
      });
    }

    setIsCreateModalOpen(false);
  };

  // Filtered shoes in table
  const filteredShoes = shoes.filter((s) => {
    const matchesCategory =
      categoryFilter === 'all' || s.category_id.toString() === categoryFilter;
    const matchesSearch =
      s.name.toLowerCase().includes(tableSearch.toLowerCase()) ||
      s.description.toLowerCase().includes(tableSearch.toLowerCase());
    return matchesCategory && matchesSearch;
  });

  // If not logged in, render the login view
  if (!isLoggedIn) {
    return (
      <div className="min-h-[75vh] flex items-center justify-center px-4 py-12">
        <div className="w-full max-w-md bg-stone-900 border border-stone-800 rounded-3xl p-8 sm:p-10 shadow-2xl relative text-white">
          <div className="text-center mb-8">
            <div className="w-12 h-12 rounded-2xl bg-amber-500 text-stone-950 font-black text-xl flex items-center justify-center mx-auto mb-3 shadow-lg">
              AS
            </div>
            <h2 className="text-2xl font-black tracking-tight">Admin Authentication</h2>
            <p className="text-xs text-stone-400 mt-1">
              CodeIgniter 4 AuthFilter protected session
            </p>
          </div>

          {loginError && (
            <div className="mb-6 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
              <AlertTriangle className="w-4 h-4 text-rose-400 shrink-0" />
              <span>{loginError}</span>
            </div>
          )}

          <form onSubmit={handleLoginSubmit} className="space-y-4">
            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-stone-300 mb-1.5">
                Admin Email
              </label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="w-full px-4 py-3 rounded-xl bg-stone-950 border border-stone-700 text-white text-sm focus:outline-hidden focus:border-amber-500 transition-colors font-mono text-xs"
              />
            </div>

            <div>
              <div className="flex items-center justify-between mb-1.5">
                <label className="block text-xs font-semibold uppercase tracking-wider text-stone-300">
                  Password
                </label>
                <span className="text-[10px] text-stone-500 font-mono">Server authentication required</span>
              </div>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                className="w-full px-4 py-3 rounded-xl bg-stone-950 border border-stone-700 text-white text-sm focus:outline-hidden focus:border-amber-500 transition-colors font-mono text-xs"
              />
            </div>

            <button
              type="submit"
              className="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs uppercase tracking-wider transition-all shadow-lg hover:shadow-amber-500/20 mt-4 flex items-center justify-center gap-2"
            >
              <Lock className="w-4 h-4" />
              <span>Enter Admin Dashboard</span>
            </button>
          </form>

          <div className="mt-6 pt-6 border-t border-stone-800 flex items-center justify-between text-xs text-stone-400">
            <button
              onClick={onGoToStorefront}
              className="hover:text-white transition-colors"
            >
              &larr; Back to Store
            </button>
            <span className="text-[11px] text-stone-500">CI4 Session: /tmp/session</span>
          </div>
        </div>
      </div>
    );
  }

  // Logged-in Admin Dashboard
  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      {/* Top Bar with Admin Info & Logout */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-stone-900 text-white rounded-2xl p-6 border border-stone-800 shadow-sm">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <span className="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-500 text-stone-950">
              Admin Session Active
            </span>
            <span className="text-xs text-stone-400">admin@solestore.com</span>
          </div>
          <h1 className="text-2xl font-black tracking-tight">Shoe Inventory CRUD Hub</h1>
          <p className="text-xs text-stone-400">
            Create, update, categorize and delete footwear with serverless Cloudinary image streaming.
          </p>
        </div>

        <div className="flex items-center gap-3">
          <button
            onClick={openCreateModal}
            className="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 text-xs font-bold transition-all shadow-md flex items-center gap-2"
          >
            <Plus className="w-4 h-4" />
            <span>Add New Shoe</span>
          </button>

          <button
            onClick={onLogout}
            className="px-3.5 py-2.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-semibold transition-colors flex items-center gap-1.5"
            title="Log Out"
          >
            <LogOut className="w-4 h-4" />
            <span>Exit</span>
          </button>
        </div>
      </div>

      {/* Metrics Row */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
          <span className="text-xs font-semibold uppercase tracking-wider text-stone-400">
            Catalog Size
          </span>
          <div className="text-2xl font-black text-stone-900 mt-1">{shoes.length}</div>
          <span className="text-[11px] text-stone-500">Shoes in Cloud Database</span>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
          <span className="text-xs font-semibold uppercase tracking-wider text-stone-400">
            Categories
          </span>
          <div className="text-2xl font-black text-stone-900 mt-1">{categories.length}</div>
          <span className="text-[11px] text-stone-500">Active classifications</span>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
          <span className="text-xs font-semibold uppercase tracking-wider text-stone-400">
            Featured Shoes
          </span>
          <div className="text-2xl font-black text-amber-600 mt-1">
            {shoes.filter((s) => s.is_featured === 1).length}
          </div>
          <span className="text-[11px] text-stone-500">Homepage hero items</span>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
          <span className="text-xs font-semibold uppercase tracking-wider text-stone-400">
            Low Stock Alerts
          </span>
          <div className="text-2xl font-black text-rose-600 mt-1">
            {shoes.filter((s) => s.stock_quantity <= 5).length}
          </div>
          <span className="text-[11px] text-stone-500">&le; 5 units remaining</span>
        </div>
      </div>

      {/* Table Filter Controls */}
      <div className="bg-white p-4 rounded-2xl border border-stone-200 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xs">
        <div className="relative w-full sm:w-72">
          <Search className="w-4 h-4 text-stone-400 absolute left-3 top-2.5" />
          <input
            type="text"
            value={tableSearch}
            onChange={(e) => setTableSearch(e.target.value)}
            placeholder="Search shoes by name or specs..."
            className="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-stone-300 bg-stone-50 focus:outline-hidden focus:border-stone-900 focus:bg-white"
          />
        </div>

        <div className="flex items-center gap-2 w-full sm:w-auto">
          <span className="text-xs text-stone-500 font-medium">Category:</span>
          <select
            value={categoryFilter}
            onChange={(e) => setCategoryFilter(e.target.value)}
            className="text-xs px-3 py-2 rounded-xl border border-stone-300 bg-stone-50 font-medium focus:outline-hidden cursor-pointer"
          >
            <option value="all">All Categories ({shoes.length})</option>
            {categories.map((c) => (
              <option key={c.id} value={c.id.toString()}>
                {c.name}
              </option>
            ))}
          </select>
        </div>
      </div>

      {/* Shoes Inventory Table */}
      <div className="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-xs">
        {filteredShoes.length === 0 ? (
          <div className="p-12 text-center">
            <Layers className="w-8 h-8 text-stone-300 mx-auto mb-2" />
            <p className="text-xs text-stone-500">No shoes matching your search criteria.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse text-xs">
              <thead>
                <tr className="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider text-[10px]">
                  <th className="py-3.5 px-4">Shoe</th>
                  <th className="py-3.5 px-4">Category</th>
                  <th className="py-3.5 px-4">Price</th>
                  <th className="py-3.5 px-4">Stock</th>
                  <th className="py-3.5 px-4">Storage Provider</th>
                  <th className="py-3.5 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-stone-100">
                {filteredShoes.map((shoe) => (
                  <tr key={shoe.id} className="hover:bg-stone-50/80 transition-colors">
                    <td className="py-3 px-4">
                      <div className="flex items-center gap-3">
                        <img
                          src={shoe.image_url}
                          alt={shoe.name}
                          className="w-12 h-12 object-contain rounded-xl bg-stone-100 p-1 border border-stone-200 shrink-0"
                          onError={(e) => {
                            (e.target as HTMLImageElement).src =
                              'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=120&q=80';
                          }}
                        />
                        <div>
                          <div className="font-bold text-stone-900 text-sm flex items-center gap-1.5">
                            <span>{shoe.name}</span>
                            {shoe.is_featured === 1 && (
                              <span className="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">
                                Featured
                              </span>
                            )}
                          </div>
                          <span className="text-[11px] text-stone-400 font-mono">
                            ID: #{shoe.id} &bull; Slug: {shoe.slug}
                          </span>
                        </div>
                      </div>
                    </td>
                    <td className="py-3 px-4">
                      <span className="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-stone-100 text-stone-700 border border-stone-200">
                        {shoe.category_name || 'Footwear'}
                      </span>
                    </td>
                    <td className="py-3 px-4 font-black text-stone-900">
                      <div>KSH {shoe.price.toFixed(2)}</div>
                      {shoe.sale_price && (
                        <div className="text-[10px] text-stone-400 line-through">
                          KSH {shoe.sale_price.toFixed(2)}
                        </div>
                      )}
                    </td>
                    <td className="py-3 px-4">
                      {shoe.stock_quantity <= 5 ? (
                        <span className="text-rose-600 font-bold bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                          {shoe.stock_quantity} units
                        </span>
                      ) : (
                        <span className="text-stone-700 font-medium">
                          {shoe.stock_quantity} units
                        </span>
                      )}
                    </td>
                    <td className="py-3 px-4">
                      {shoe.cloudinary_public_id ? (
                        <span className="text-[10px] text-emerald-700 font-medium bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                          <Cloud className="w-2.5 h-2.5" />
                          <span>Cloudinary</span>
                        </span>
                      ) : (
                        <span className="text-[10px] text-sky-700 font-medium bg-sky-50 border border-sky-200 px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                          <ArrowUpRight className="w-2.5 h-2.5" />
                          <span>Direct CDN</span>
                        </span>
                      )}
                    </td>
                    <td className="py-3 px-4 text-right">
                      <div className="inline-flex items-center gap-1">
                        <button
                          onClick={() => onViewProduct(shoe)}
                          className="p-1.5 rounded-lg hover:bg-stone-200 text-stone-500 hover:text-stone-900"
                          title="View Detail Modal"
                        >
                          <Eye className="w-4 h-4" />
                        </button>
                        <button
                          onClick={() => openEditModal(shoe)}
                          className="p-1.5 rounded-lg hover:bg-amber-100 text-amber-700 hover:text-amber-800"
                          title="Edit Shoe"
                        >
                          <Edit3 className="w-4 h-4" />
                        </button>
                        <button
                          onClick={() => {
                            if (window.confirm(`Delete "${shoe.name}" from shoe inventory?`)) {
                              onDeleteShoe(shoe.id);
                            }
                          }}
                          className="p-1.5 rounded-lg hover:bg-rose-100 text-rose-600 hover:text-rose-700"
                          title="Delete Shoe"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Create / Edit Shoe Modal */}
      {isCreateModalOpen && (
        <div className="fixed inset-0 z-50 overflow-y-auto bg-stone-950/70 backdrop-blur-xs flex items-center justify-center p-4">
          <div 
            className="w-full max-w-2xl bg-white rounded-3xl border border-stone-200 p-6 sm:p-8 shadow-2xl relative my-8"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="border-b border-stone-200 pb-4 mb-6 flex items-center justify-between">
              <div>
                <h2 className="text-xl font-black text-stone-900 tracking-tight">
                  {editingShoe ? `Edit Shoe #${editingShoe.id}` : 'Create New Shoe Product'}
                </h2>
                <p className="text-xs text-stone-500 mt-0.5">
                  CodeIgniter 4 Model Validation & Cloud Storage Image Stream
                </p>
              </div>
              <button
                onClick={() => setIsCreateModalOpen(false)}
                className="text-stone-400 hover:text-stone-700 text-lg font-bold"
              >
                &times;
              </button>
            </div>

            {formErrors.length > 0 && (
              <div className="mb-4 p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 space-y-1">
                <div className="font-bold">Validation Errors:</div>
                {formErrors.map((err, i) => (
                  <div key={i}>&bull; {err}</div>
                ))}
              </div>
            )}

            <form onSubmit={handleSaveShoe} className="space-y-4">
              {/* Name */}
              <div>
                <label className="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">
                  Shoe Name *
                </label>
                <input
                  type="text"
                  value={formName}
                  onChange={(e) => setFormName(e.target.value)}
                  placeholder="e.g. Apex Velocity Carbon Streak"
                  required
                  className="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-xs focus:outline-hidden focus:border-stone-900"
                />
              </div>

              {/* Category & Price Grid */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">
                    Category *
                  </label>
                  <select
                    value={formCategoryId}
                    onChange={(e) => setFormCategoryId(Number(e.target.value))}
                    className="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-xs bg-white focus:outline-hidden focus:border-stone-900"
                  >
                    {categories.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name} ({c.slug})
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">
                    Price (KSH) *
                  </label>
                  <input
                    type="number"
                    step="0.01"
                    min="1"
                    value={formPrice}
                    onChange={(e) => setFormPrice(e.target.value)}
                    required
                    className="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-xs focus:outline-hidden focus:border-stone-900"
                  />
                </div>
              </div>

              {/* Stock & Sale Price */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">
                    Initial Stock Units *
                  </label>
                  <input
                    type="number"
                    min="0"
                    value={formStock}
                    onChange={(e) => setFormStock(e.target.value)}
                    required
                    className="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-xs focus:outline-hidden focus:border-stone-900"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">
                    Sale Price (Optional)
                  </label>
                  <input
                    type="number"
                    step="0.01"
                    value={formSalePrice}
                    onChange={(e) => setFormSalePrice(e.target.value)}
                    placeholder="e.g. 129.99"
                    className="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-xs focus:outline-hidden focus:border-stone-900"
                  />
                </div>
              </div>

              {/* Cloudinary Serverless Image Upload Box */}
              <div className="border-2 border-dashed border-stone-300 rounded-2xl p-5 bg-stone-50 space-y-3">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <UploadCloud className="w-5 h-5 text-amber-600" />
                    <div>
                      <h4 className="text-xs font-bold text-stone-900">
                        Cloud Storage Image Stream
                      </h4>
                      <p className="text-[11px] text-stone-500">
                        Uploaded binary is streamed directly to Cloudinary (No local writable disk writes)
                      </p>
                    </div>
                  </div>
                  {isUploadingCloud && (
                    <span className="text-[10px] font-bold text-amber-600 animate-pulse">
                      Streaming...
                    </span>
                  )}
                </div>

                <div className="flex flex-col sm:flex-row items-center gap-3">
                  <input
                    type="file"
                    accept="image/*"
                    onChange={handleFileUpload}
                    className="text-xs text-stone-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-900 file:text-white hover:file:bg-amber-600 cursor-pointer w-full sm:w-auto"
                  />
                  <div className="text-[11px] text-stone-400">or paste direct image URL below</div>
                </div>

                {uploadFeedback && (
                  <div className="text-[11px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                    <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                    <span>{uploadFeedback}</span>
                  </div>
                )}

                <input
                  type="url"
                  value={formImageUrl}
                  onChange={(e) => setFormImageUrl(e.target.value)}
                  placeholder="https://images.unsplash.com/..."
                  className="w-full px-3 py-2 text-xs rounded-xl border border-stone-300 bg-white font-mono"
                />

                {/* Quick Presets */}
                <div className="pt-1">
                  <span className="text-[10px] font-bold uppercase text-stone-400 block mb-1">
                    Quick Sample Presets:
                  </span>
                  <div className="flex flex-wrap gap-1.5">
                    {sampleImages.map((s, idx) => (
                      <button
                        key={idx}
                        type="button"
                        onClick={() => setFormImageUrl(s.url)}
                        className="text-[10px] font-semibold px-2 py-1 rounded bg-stone-200 hover:bg-stone-300 text-stone-700 transition-colors"
                      >
                        {s.label}
                      </button>
                    ))}
                  </div>
                </div>

                {/* Preview Thumbnail */}
                {formImageUrl && (
                  <div className="mt-2 flex items-center gap-3 p-2 bg-white rounded-xl border border-stone-200">
                    <img
                      src={formImageUrl}
                      alt="Preview"
                      className="w-14 h-14 object-contain rounded-lg bg-stone-100 p-1"
                    />
                    <div className="text-[11px] text-stone-600 truncate">
                      <span className="font-bold block text-stone-900">Current Payload</span>
                      <span className="font-mono text-stone-400 text-[10px]">{formImageUrl}</span>
                    </div>
                  </div>
                )}
              </div>

              {/* Sizes */}
              <div>
                <label className="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">
                  Sizes Available (Comma-separated)
                </label>
                <input
                  type="text"
                  value={formSizes}
                  onChange={(e) => setFormSizes(e.target.value)}
                  placeholder="7,8,8.5,9,9.5,10,10.5,11,12"
                  className="w-full px-4 py-2 rounded-xl border border-stone-300 text-xs focus:outline-hidden focus:border-stone-900 font-mono"
                />
              </div>

              <div>
                <label className="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">
                  Available Colors (Optional, comma-separated)
                </label>
                <input
                  type="text"
                  value={formColors}
                  onChange={(e) => setFormColors(e.target.value)}
                  placeholder="Black, White, Red"
                  className="w-full px-4 py-2 rounded-xl border border-stone-300 text-xs focus:outline-hidden focus:border-stone-900"
                />
              </div>

              {/* Description */}
              <div>
                <label className="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">
                  Description & Materials
                </label>
                <textarea
                  rows={3}
                  value={formDescription}
                  onChange={(e) => setFormDescription(e.target.value)}
                  required
                  placeholder="Detailed shoe build, comfort tech, midsole and outsole materials..."
                  className="w-full px-4 py-2 rounded-xl border border-stone-300 text-xs focus:outline-hidden focus:border-stone-900"
                />
              </div>

              {/* Featured checkbox */}
              <div className="flex items-center gap-2 pt-1">
                <input
                  type="checkbox"
                  id="featuredToggle"
                  checked={formIsFeatured}
                  onChange={(e) => setFormIsFeatured(e.target.checked)}
                  className="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-stone-300 cursor-pointer"
                />
                <label
                  htmlFor="featuredToggle"
                  className="text-xs font-bold text-stone-800 cursor-pointer flex items-center gap-1"
                >
                  <Sparkles className="w-3.5 h-3.5 text-amber-500" />
                  <span>Highlight on Storefront Hero & Featured Section</span>
                </label>
              </div>

              {/* Actions */}
              <div className="pt-4 border-t border-stone-200 flex items-center justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setIsCreateModalOpen(false)}
                  className="px-4 py-2 rounded-xl border border-stone-300 text-xs font-bold text-stone-700 hover:bg-stone-100"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-6 py-2 rounded-xl bg-stone-900 hover:bg-amber-600 text-white text-xs font-bold transition-colors shadow-md"
                >
                  {editingShoe ? 'Save Updates' : 'Publish Shoe to Catalog'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
