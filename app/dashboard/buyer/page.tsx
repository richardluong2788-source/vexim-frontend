"use client"

import { useState, useEffect } from "react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { API_ENDPOINTS } from "@/lib/api-config"
import { Mail, Phone, Calendar, Building2, MapPin, Star, TrendingUp } from "lucide-react"
import Link from "next/link"

interface ContactHistory {
  id: number
  supplier_name: string
  supplier_id: number
  message: string
  sent_at: string
  status: "pending" | "replied" | "closed"
}

interface RecommendedSupplier {
  id: number
  name: string
  industry: string
  country: string
  verified: boolean
  premium: boolean
  rating: number
  match_score: number
}

export default function BuyerDashboardPage() {
  const [contactHistory, setContactHistory] = useState<ContactHistory[]>([])
  const [recommendations, setRecommendations] = useState<RecommendedSupplier[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchDashboardData()
  }, [])

  const fetchDashboardData = async () => {
    try {
      // Fetch contact history
      const historyResponse = await fetch(API_ENDPOINTS.BUYER_CONTACT_HISTORY)
      if (historyResponse.ok) {
        const data = await historyResponse.json()
        setContactHistory(data)
      }

      // Fetch recommendations
      const recsResponse = await fetch(API_ENDPOINTS.BUYER_RECOMMENDATIONS)
      if (recsResponse.ok) {
        const data = await recsResponse.json()
        setRecommendations(data)
      }
    } catch (error) {
      console.error("Error fetching dashboard data:", error)
      // Use mock data as fallback
      setContactHistory([
        {
          id: 1,
          supplier_name: "Global Electronics Co.",
          supplier_id: 101,
          message: "Inquiry about bulk order of LED displays",
          sent_at: "2025-01-15T10:30:00Z",
          status: "replied",
        },
        {
          id: 2,
          supplier_name: "Premium Textiles Ltd.",
          supplier_id: 102,
          message: "Request for fabric samples and pricing",
          sent_at: "2025-01-14T14:20:00Z",
          status: "pending",
        },
        {
          id: 3,
          supplier_name: "Industrial Machinery Inc.",
          supplier_id: 103,
          message: "Quote request for manufacturing equipment",
          sent_at: "2025-01-12T09:15:00Z",
          status: "closed",
        },
      ])

      setRecommendations([
        {
          id: 201,
          name: "Tech Components Asia",
          industry: "Electronics",
          country: "China",
          verified: true,
          premium: true,
          rating: 4.8,
          match_score: 95,
        },
        {
          id: 202,
          name: "Quality Fabrics Export",
          industry: "Textiles",
          country: "India",
          verified: true,
          premium: false,
          rating: 4.6,
          match_score: 88,
        },
        {
          id: 203,
          name: "Precision Tools Manufacturing",
          industry: "Machinery",
          country: "Germany",
          verified: true,
          premium: true,
          rating: 4.9,
          match_score: 92,
        },
      ])
    } finally {
      setLoading(false)
    }
  }

  const getStatusColor = (status: string) => {
    switch (status) {
      case "replied":
        return "bg-green-100 text-green-800"
      case "pending":
        return "bg-yellow-100 text-yellow-800"
      case "closed":
        return "bg-gray-100 text-gray-800"
      default:
        return "bg-gray-100 text-gray-800"
    }
  }

  const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    return date.toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    })
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
          <p className="text-muted-foreground">Loading dashboard...</p>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-background">
      <div className="container mx-auto px-4 py-8">
        <div className="mb-8">
          <h1 className="text-4xl font-bold text-foreground mb-2">Buyer Dashboard</h1>
          <p className="text-muted-foreground">Manage your inquiries and discover recommended suppliers</p>
        </div>

        <Tabs defaultValue="contacts" className="space-y-6">
          <TabsList className="grid w-full max-w-md grid-cols-2">
            <TabsTrigger value="contacts">Contact History</TabsTrigger>
            <TabsTrigger value="recommendations">Recommendations</TabsTrigger>
          </TabsList>

          <TabsContent value="contacts" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Your Contact History</CardTitle>
                <CardDescription>View all inquiries you've sent to suppliers</CardDescription>
              </CardHeader>
              <CardContent>
                {contactHistory.length === 0 ? (
                  <div className="text-center py-12">
                    <Mail className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                    <p className="text-muted-foreground mb-4">No contact history yet</p>
                    <Button asChild>
                      <Link href="/suppliers">Browse Suppliers</Link>
                    </Button>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {contactHistory.map((contact) => (
                      <div key={contact.id} className="border rounded-lg p-4 hover:border-primary transition-colors">
                        <div className="flex items-start justify-between mb-2">
                          <div className="flex-1">
                            <Link
                              href={`/supplier/${contact.supplier_id}`}
                              className="text-lg font-semibold text-foreground hover:text-primary transition-colors"
                            >
                              {contact.supplier_name}
                            </Link>
                            <div className="flex items-center gap-2 mt-1">
                              <Calendar className="h-4 w-4 text-muted-foreground" />
                              <span className="text-sm text-muted-foreground">{formatDate(contact.sent_at)}</span>
                            </div>
                          </div>
                          <Badge className={getStatusColor(contact.status)}>{contact.status}</Badge>
                        </div>
                        <p className="text-muted-foreground text-sm mt-2">{contact.message}</p>
                      </div>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="recommendations" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Recommended Suppliers</CardTitle>
                <CardDescription>Suppliers matched to your interests and search history</CardDescription>
              </CardHeader>
              <CardContent>
                {recommendations.length === 0 ? (
                  <div className="text-center py-12">
                    <TrendingUp className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                    <p className="text-muted-foreground mb-4">No recommendations yet</p>
                    <p className="text-sm text-muted-foreground mb-4">
                      Browse suppliers to get personalized recommendations
                    </p>
                    <Button asChild>
                      <Link href="/suppliers">Browse Suppliers</Link>
                    </Button>
                  </div>
                ) : (
                  <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {recommendations.map((supplier) => (
                      <Card key={supplier.id} className="hover:shadow-lg transition-shadow">
                        <CardHeader>
                          <div className="flex items-start justify-between mb-2">
                            <CardTitle className="text-lg">{supplier.name}</CardTitle>
                            <div className="flex items-center gap-1 text-sm font-semibold text-primary">
                              <TrendingUp className="h-4 w-4" />
                              {supplier.match_score}%
                            </div>
                          </div>
                          <div className="flex items-center gap-2">
                            {supplier.verified && (
                              <Badge variant="secondary" className="text-xs">
                                Verified
                              </Badge>
                            )}
                            {supplier.premium && (
                              <Badge className="text-xs bg-accent text-accent-foreground">Premium</Badge>
                            )}
                          </div>
                        </CardHeader>
                        <CardContent className="space-y-3">
                          <div className="flex items-center gap-2 text-sm">
                            <Building2 className="h-4 w-4 text-muted-foreground" />
                            <span className="text-muted-foreground">{supplier.industry}</span>
                          </div>
                          <div className="flex items-center gap-2 text-sm">
                            <MapPin className="h-4 w-4 text-muted-foreground" />
                            <span className="text-muted-foreground">{supplier.country}</span>
                          </div>
                          <div className="flex items-center gap-2 text-sm">
                            <Star className="h-4 w-4 text-accent fill-accent" />
                            <span className="font-semibold">{supplier.rating}</span>
                            <span className="text-muted-foreground">rating</span>
                          </div>
                          <Button asChild className="w-full mt-4">
                            <Link href={`/supplier/${supplier.id}`}>View Profile</Link>
                          </Button>
                        </CardContent>
                      </Card>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>

        <Card className="mt-6">
          <CardHeader>
            <CardTitle>Need Help?</CardTitle>
            <CardDescription>Our support team is here to assist you</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
              <div className="flex items-start gap-3 p-4 border rounded-lg">
                <Mail className="h-5 w-5 text-primary mt-1" />
                <div>
                  <h3 className="font-semibold mb-1">Email Support</h3>
                  <p className="text-sm text-muted-foreground mb-2">Get help via email within 24 hours</p>
                  <a href="mailto:support@vexim.com" className="text-sm text-primary hover:underline">
                    support@vexim.com
                  </a>
                </div>
              </div>
              <div className="flex items-start gap-3 p-4 border rounded-lg">
                <Phone className="h-5 w-5 text-primary mt-1" />
                <div>
                  <h3 className="font-semibold mb-1">Phone Support</h3>
                  <p className="text-sm text-muted-foreground mb-2">Speak with our team directly</p>
                  <a href="tel:+1234567890" className="text-sm text-primary hover:underline">
                    +1 (234) 567-890
                  </a>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
