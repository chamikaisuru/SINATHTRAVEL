/**
 * COMPLETE Admin API Client - Token-Based Authentication
 * Replace ENTIRE contents of: client/src/lib/adminApi.ts
 */

const ADMIN_API_BASE = import.meta.env.VITE_API_URL?.replace('/api', '/api/admin') ||
  '/api/admin';

console.log('🔧 Admin API Base URL:', ADMIN_API_BASE);

/**
 * Helper to get auth headers
 */
function getAuthHeaders(): HeadersInit {
  const token = localStorage.getItem('admin_token');
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  return headers;
}

/**
 * Admin User Type
 */
export interface AdminUser {
  id: number;
  username: string;
  email: string;
  full_name: string;
  role: 'superadmin' | 'admin' | 'editor';
}

/**
 * Admin Package Type
 */
export interface AdminPackage {
  id?: number;
  category: 'tour' | 'visa' | 'ticket' | 'offer';
  title_en: string;
  description_en: string;
  price: number;
  duration?: string;
  image?: string;
  status: 'active' | 'inactive';
  featured_type?: 'popular' | 'special_offer' | 'seasonal' | null;
  created_at?: string;
}

/**
 * Admin Inquiry Type
 */
export interface AdminInquiry {
  id: number;
  name: string;
  email: string;
  phone: string;
  message: string;
  status: 'new' | 'read' | 'replied';
  created_at: string;
}

/**
 * LOGIN
 */
export async function adminLogin(credentials: { username: string; password: string }) {
  console.log('🔵 adminLogin called with:', credentials.username);

  const url = `${ADMIN_API_BASE}/auth.php?action=login`;
  console.log('🔵 Calling:', url);

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(credentials),
    });

    console.log('🔵 Response status:', response.status);

    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Login failed' }));
      console.error('❌ Login error:', error);
      throw new Error(error.message || 'Login failed');
    }

    const result = await response.json();
    console.log('✅ Login successful:', result);

    // Store token in localStorage
    if (result.data?.token) {
      localStorage.setItem('admin_token', result.data.token);
      console.log('💾 Token stored in localStorage');
    }

    return result.data;
  } catch (error) {
    console.error('❌ adminLogin error:', error);
    throw error;
  }
}

/**
 * LOGOUT
 */
export async function adminLogout(): Promise<void> {
  const url = `${ADMIN_API_BASE}/auth.php?action=logout`;

  const token = localStorage.getItem('admin_token');

  const response = await fetch(url, {
    method: 'POST',
    headers: token ? { 'Authorization': `Bearer ${token}` } : {},
  });

  // Clear token regardless of response
  localStorage.removeItem('admin_token');

  if (!response.ok) {
    throw new Error('Logout failed');
  }
}

/**
 * CHECK AUTH
 */
