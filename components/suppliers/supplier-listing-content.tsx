"use client"

import { useState, useEffect } from "react"
import { useSearchParams } from "next/navigation"
import { SupplierFilters } from "./supplier-filters"
import { SupplierCard } from "./supplier-card"
import { Button } from "@/components/ui/button"
import { API_ENDPOINTS, apiCall } from "@/lib/api-config"
import { Loader2 } from "lucide-react"

export interface Supplier {
  id: string
  name: string
  logo: string
  industry: string
  country: string
  verified: boolean
  premium: boolean
  description: string
  establishedYear: number
}

export function SupplierListingContent() {
  const searchParams = useSearchParams()
  const [suppliers, setSuppliers] = useState<Supplier[]>([])
  const [loading, setLoading] = useState(true)
  const [page, setPage] = useState(1)
  const [hasMore, setHasMore] = useState(true)

  const [filters, setFilters] = useState({
    industry: searchParams.get("category") || "",
    country: "",
    verified: false,
    premium: false,
    search: searchParams.get("search") || "",
  })

  useEffect(() => {
    fetchSuppliers()
  }, [filters, page])

  async function fetchSuppliers() {
    setLoading(true)
    try {
      const queryParams = new URLSearchParams()
      if (filters.industry) queryParams.append("industry", filters.industry)
      if (filters.country) queryParams.append("country", filters.country)
      if (filters.verified) queryParams.append("verified", "true")
      if (filters.premium) queryParams.append("premium", "true")
      if (filters.search) queryParams.append("search", filters.search)
      queryParams.append("page", page.toString())

      const endpoint = `${API_ENDPOINTS.SUPPLIERS}?${queryParams.toString()}`
      const data = await apiCall<{ suppliers: Supplier[]; hasMore: boolean }>(endpoint)

      if (page === 1) {
        setSuppliers(data.suppliers)
      } else {
        setSuppliers((prev) => [...prev, ...data.suppliers])
      }
      setHasMore(data.hasMore)
    } catch (error) {
      console.error("Failed to fetch suppliers:", error)
      // Fallback to mock data
      const mockSuppliers: Supplier[] = Array.from({ length: 12 }, (_, i) => ({
        id: `${i + 1}`,
        name: `Supplier Company ${i + 1}`,
        logo: "/generic-company-logo.png",
        industry: ["Electronics", "Textiles", "Machinery", "Food & Beverage"][i % 4],
        country: ["China", "India", "Germany", "Vietnam", "USA"][i % 5],
        verified: i % 3 === 0,
        premium: i % 4 === 0,
        description: "Leading manufacturer and exporter with over 15 years of experience in the industry.",
        establishedYear: 2005 + (i % 10),
      }))
      setSuppliers(mockSuppliers)
      setHasMore(false)
    } finally {
      setLoading(false)
    }
  }

  const handleFilterChange = (newFilters: typeof filters) => {
    setFilters(newFilters)
    setPage(1)
  }

  const loadMore = () => {
    setPage((prev) => prev + 1)
  }

  return (
    <div className="py-8">
      <div className="container mx-auto px-4">
        <div className="mb-8">
          <h1 className="text-3xl md:text-4xl font-bold mb-2">Find Suppliers</h1>
          <p className="text-muted-foreground">Browse verified suppliers and manufacturers from around the world</p>
        </div>

        <div className="flex flex-col lg:flex-row gap-8">
          {/* Sidebar Filters */}
          <aside className="lg:w-64 flex-shrink-0">
            <SupplierFilters filters={filters} onFilterChange={handleFilterChange} />
          </aside>

          {/* Main Content */}
          <div className="flex-1">
            {loading && page === 1 ? (
              <div className="flex items-center justify-center py-12">
                <Loader2 className="h-8 w-8 animate-spin text-primary" />
              </div>
            ) : (
              <>
                <div className="mb-4 text-sm text-muted-foreground">
                  Showing {suppliers.length} supplier{suppliers.length !== 1 ? "s" : ""}
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                  {suppliers.map((supplier) => (
                    <SupplierCard key={supplier.id} supplier={supplier} />
                  ))}
                </div>

                {suppliers.length === 0 && (
                  <div className="text-center py-12">
                    <p className="text-muted-foreground">No suppliers found matching your criteria.</p>
                  </div>
                )}

                {hasMore && suppliers.length > 0 && (
                  <div className="text-center">
                    <Button onClick={loadMore} disabled={loading} size="lg">
                      {loading ? (
                        <>
                          <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                          Loading...
                        </>
                      ) : (
                        "Load More"
                      )}
                    </Button>
                  </div>
                )}
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}
