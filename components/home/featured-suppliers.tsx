"use client"

import { useEffect, useState } from "react"
import { Card, CardContent, CardFooter } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { API_ENDPOINTS, apiCall } from "@/lib/api-config"
import { CheckCircle2 } from "lucide-react"
import Link from "next/link"

interface Supplier {
  id: string
  name: string
  logo: string
  industry: string
  country: string
  verified: boolean
  premium: boolean
}

export function FeaturedSuppliers() {
  const [suppliers, setSuppliers] = useState<Supplier[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function fetchSuppliers() {
      try {
        const data = await apiCall<Supplier[]>(API_ENDPOINTS.FEATURED_SUPPLIERS)
        setSuppliers(data)
      } catch (error) {
        console.error("Failed to fetch featured suppliers:", error)
        // Fallback to mock data for demo
        setSuppliers([
          {
            id: "1",
            name: "Global Tech Manufacturing",
            logo: "/generic-company-logo.png",
            industry: "Electronics",
            country: "China",
            verified: true,
            premium: true,
          },
          {
            id: "2",
            name: "Premium Textiles Ltd",
            logo: "/textile-company-logo.jpg",
            industry: "Textiles",
            country: "India",
            verified: true,
            premium: false,
          },
          {
            id: "3",
            name: "Industrial Parts Co",
            logo: "/industrial-logo.png",
            industry: "Machinery",
            country: "Germany",
            verified: true,
            premium: true,
          },
          {
            id: "4",
            name: "Organic Foods Export",
            logo: "/food-company-logo.png",
            industry: "Food & Beverage",
            country: "Vietnam",
            verified: true,
            premium: false,
          },
        ])
      } finally {
        setLoading(false)
      }
    }

    fetchSuppliers()
  }, [])

  if (loading) {
    return (
      <section className="py-16 md:py-24 bg-muted/30">
        <div className="container mx-auto px-4">
          <div className="text-center">
            <p className="text-muted-foreground">Loading featured suppliers...</p>
          </div>
        </div>
      </section>
    )
  }

  return (
    <section className="py-16 md:py-24 bg-muted/30">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">Featured Suppliers</h2>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            Discover verified suppliers trusted by businesses worldwide
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          {suppliers.map((supplier) => (
            <Card key={supplier.id} className="hover:shadow-lg transition-shadow">
              <CardContent className="pt-6">
                <div className="flex flex-col items-center text-center">
                  <div className="relative mb-4">
                    <img
                      src={supplier.logo || "/placeholder.svg"}
                      alt={supplier.name}
                      className="h-20 w-20 object-contain rounded-lg"
                      crossOrigin="anonymous"
                    />
                    {supplier.verified && (
                      <CheckCircle2 className="absolute -top-1 -right-1 h-6 w-6 text-green-600 bg-background rounded-full" />
                    )}
                  </div>
                  <h3 className="text-lg font-bold mb-2">{supplier.name}</h3>
                  <div className="flex flex-wrap gap-2 justify-center mb-3">
                    <Badge variant="secondary">{supplier.industry}</Badge>
                    <Badge variant="outline">{supplier.country}</Badge>
                    {supplier.premium && <Badge className="bg-secondary text-secondary-foreground">Premium</Badge>}
                  </div>
                </div>
              </CardContent>
              <CardFooter>
                <Button variant="outline" className="w-full bg-transparent" asChild>
                  <Link href={`/supplier/${supplier.id}`}>View Profile</Link>
                </Button>
              </CardFooter>
            </Card>
          ))}
        </div>

        <div className="text-center">
          <Button size="lg" asChild>
            <Link href="/suppliers">View All Suppliers</Link>
          </Button>
        </div>
      </div>
    </section>
  )
}