export async function checkAdminAuth(): Promise<{ admin: AdminUser } | null> {
  console.log('🔍 checkAdminAuth called');

  const token = localStorage.getItem('admin_token');

  if (!token) {
    console.log('❌ No token in localStorage');
    return null;
  }

  const url = `${ADMIN_API_BASE}/auth.php`;
  console.log('🔍 URL:', url);

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`
      },
    });

    console.log('🔍 Auth check status:', response.status);

    if (response.status === 401) {
      console.log('❌ Not authenticated - clearing token');
      localStorage.removeItem('admin_token');
      return null;
    }

    if (!response.ok) {
      throw new Error('Auth check failed');
    }

    const result = await response.json();
    console.log('✅ Auth check result:', result);

    return result.data;
  } catch (error) {
    console.error('❌ checkAdminAuth error:', error);
    localStorage.removeItem('admin_token');
    return null;
  }
}

/**
 * GET PACKAGES
 */
export async function getAdminPackages(): Promise<AdminPackage[]> {
  console.log('📦 getAdminPackages called');

  const url = `${ADMIN_API_BASE}/packages.php`;
  console.log('📦 URL:', url);

  const response = await fetch(url, {
    headers: getAuthHeaders(),
  });

  console.log('📦 Response status:', response.status);

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: 'Failed to fetch packages' }));
    throw new Error(error.message);
  }

  const result = await response.json();
  console.log('📦 Raw result:', result);

  const packages = result.data || [];
  console.log('📦 Extracted packages:', packages);

  return packages;
}

/**
 * CREATE PACKAGE
 */
export async function createAdminPackage(data: FormData): Promise<AdminPackage> {
  const url = `${ADMIN_API_BASE}/packages.php`;

  // For FormData, don't set Content-Type header
  const token = localStorage.getItem('admin_token');
  const headers: HeadersInit = {};
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(url, {
    method: 'POST',
    headers,
    body: data,
  });

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: 'Failed to create package' }));
    throw new Error(error.message);
  }

  const result = await response.json();
  return result.data;
}

/**
 * UPDATE PACKAGE
 */
export async function updateAdminPackage(
  id: number,
  data: Partial<AdminPackage> | FormData
): Promise<void> {
  const url = `${ADMIN_API_BASE}/packages.php`;

  console.log('📝 Updating package:', id);
  console.log('📝 Data type:', data instanceof FormData ? 'FormData' : 'Object');

  let body: any;
  const token = localStorage.getItem('admin_token');
  let headers: HeadersInit = {};

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  if (data instanceof FormData) {
    data.append('id', id.toString());
    data.append('_method', 'PUT');
    data.append('action', 'update');
    body = data;
  } else {
    const formData = new URLSearchParams();
    formData.append('id', id.toString());

    Object.entries(data).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        formData.append(key, value.toString());
      }
    });

    body = formData.toString();
    headers['Content-Type'] = 'application/x-www-form-urlencoded';
  }

  const response = await fetch(url, {
    method: data instanceof FormData ? 'POST' : 'PUT',
    headers,
    body,
  });

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: 'Request failed' }));
    throw new Error(error.message);
  }

  const result = await response.json();
  console.log('✅ Package updated:', result);

  return result.data || result;
}

/**
 * DELETE PACKAGE
 */
export async function deleteAdminPackage(id: number): Promise<void> {
  const url = `${ADMIN_API_BASE}/packages.php`;

  const formData = new URLSearchParams();
  formData.append('id', id.toString());

  const token = localStorage.getItem('admin_token');
  const headers: HeadersInit = {
    'Content-Type': 'application/x-www-form-urlencoded',
  };
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(url, {
    method: 'DELETE',
    headers,
    body: formData.toString(),
  });

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: 'Failed to delete package' }));
    throw new Error(error.message);
  }
}

/**
 * GET INQUIRIES
 */
export async function getAdminInquiries(params?: {
  status?: string;
  search?: string;
  limit?: number;
  offset?: number;
}) {
  const queryParams = new URLSearchParams();

  if (params?.status) queryParams.append('status', params.status);
  if (params?.search) queryParams.append('search', params.search);
  if (params?.limit) queryParams.append('limit', params.limit.toString());
  if (params?.offset) queryParams.append('offset', params.offset.toString());

  const url = `${ADMIN_API_BASE}/inquiries.php?${queryParams.toString()}`;

  const response = await fetch(url, {
    headers: getAuthHeaders(),
  });

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: 'Failed to fetch inquiries' }));
    throw new Error(error.message);
  }

  const result = await response.json();
  return result.data;
}

/**
 * UPDATE INQUIRY STATUS
 */
export async function updateInquiryStatus(
  id: number,
  status: 'new' | 'read' | 'replied'
): Promise<void> {
  const url = `${ADMIN_API_BASE}/inquiries.php`;

  const formData = new URLSearchParams();
  formData.append('id', id.toString());
  formData.append('status', status);

  const token = localStorage.getItem('admin_token');
  const headers: HeadersInit = {
    'Content-Type': 'application/x-www-form-urlencoded',
  };
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(url, {
    method: 'PUT',
    headers,
    body: formData.toString(),
  });

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: 'Failed to update inquiry' }));
    throw new Error(error.message);
  }
}

/**
 * DELETE INQUIRY
 */
export async function deleteInquiry(id: number): Promise<void> {
  const url = `${ADMIN_API_BASE}/inquiries.php`;

  const formData = new URLSearchParams();
  formData.append('id', id.toString());

  const token = localStorage.getItem('admin_token');
  const headers: HeadersInit = {
    'Content-Type': 'application/x-www-form-urlencoded',
  };
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(url, {
    method: 'DELETE',
    headers,
    body: formData.toString(),
  });

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: 'Failed to delete inquiry' }));
    throw new Error(error.message);
  }
}