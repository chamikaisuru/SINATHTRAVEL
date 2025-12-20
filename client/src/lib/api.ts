/**
 * API Client for Sinath Travels Backend
 * Handles all API requests to PHP backend
 */

// API base URL - adjust based on your server configuration
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost/sinath-travels/server/api';

/**
 * Generic API request function
 */
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

// ====================
// PACKAGES API
// ====================

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

// ====================
// SERVICES API
// ====================

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

// ====================
// INQUIRIES API
// ====================

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

// ====================
// HELPER FUNCTIONS
// ====================

/**
 * Get full image URL
 */
export function getImageUrl(imagePath?: string): string {
  if (!imagePath) return '';
  
  // If already a full URL, return as is
  if (imagePath.startsWith('http')) return imagePath;
  
  // If it's a path from the backend
  if (imagePath.startsWith('/server/uploads/')) {
    return `${window.location.origin}${imagePath}`;
  }
  
  // If it's just a filename from attached_assets (for backwards compatibility)
  if (!imagePath.startsWith('/')) {
    return new URL(`/src/assets/stock_images/${imagePath}`, import.meta.url).href;
  }
  
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