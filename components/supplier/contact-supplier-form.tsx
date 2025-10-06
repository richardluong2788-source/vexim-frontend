"use client"

import type React from "react"

import { useState } from "react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"
import { Label } from "@/components/ui/label"
import { API_ENDPOINTS, apiCall } from "@/lib/api-config"
import { Loader2 } from "lucide-react"
import { useToast } from "@/hooks/use-toast"

interface ContactSupplierFormProps {
  supplierId: string
  supplierName: string
}

export function ContactSupplierForm({ supplierId, supplierName }: ContactSupplierFormProps) {
  const [loading, setLoading] = useState(false)
  const { toast } = useToast()
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    company: "",
    message: "",
  })

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)

    try {
      await apiCall(API_ENDPOINTS.CONTACT_REQUEST, {
        method: "POST",
        body: JSON.stringify({
          supplierId,
          ...formData,
        }),
      })

      toast({
        title: "Message Sent",
        description: "Your inquiry has been sent to the supplier. They will contact you soon.",
      })

      setFormData({
        name: "",
        email: "",
        company: "",
        message: "",
      })
    } catch (error) {
      console.error("Failed to send message:", error)
      toast({
        title: "Error",
        description: "Failed to send message. Please try again later.",
        variant: "destructive",
      })
    } finally {
      setLoading(false)
    }
  }

  return (
    <Card className="sticky top-20">
      <CardHeader>
        <CardTitle>Contact Supplier</CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="name">Your Name *</Label>
            <Input
              id="name"
              required
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              placeholder="John Doe"
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="email">Email *</Label>
            <Input
              id="email"
              type="email"
              required
              value={formData.email}
              onChange={(e) => setFormData({ ...formData, email: e.target.value })}
              placeholder="john@company.com"
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="company">Company</Label>
            <Input
              id="company"
              value={formData.company}
              onChange={(e) => setFormData({ ...formData, company: e.target.value })}
              placeholder="Your Company Name"
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="message">Message *</Label>
            <Textarea
              id="message"
              required
              value={formData.message}
              onChange={(e) => setFormData({ ...formData, message: e.target.value })}
              placeholder="I'm interested in your products..."
              rows={5}
            />
          </div>

          <Button type="submit" className="w-full" disabled={loading}>
            {loading ? (
              <>
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                Sending...
              </>
            ) : (
              "Send Inquiry"
            )}
          </Button>

          <p className="text-xs text-muted-foreground">
            Your contact information will be shared with {supplierName} for business purposes only.
          </p>
        </form>
      </CardContent>
    </Card>
  )
}
