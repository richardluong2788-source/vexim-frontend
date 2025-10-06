"use client"

import { useState, useEffect } from "react"
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Check, X, Star, Zap, Crown } from "lucide-react"
import { API_ENDPOINTS } from "@/lib/api-config"
import { useToast } from "@/hooks/use-toast"

interface PackageFeature {
  name: string
  included: boolean
}

interface PricingPackage {
  id: number
  name: string
  price: number
  duration: string
  features: PackageFeature[]
  recommended?: boolean
  icon?: "basic" | "premium" | "enterprise"
}

export default function PricingPage() {
  const [packages, setPackages] = useState<PricingPackage[]>([])
  const [loading, setLoading] = useState(true)
  const { toast } = useToast()

  useEffect(() => {
    fetchPackages()
  }, [])

  const fetchPackages = async () => {
    try {
      const response = await fetch(API_ENDPOINTS.PACKAGES)
      if (response.ok) {
        const data = await response.json()
        setPackages(data)
      } else {
        throw new Error("Failed to fetch packages")
      }
    } catch (error) {
      console.error("Error fetching packages:", error)
      // Use mock data as fallback
      setPackages([
        {
          id: 1,
          name: "Basic",
          price: 99,
          duration: "month",
          icon: "basic",
          features: [
            { name: "Company Profile Listing", included: true },
            { name: "Up to 10 Product Listings", included: true },
            { name: "Basic Analytics", included: true },
            { name: "Email Support", included: true },
            { name: "Verification Badge", included: false },
            { name: "Priority Placement", included: false },
            { name: "Featured Supplier Status", included: false },
            { name: "Dedicated Account Manager", included: false },
          ],
        },
        {
          id: 2,
          name: "Premium",
          price: 299,
          duration: "month",
          icon: "premium",
          recommended: true,
          features: [
            { name: "Company Profile Listing", included: true },
            { name: "Unlimited Product Listings", included: true },
            { name: "Advanced Analytics & Insights", included: true },
            { name: "Priority Email & Phone Support", included: true },
            { name: "Verification Badge", included: true },
            { name: "Priority Placement in Search", included: true },
            { name: "Featured Supplier Status", included: true },
            { name: "Dedicated Account Manager", included: false },
          ],
        },
        {
          id: 3,
          name: "Enterprise",
          price: 799,
          duration: "month",
          icon: "enterprise",
          features: [
            { name: "Company Profile Listing", included: true },
            { name: "Unlimited Product Listings", included: true },
            { name: "Advanced Analytics & Insights", included: true },
            { name: "24/7 Priority Support", included: true },
            { name: "Verification Badge", included: true },
            { name: "Top Priority Placement", included: true },
            { name: "Featured Supplier Status", included: true },
            { name: "Dedicated Account Manager", included: true },
          ],
        },
      ])
    } finally {
      setLoading(false)
    }
  }

  const handleSubscribe = async (packageId: number, packageName: string) => {
    try {
      const response = await fetch(API_ENDPOINTS.SUBSCRIBE, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ package_id: packageId }),
      })

      if (response.ok) {
        toast({
          title: "Success!",
          description: `You've subscribed to the ${packageName} package.`,
        })
      } else {
        throw new Error("Subscription failed")
      }
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to process subscription. Please try again.",
        variant: "destructive",
      })
    }
  }

  const getPackageIcon = (icon?: string) => {
    switch (icon) {
      case "basic":
        return <Star className="h-8 w-8 text-primary" />
      case "premium":
        return <Zap className="h-8 w-8 text-accent" />
      case "enterprise":
        return <Crown className="h-8 w-8 text-accent" />
      default:
        return <Star className="h-8 w-8 text-primary" />
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
          <p className="text-muted-foreground">Loading pricing packages...</p>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-background">
      {/* Hero Section */}
      <section className="bg-primary text-primary-foreground py-16">
        <div className="container mx-auto px-4 text-center">
          <h1 className="text-4xl md:text-5xl font-bold mb-4">Choose Your Plan</h1>
          <p className="text-xl text-primary-foreground/90 max-w-2xl mx-auto">
            Select the perfect package to showcase your business and connect with global buyers
          </p>
        </div>
      </section>

      {/* Pricing Cards */}
      <section className="py-16">
        <div className="container mx-auto px-4">
          <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3 max-w-7xl mx-auto">
            {packages.map((pkg) => (
              <Card
                key={pkg.id}
                className={`relative flex flex-col ${
                  pkg.recommended ? "border-accent border-2 shadow-xl scale-105" : ""
                }`}
              >
                {pkg.recommended && (
                  <div className="absolute -top-4 left-1/2 -translate-x-1/2">
                    <Badge className="bg-accent text-accent-foreground px-4 py-1 text-sm font-semibold">
                      Most Popular
                    </Badge>
                  </div>
                )}

                <CardHeader className="text-center pb-8 pt-8">
                  <div className="flex justify-center mb-4">{getPackageIcon(pkg.icon)}</div>
                  <CardTitle className="text-2xl font-bold mb-2">{pkg.name}</CardTitle>
                  <div className="mt-4">
                    <span className="text-4xl font-bold">${pkg.price}</span>
                    <span className="text-muted-foreground">/{pkg.duration}</span>
                  </div>
                </CardHeader>

                <CardContent className="flex-1">
                  <ul className="space-y-3">
                    {pkg.features.map((feature, index) => (
                      <li key={index} className="flex items-start gap-3">
                        {feature.included ? (
                          <Check className="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                        ) : (
                          <X className="h-5 w-5 text-muted-foreground flex-shrink-0 mt-0.5" />
                        )}
                        <span className={`text-sm ${feature.included ? "text-foreground" : "text-muted-foreground"}`}>
                          {feature.name}
                        </span>
                      </li>
                    ))}
                  </ul>
                </CardContent>

                <CardFooter className="pt-6">
                  <Button
                    className="w-full"
                    variant={pkg.recommended ? "default" : "outline"}
                    size="lg"
                    onClick={() => handleSubscribe(pkg.id, pkg.name)}
                  >
                    Subscribe Now
                  </Button>
                </CardFooter>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* FAQ Section */}
      <section className="py-16 bg-muted/30">
        <div className="container mx-auto px-4">
          <div className="max-w-3xl mx-auto">
            <h2 className="text-3xl font-bold text-center mb-12">Frequently Asked Questions</h2>
            <div className="space-y-6">
              <Card>
                <CardHeader>
                  <CardTitle className="text-lg">Can I upgrade or downgrade my plan?</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-muted-foreground">
                    Yes, you can upgrade or downgrade your plan at any time. Changes will be reflected in your next
                    billing cycle.
                  </p>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle className="text-lg">What payment methods do you accept?</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-muted-foreground">
                    We accept all major credit cards, PayPal, and bank transfers for Enterprise plans.
                  </p>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle className="text-lg">Is there a free trial available?</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-muted-foreground">
                    Yes, we offer a 14-day free trial for the Premium package. No credit card required.
                  </p>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle className="text-lg">Can I cancel my subscription?</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-muted-foreground">
                    Yes, you can cancel your subscription at any time. Your access will continue until the end of your
                    current billing period.
                  </p>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-16 bg-primary text-primary-foreground">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-3xl font-bold mb-4">Still have questions?</h2>
          <p className="text-xl text-primary-foreground/90 mb-8 max-w-2xl mx-auto">
            Our team is here to help you choose the right plan for your business
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Button size="lg" variant="secondary" asChild>
              <a href="mailto:sales@vexim.com">Contact Sales</a>
            </Button>
            <Button
              size="lg"
              variant="outline"
              className="bg-transparent border-primary-foreground text-primary-foreground hover:bg-primary-foreground hover:text-primary"
              asChild
            >
              <a href="tel:+1234567890">Call Us</a>
            </Button>
          </div>
        </div>
      </section>
    </div>
  )
}
