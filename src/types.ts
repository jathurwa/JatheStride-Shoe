export interface Category {
  id: number;
  name: string;
  slug: string;
  description: string;
  icon: string;
}

export interface Shoe {
  id: number;
  name: string;
  slug: string;
  category_id: number;
  category_name?: string;
  category_slug?: string;
  price: number;
  sale_price?: number | null;
  description: string;
  image_url: string;
  cloudinary_public_id?: string | null;
  sizes_available: string;
  colors_available?: string | null;
  is_featured: number; // 0 or 1
  stock_quantity: number;
  created_at?: string;
  updated_at?: string;
}

export interface CartItem {
  shoe: Shoe;
  size: string;
  quantity: number;
}

export interface AdminUser {
  id: number;
  name: string;
  email: string;
  role: string;
}

export interface CI4FileEntry {
  path: string;
  category: 'config' | 'controller' | 'model' | 'library' | 'view' | 'devops' | 'sql';
  description: string;
  content: string;
}
