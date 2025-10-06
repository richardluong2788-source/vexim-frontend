"use client"

import { useEffect, useState } from "react"
import { API_ENDPOINTS, apiCall } from "@/lib/api-config"
import { Loader2 } from "lucide-react"
import { DashboardStats } from "./dashboard-stats"
import { InquiriesTable } from "./inquiries-table"
import { ProfileEditor } from "./profile-editor"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"

export interface DashboardData {
  stats: {
    profileViews: number
    inquiries: number
    responseRate: number
    activeListings: number
  }
  inquiries: {
    id: string
    buyerName: string
    buyerEmail: string
    company: string
    message: string
    date: string
    status: "new" | "replied" | "archived"
  }[]
  profile: {
    name: string
    logo: string
    industry: string
    country: string
    description: string
    establishedYear: number
    certificates: string[]
    email: string
    phone: string
    website: string
    address: string
  }
}

export function SupplierDashboardContent() {
  const [data, setData] = useState<DashboardData | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function fetchDashboardData() {
      setLoading(true)
      try {
        const dashboardData = await apiCall<DashboardData>(API_ENDPOINTS.SUPPLIER_DASHBOARD)
        setData(dashboardData)
      } catch (error) {
        console.error("Failed to fetch dashboard data:", error)
        // Fallback to mock data
        setData({
          stats: {
            profileViews: 1247,
            inquiries: 38,
            responseRate: 92,
            activeListings: 5,
          },
          inquiries: [
            {
              id: "1",
              buyerName: "John Smith",
              buyerEmail: "john@techcorp.com",
              company: "Tech Corp International",
              message: "Interested in your electronic components. Can you provide a quote for 10,000 units?",
              date: "2024-01-15",
              status: "new",
            },
            {
              id: "2",
              buyerName: "Sarah Johnson",
              buyerEmail: "sarah@globalimports.com",
              company: "Global Imports Ltd",
              message: "Looking for long-term supplier partnership. Please contact me to discuss terms.",
              date: "2024-01-14",
              status: "new",
            },
            {
              id: "3",
              buyerName: "Michael Chen",
              buyerEmail: "m.chen@asiabusiness.com",
              company: "Asia Business Solutions",
              message: "Need samples of your products. What is your MOQ?",
              date: "2024-01-13",
              status: "replied",
            },
          ],
          profile: {
            name: "Global Tech Manufacturing Co., Ltd",
            logo: "/generic-company-logo.png",
            industry: "Electronics",
            country: "China",
            description: "Leading manufacturer and exporter of electronic components with over 15 years of experience.",
            establishedYear: 2008,
            certificates: ["ISO 9001:2015", "CE Certified", "RoHS Compliant"],
            email: "contact@globaltechmanufacturing.com",
            phone: "+86 755 1234 5678",
            website: "www.globaltechmanufacturing.com",
            address: "123 Industrial Park, Shenzhen, Guangdong, China",
          },
        })
      } finally {
        setLoading(false)
      }
    }

    fetchDashboardData()
  }, [])

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-12">
        <div className="flex items-center justify-center py-12">
          <Loader2 className="h-8 w-8 animate-spin text-primary" />
        </div>
      </div>
    )
  }

  if (!data) {
    return (
      <div className="container mx-auto px-4 py-12">
        <div className="text-center py-12">
          <h2 className="text-2xl font-bold mb-2">Failed to Load Dashboard</h2>
          <p className="text-muted-foreground">Please try again later.</p>
        </div>
      </div>
    )
  }

  return (
    <div className="py-8">
      <div className="container mx-auto px-4">
        <div className="mb-8">
          <h1 className="text-3xl md:text-4xl font-bold mb-2">Supplier Dashboard</h1>
          <p className="text-muted-foreground">Manage your profile, view inquiries, and track performance</p>
        </div>

        <DashboardStats stats={data.stats} />

        <Tabs defaultValue="inquiries" className="mt-8">
          <TabsList>
            <TabsTrigger value="inquiries">Inquiries</TabsTrigger>
            <TabsTrigger value="profile">Edit Profile</TabsTrigger>
          </TabsList>

          <TabsContent value="inquiries" className="mt-6">
            <InquiriesTable inquiries={data.inquiries} />
          </TabsContent>

          <TabsContent value="profile" className="mt-6">
            <ProfileEditor profile={data.profile} />
          </TabsContent>
        </Tabs>
      </div>
    </div>
  )
}
