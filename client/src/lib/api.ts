/**
 * API Client - FIXED IMAGE HANDLING
 * Replace: client/src/lib/api.ts
 */

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080/server/api';

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
 * FIXED: Get image URL - handles both stock and uploaded images
 * Backend now sends the correct path, we just need to pass it through
 */
export function getImageUrl(imagePath?: string): string {
  if (!imagePath) return '';
  
  console.log('🖼️ Frontend getImageUrl received:', imagePath);
  
  // If already a full URL, return as is
  if (imagePath.startsWith('http')) {
    console.log('🖼️ Full URL, returning as is');
    return imagePath;
  }
  
  // If it's a stock image path (starts with /src/assets)
  if (imagePath.startsWith('/src/assets/stock_images/')) {
    // Extract just the filename
    const filename = imagePath.split('/').pop() || '';
    console.log('🖼️ Stock image, using Vite import:', filename);
    
    try {
      // Use Vite's asset import
      return new URL(`/attached_assets/stock_images/${filename}`, import.meta.url).href;
    } catch (e) {
      console.error('Failed to load stock image:', filename, e);
      return `/attached_assets/stock_images/${filename}`;
    }
  }
  
  // Otherwise, assume backend gave us correct path
  console.log('🖼️ Using path as provided by backend');
  return imagePath;
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