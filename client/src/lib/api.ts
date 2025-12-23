/**
 * API Client - FIXED WITH STOCK IMAGES
 * Replace: client/src/lib/api.ts
 */

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost/sinath-travels/server/api';

async function apiRequest<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const url = `${API_BASE_URL}/${endpoint}`;
  
  const config: RequestInit = {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
  };

  try {
    const response = await fetch(url, config);
    
    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Request failed' }));
      throw new Error(error.message || `HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    return data.data || data;
  } catch (error) {
    console.error('API request failed:', error);
    throw error;
  }
}

export interface Package {
  id: number;
  category: 'tour' | 'visa' | 'ticket' | 'offer';
  title_en: string;
  title_si?: string;
  title_ta?: string;
  description_en: string;
  description_si?: string;
  description_ta?: string;
  price: number;
  duration?: string;
  image?: string;
  status: 'active' | 'inactive';
  created_at: string;
}

export async function getPackages(params?: {
  category?: string;
  status?: string;
  limit?: number;
}): Promise<Package[]> {
  const queryParams = new URLSearchParams();
  if (params?.category) queryParams.append('category', params.category);
  if (params?.status) queryParams.append('status', params.status);
  if (params?.limit) queryParams.append('limit', params.limit.toString());
  
  const query = queryParams.toString();
  return apiRequest<Package[]>(`packages.php${query ? '?' + query : ''}`);
}

export interface Service {
  id: number;
  icon: string;
  title_en: string;
  title_si?: string;
  title_ta?: string;
  description_en: string;
  description_si?: string;
  description_ta?: string;
  display_order: number;
  status: number;
}

export async function getServices(): Promise<Service[]> {
  return apiRequest<Service[]>('services.php');
}

export interface InquiryData {
  name: string;
  email: string;
  phone: string;
  message: string;
}

export interface InquiryResponse {
  id: number;
  message: string;
}

export async function submitInquiry(data: InquiryData): Promise<InquiryResponse> {
  return apiRequest<InquiryResponse>('inquiries.php', {
    method: 'POST',
    body: JSON.stringify(data),
  });
}

/**
 * FIXED: Get full image URL with stock images support
 */
export function getImageUrl(imagePath?: string): string {
  if (!imagePath) return '';
  
  // If already a full URL, return as is
  if (imagePath.startsWith('http')) return imagePath;
  
  // CRITICAL: Check if this is a stock image (no path separators)
  const isStockImage = !imagePath.includes('/') && !imagePath.includes('\\');
  
  if (isStockImage) {
    // Use Vite's asset import system for stock images
    try {
      // Import from attached_assets/stock_images
      return new URL(`/attached_assets/stock_images/${imagePath}`, import.meta.url).href;
    } catch (e) {
      console.error('Failed to load stock image:', imagePath, e);
      // Fallback
      return `/attached_assets/stock_images/${imagePath}`;
    }
  }
  
  // If it's a path from the server uploads
  if (imagePath.startsWith('/server/uploads/')) {
    return `${window.location.origin}${imagePath}`;
  }
  
  // If it's a relative server path
  if (imagePath.startsWith('/uploads/')) {
    return `${window.location.origin}/server${imagePath}`;
  }
  
  // Default case - assume it's in uploads
  return `${window.location.origin}/server/uploads/${imagePath}`;
}

/**
 * Format price with currency
 */
export function formatPrice(price: number, currency: string = 'USD'): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency,
  }).format(price);
}