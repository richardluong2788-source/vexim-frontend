// API Configuration for Vexim Backend
export const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || "https://b2b.veximglobal.com"

export const API_ENDPOINTS = {
  // Supplier endpoints
  FEATURED_SUPPLIERS: `${API_BASE_URL}/api/suppliers/featured`,
  SUPPLIERS: `${API_BASE_URL}/api/suppliers/search`,
  SUPPLIER: (id: string) => `${API_BASE_URL}/api/suppliers/${id}`,
  SUPPLIER_PRODUCTS: (companyId: string) => `${API_BASE_URL}/api/products/search?company_id=${companyId}`,
  SUPPLIER_DASHBOARD: `${API_BASE_URL}/api/supplier/dashboard`,
  UPDATE_PROFILE: `${API_BASE_URL}/api/supplier/profile`,

  // Buyer dashboard endpoints
  BUYER_CONTACT_HISTORY: `${API_BASE_URL}/api/buyer/contact-history`,
  BUYER_RECOMMENDATIONS: `${API_BASE_URL}/api/buyer/recommendations`,

  // Auth endpoints
  LOGIN: `${API_BASE_URL}/api/auth/login`,
  REGISTER: `${API_BASE_URL}/api/auth/register`,
  LOGOUT: `${API_BASE_URL}/api/auth/logout`,

  // Dashboard endpoints
  DASHBOARD: `${API_BASE_URL}/api/dashboard`,
  CONTACTS: `${API_BASE_URL}/api/contacts`,

  // Package and subscription endpoints
  PACKAGES: `${API_BASE_URL}/api/packages`,
  SUBSCRIBE: `${API_BASE_URL}/api/subscribe`,
  CHECKOUT: `${API_BASE_URL}/api/payments/checkout`,

  // Contact and support
  CONTACT_REQUEST: `${API_BASE_URL}/api/contact-request`,
  SUPPORT: `${API_BASE_URL}/api/support`,
  SUPPORT_TICKETS: `${API_BASE_URL}/api/support/tickets`,
} as const

// Helper function for API calls with authentication support
export async function apiCall<T>(endpoint: string, options?: RequestInit): Promise<T> {
  const response = await fetch(endpoint, {
    ...options,
    headers: {
      "Content-Type": "application/json",
      ...options?.headers,
    },
    credentials: "include", // Added credentials to support authentication cookies
  })

  if (!response.ok) {
    throw new Error(`API call failed: ${response.statusText}`)
  }

  return response.json()
}
