/**
 * Admin API Client
 * Handles all admin panel API requests
 */

const ADMIN_API_BASE = import.meta.env.VITE_API_URL 
  ? import.meta.env.VITE_API_URL.replace('/api', '/api/admin')
  : 'http://localhost/sinath-travels/server/api/admin';

/**
 * Admin API request with authentication
 */
async function adminApiRequest<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const url = `${ADMIN_API_BASE}/${endpoint}`;
  
  const config: RequestInit = {
    ...options,
    credentials: 'include', // Important for session cookies
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
  };

  try {
    const response = await fetch(url, config);
    
    if (response.status === 401) {
      // Redirect to login on authentication failure
      window.location.href = '/admin/login';
      throw new Error('Authentication required');
    }
    
    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Request failed' }));
      throw new Error(error.message || `HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    return data.data || data;
  } catch (error) {
    console.error('Admin API request failed:', error);
    throw error;
  }
}

// ====================
// AUTHENTICATION
// ====================

export interface AdminUser {
  id: number;
  username: string;
  email: string;
  full_name: string;
  role: 'superadmin' | 'admin' | 'editor';
}

export interface LoginCredentials {
  username: string;
  password: string;
}

export interface LoginResponse {
  token: string;
  admin: AdminUser;
}

export async function adminLogin(credentials: LoginCredentials): Promise<LoginResponse> {
  return adminApiRequest<LoginResponse>('auth.php?action=login', {
    method: 'POST',
    body: JSON.stringify(credentials),
  });
}

export async function adminLogout(): Promise<void> {
  return adminApiRequest<void>('auth.php?action=logout', {
    method: 'POST',
  });
}

export async function checkAdminAuth(): Promise<{ admin: AdminUser }> {
  return adminApiRequest<{ admin: AdminUser }>('auth.php');
}

// ====================
// PACKAGES MANAGEMENT
// ====================

export interface AdminPackage {
  id?: number;
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
}

export async function getAdminPackages(params?: {
  status?: string;
  category?: string;
}): Promise<AdminPackage[]> {
  const queryParams = new URLSearchParams();
  if (params?.status) queryParams.append('status', params.status);
  if (params?.category) queryParams.append('category', params.category);
  
  const query = queryParams.toString();
  return adminApiRequest<AdminPackage[]>(`packages.php${query ? '?' + query : ''}`);
}

export async function getAdminPackage(id: number): Promise<AdminPackage> {
  return adminApiRequest<AdminPackage>(`packages.php?id=${id}`);
}

export async function createAdminPackage(formData: FormData): Promise<{ id: number }> {
  const url = `${ADMIN_API_BASE}/packages.php`;
  
  const response = await fetch(url, {
    method: 'POST',
    credentials: 'include',
    body: formData, // Don't set Content-Type, browser will set it with boundary
  });
  
  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: 'Request failed' }));
    throw new Error(error.message);
  }
  
  const data = await response.json();
  return data.data || data;
}

export async function updateAdminPackage(id: number, data: Partial<AdminPackage>): Promise<void> {
  const formData = new URLSearchParams();
  formData.append('id', id.toString());
  Object.entries(data).forEach(([key, value]) => {
    if (value !== undefined && value !== null) {
      formData.append(key, value.toString());
    }
  });
  
  return adminApiRequest<void>('packages.php', {
    method: 'PUT',
    body: formData.toString(),
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
  });
}

export async function deleteAdminPackage(id: number): Promise<void> {
  return adminApiRequest<void>('packages.php', {
    method: 'DELETE',
    body: `id=${id}`,
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
  });
}

// ====================
// INQUIRIES MANAGEMENT
// ====================

export interface AdminInquiry {
  id: number;
  name: string;
  email: string;
  phone: string;
  message: string;
  status: 'new' | 'read' | 'replied';
  created_at: string;
}

export interface InquiriesResponse {
  inquiries: AdminInquiry[];
  pagination: {
    total: number;
    limit: number;
    offset: number;
    pages: number;
  };
  stats: {
    total: number;
    new_count: number;
    read_count: number;
    replied_count: number;
  };
}

export async function getAdminInquiries(params?: {
  status?: string;
  limit?: number;
  offset?: number;
  search?: string;
}): Promise<InquiriesResponse> {
  const queryParams = new URLSearchParams();
  if (params?.status) queryParams.append('status', params.status);
  if (params?.limit) queryParams.append('limit', params.limit.toString());
  if (params?.offset) queryParams.append('offset', params.offset.toString());
  if (params?.search) queryParams.append('search', params.search);
  
  const query = queryParams.toString();
  return adminApiRequest<InquiriesResponse>(`inquiries.php${query ? '?' + query : ''}`);
}

export async function updateInquiryStatus(id: number, status: 'new' | 'read' | 'replied'): Promise<void> {
  return adminApiRequest<void>('inquiries.php', {
    method: 'PUT',
    body: `id=${id}&status=${status}`,
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
  });
}

export async function deleteInquiry(id: number): Promise<void> {
  return adminApiRequest<void>('inquiries.php', {
    method: 'DELETE',
    body: `id=${id}`,
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
  });
}