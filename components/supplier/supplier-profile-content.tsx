"use client"

import { useEffect, useState } from "react"
import { API_ENDPOINTS, apiCall } from "@/lib/api-config"
import { Loader2 } from "lucide-react"
import { SupplierHeader } from "./supplier-header"
import { SupplierInfo } from "./supplier-info"
import { FactoryGallery } from "./factory-gallery"
import { ExportHistory } from "./export-history"
import { ContactSupplierForm } from "./contact-supplier-form"

export interface SupplierProfile {
  id: string
  name: string
  logo: string
  industry: string
  country: string
  verified: boolean
  premium: boolean
  description: string
  establishedYear: number
  certificates: string[]
  factoryImages: string[]
  exportHistory: {
    year: number
    value: number
  }[]
  email: string
  phone: string
  website: string
  address: string
}

interface SupplierProfileContentProps {
  supplierId: string
}

export function SupplierProfileContent({ supplierId }: SupplierProfileContentProps) {
  const [supplier, setSupplier] = useState<SupplierProfile | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function fetchSupplier() {
      setLoading(true)
      try {
        const data = await apiCall<SupplierProfile>(API_ENDPOINTS.SUPPLIER(supplierId))
        setSupplier(data)
      } catch (error) {
        console.error("Failed to fetch supplier:", error)
        // Fallback to mock data
        setSupplier({
          id: supplierId,
          name: "Global Tech Manufacturing Co., Ltd",
          logo: "/generic-company-logo.png",
          industry: "Electronics",
          country: "China",
          verified: true,
          premium: true,
          description:
            "Leading manufacturer and exporter of electronic components with over 15 years of experience. We specialize in high-quality circuit boards, semiconductors, and consumer electronics. Our state-of-the-art facilities ensure consistent quality and timely delivery to clients worldwide.",
          establishedYear: 2008,
          certificates: ["ISO 9001:2015", "CE Certified", "RoHS Compliant", "UL Listed"],
          factoryImages: [
            "/factory-floor-electronics.jpg",
            "/production-line-assembly.jpg",
            "/quality-control-lab.jpg",
            "/warehouse-storage.jpg",
          ],
          exportHistory: [
            { year: 2020, value: 12500000 },
            { year: 2021, value: 15800000 },
            { year: 2022, value: 18200000 },
            { year: 2023, value: 21500000 },
            { year: 2024, value: 24800000 },
          ],
          email: "contact@globaltechmanufacturing.com",
          phone: "+86 755 1234 5678",
          website: "www.globaltechmanufacturing.com",
          address: "123 Industrial Park, Shenzhen, Guangdong, China",
        })
      } finally {
        setLoading(false)
      }
    }

    fetchSupplier()
  }, [supplierId])

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-12">
        <div className="flex items-center justify-center py-12">
          <Loader2 className="h-8 w-8 animate-spin text-primary" />
        </div>
      </div>
    )
  }

  if (!supplier) {
    return (
      <div className="container mx-auto px-4 py-12">
        <div className="text-center py-12">
          <h2 className="text-2xl font-bold mb-2">Supplier Not Found</h2>
          <p className="text-muted-foreground">The supplier you are looking for does not exist.</p>
        </div>
      </div>
    )
  }

  return (
    <div className="py-8">
      <div className="container mx-auto px-4">
        <SupplierHeader supplier={supplier} />

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
          <div className="lg:col-span-2 space-y-8">
            <SupplierInfo supplier={supplier} />
            <FactoryGallery images={supplier.factoryImages} />
            <ExportHistory data={supplier.exportHistory} />
          </div>

          <div className="lg:col-span-1">
            <ContactSupplierForm supplierId={supplier.id} supplierName={supplier.name} />
          </div>
        </div>
      </div>
    </div>
  )
}
