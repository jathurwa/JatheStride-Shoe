import { Category, Shoe } from '../types';
export { CI4_CODE_FILES } from './ci4Codebase';

export const INITIAL_CATEGORIES: Category[] = [
  {
    id: 1,
    name: 'Sneakers',
    slug: 'sneakers',
    description: 'Low-profile and high-top street lifestyle footwear.',
    icon: 'fa-bolt',
  },
  {
    id: 2,
    name: 'Running',
    slug: 'running',
    description: 'Ultralight responsive carbon-plated and cushioned runners.',
    icon: 'fa-person-running',
  },
  {
    id: 3,
    name: 'Boots',
    slug: 'boots',
    description: 'Rugged, water-resistant handcrafted leather explorer boots.',
    icon: 'fa-mountain',
  },
  {
    id: 4,
    name: 'Formal',
    slug: 'formal',
    description: 'Sleek Goodyear-welted oxfords, brogues and derby dress shoes.',
    icon: 'fa-briefcase',
  },
  {
    id: 5,
    name: 'Casual',
    slug: 'casual',
    description: 'Comfortable everyday shoes for relaxed, versatile wear.',
    icon: 'fa-shoe-prints',
  },
];

export const INITIAL_SHOES: Shoe[] = [
  {
    id: 1,
    name: 'Apex Volt HyperGlide Runner',
    slug: 'apex-volt-hyperglide-runner',
    category_id: 2,
    category_name: 'Running',
    category_slug: 'running',
    price: 179.99,
    sale_price: 159.99,
    description:
      'Ultra-responsive carbon fiber sprint plate paired with dual-density nitrogen foam for effortless energy return over marathon distances. Engineered mesh upper promotes continuous airflow.',
    image_url:
      'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=80',
    cloudinary_public_id: 'shoes/apex_volt_hyperglide_runner_cld',
    sizes_available: '8,8.5,9,9.5,10,10.5,11,12',
    is_featured: 1,
    stock_quantity: 35,
  },
  {
    id: 2,
    name: 'Retro High Court Classic',
    slug: 'retro-high-court-classic',
    category_id: 1,
    category_name: 'Sneakers',
    category_slug: 'sneakers',
    price: 139.99,
    sale_price: null,
    description:
      'Premium tumble leather silhouette with iconic color blocking, perforated toe box, and vintage off-white vulcanized rubber outsole for timeless street appeal.',
    image_url:
      'https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=1000&q=80',
    cloudinary_public_id: 'shoes/retro_court_classic_cld',
    sizes_available: '7,8,8.5,9,9.5,10,11,12',
    is_featured: 1,
    stock_quantity: 18,
  },
  {
    id: 3,
    name: 'IronClad Heritage Waterproof Boot',
    slug: 'ironclad-heritage-waterproof-boot',
    category_id: 3,
    category_name: 'Boots',
    category_slug: 'boots',
    price: 219.99,
    sale_price: 199.99,
    description:
      'Full-grain waxed nubuck leather with storm-welt construction and Vibram commando lug tread built to conquer rugged mountain trails or wet city pavement.',
    image_url:
      'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=1000&q=80',
    cloudinary_public_id: 'shoes/ironclad_boot_cld',
    sizes_available: '8,9,9.5,10,10.5,11,12',
    is_featured: 1,
    stock_quantity: 12,
  },
  {
    id: 4,
    name: 'Kensington Hand-Burnished Oxford',
    slug: 'kensington-hand-burnished-oxford',
    category_id: 4,
    category_name: 'Formal',
    category_slug: 'formal',
    price: 249.99,
    sale_price: null,
    description:
      'Crafted from full-grain Italian calfskin with a classic cap toe, closed lacing system, and hand-finished dark cognac patina for executive elegance.',
    image_url:
      'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=1000&q=80',
    cloudinary_public_id: null,
    sizes_available: '8,8.5,9,9.5,10,10.5,11',
    is_featured: 0,
    stock_quantity: 9,
  },
  {
    id: 5,
    name: 'AirPulse Velocity Pro',
    slug: 'airpulse-velocity-pro',
    category_id: 2,
    category_name: 'Running',
    category_slug: 'running',
    price: 164.5,
    sale_price: null,
    description:
      'Breathable engineered mesh upper with dynamic FlyWire lockdown cables and sculpted aerodynamic heel crash pads for explosive road sprints.',
    image_url:
      'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=1000&q=80',
    cloudinary_public_id: 'shoes/airpulse_velocity_cld',
    sizes_available: '7.5,8,8.5,9,9.5,10,10.5,11,12',
    is_featured: 0,
    stock_quantity: 24,
  },
  {
    id: 6,
    name: 'Urban Nomad Minimalist Sneaker',
    slug: 'urban-nomad-minimalist-sneaker',
    category_id: 1,
    category_name: 'Sneakers',
    category_slug: 'sneakers',
    price: 119.0,
    sale_price: 99.0,
    description:
      'Ultra-clean monochromatic low-top sneaker lined in vegetable-tanned leather with cushioned Ortholite memory insoles for all-day commuter comfort.',
    image_url:
      'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=1000&q=80',
    cloudinary_public_id: null,
    sizes_available: '7,8,8.5,9,10,11,12',
    is_featured: 1,
    stock_quantity: 40,
  },
  {
    id: 7,
    name: 'Timberland Scout Alpine Hiker',
    slug: 'timberland-scout-alpine-hiker',
    category_id: 3,
    category_name: 'Boots',
    category_slug: 'boots',
    price: 189.99,
    sale_price: null,
    description:
      'Thinsulate thermal lining with rust-proof D-ring hardware and sealed waterproof bootie construction for snow, cold weather, and wet forest dirt.',
    image_url:
      'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=1000&q=80',
    cloudinary_public_id: null,
    sizes_available: '8,8.5,9,9.5,10,10.5,11,12',
    is_featured: 0,
    stock_quantity: 15,
  },
  {
    id: 8,
    name: 'Savile Row Double Monk Strap',
    slug: 'savile-row-double-monk-strap',
    category_id: 4,
    category_name: 'Formal',
    category_slug: 'formal',
    price: 229.0,
    sale_price: 199.0,
    description:
      'Polished midnight black leather with silver-finish buckle hardware, channeled leather sole, and gentleman bevel waist for black-tie affairs.',
    image_url:
      'https://images.unsplash.com/photo-1533867617858-e7b97e060509?auto=format&fit=crop&w=1000&q=80',
    cloudinary_public_id: null,
    sizes_available: '8,9,9.5,10,10.5,11',
    is_featured: 0,
    stock_quantity: 7,
  },
];
