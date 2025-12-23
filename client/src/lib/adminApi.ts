/**
 * FIXED: Update package with file upload support
 * Replace ONLY the updateAdminPackage function in: client/src/lib/adminApi.ts
 */

/**
 * UPDATE PACKAGE - FIXED TO SUPPORT FILE UPLOAD
 */
export async function updateAdminPackage(
  id: number, 
  data: Partial<AdminPackage> | FormData
): Promise<void> {
  const url = `${ADMIN_API_BASE}/packages.php`;
  
  console.log('📝 Updating package:', id);
  console.log('📝 Data type:', data instanceof FormData ? 'FormData' : 'Object');
  
  let body: any;
  let headers: Record<string, string> = {};
  
  if (data instanceof FormData) {
    // FormData (with file) - send as POST with ID
    console.log('📝 Sending as FormData (file upload)');
    data.append('id', id.toString());
    body = data;
    // Don't set Content-Type - browser will set it with boundary
  } else {
    // Regular data (no file) - send as URL encoded PUT
    console.log('📝 Sending as URL encoded PUT');
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
    credentials: 'include',
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