import { Mail, Phone, MapPin, Clock } from "lucide-react"
import { Card, CardContent } from "@/components/ui/card"

export function ContactInfo() {
  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-3xl font-bold mb-6 text-foreground">Contact Information</h2>
        <p className="text-muted-foreground leading-relaxed">
          Reach out to us through any of the following channels. We're committed to providing you with the best support
          for your B2B sourcing needs.
        </p>
      </div>

      <div className="space-y-4">
        {/* Email */}
        <Card className="border-border">
          <CardContent className="p-6">
            <div className="flex items-start gap-4">
              <div className="bg-secondary/10 p-3 rounded-lg">
                <Mail className="h-6 w-6 text-secondary-foreground" />
              </div>
              <div>
                <h3 className="font-bold text-foreground mb-1">Email</h3>
                <a
                  href="mailto:support@vexim.com"
                  className="text-muted-foreground hover:text-primary transition-colors"
                >
                  support@vexim.com
                </a>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Phone */}
        <Card className="border-border">
          <CardContent className="p-6">
            <div className="flex items-start gap-4">
              <div className="bg-secondary/10 p-3 rounded-lg">
                <Phone className="h-6 w-6 text-secondary-foreground" />
              </div>
              <div>
                <h3 className="font-bold text-foreground mb-1">Phone</h3>
                <a href="tel:+1-555-123-4567" className="text-muted-foreground hover:text-primary transition-colors">
                  +1 (555) 123-4567
                </a>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Address */}
        <Card className="border-border">
          <CardContent className="p-6">
            <div className="flex items-start gap-4">
              <div className="bg-secondary/10 p-3 rounded-lg">
                <MapPin className="h-6 w-6 text-secondary-foreground" />
              </div>
              <div>
                <h3 className="font-bold text-foreground mb-1">Office</h3>
                <p className="text-muted-foreground">
                  123 Business Avenue
                  <br />
                  New York, NY 10001
                  <br />
                  United States
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Business Hours */}
        <Card className="border-border">
          <CardContent className="p-6">
            <div className="flex items-start gap-4">
              <div className="bg-secondary/10 p-3 rounded-lg">
                <Clock className="h-6 w-6 text-secondary-foreground" />
              </div>
              <div>
                <h3 className="font-bold text-foreground mb-1">Business Hours</h3>
                <p className="text-muted-foreground">
                  Monday - Friday: 9:00 AM - 6:00 PM EST
                  <br />
                  Saturday - Sunday: Closed
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Additional Info */}
      <div className="bg-muted/50 p-6 rounded-lg border border-border">
        <h3 className="font-bold text-foreground mb-2">Need Immediate Assistance?</h3>
        <p className="text-sm text-muted-foreground leading-relaxed">
          For urgent inquiries, please call our support line. For general questions, email is the fastest way to reach
          our team.
        </p>
      </div>
    </div>
  )
}
