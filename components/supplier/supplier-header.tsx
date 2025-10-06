import { Badge } from "@/components/ui/badge"
import { CheckCircle2, MapPin, Calendar, Globe, Phone } from "lucide-react"
import type { SupplierProfile } from "./supplier-profile-content"

interface SupplierHeaderProps {
  supplier: SupplierProfile
}

export function SupplierHeader({ supplier }: SupplierHeaderProps) {
  return (
    <div className="bg-card border rounded-lg p-6">
      <div className="flex flex-col md:flex-row gap-6">
        <div className="relative flex-shrink-0">
          <img
            src={supplier.logo || "/placeholder.svg"}
            alt={supplier.name}
            className="h-32 w-32 object-contain rounded-lg border"
            crossOrigin="anonymous"
          />
          {supplier.verified && (
            <CheckCircle2 className="absolute -top-2 -right-2 h-8 w-8 text-green-600 bg-background rounded-full p-1" />
          )}
        </div>

        <div className="flex-1">
          <div className="flex flex-wrap items-start justify-between gap-4 mb-3">
            <div>
              <h1 className="text-3xl font-bold mb-2">{supplier.name}</h1>
              <div className="flex flex-wrap gap-2">
                <Badge variant="secondary">{supplier.industry}</Badge>
                {supplier.verified && <Badge className="bg-green-600 text-white">Verified</Badge>}
                {supplier.premium && <Badge className="bg-secondary text-secondary-foreground">Premium</Badge>}
              </div>
            </div>
          </div>

          <p className="text-muted-foreground mb-4 leading-relaxed">{supplier.description}</p>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div className="flex items-center gap-2">
              <MapPin className="h-4 w-4 text-muted-foreground" />
              <span>{supplier.country}</span>
            </div>
            <div className="flex items-center gap-2">
              <Calendar className="h-4 w-4 text-muted-foreground" />
              <span>Established {supplier.establishedYear}</span>
            </div>
            <div className="flex items-center gap-2">
              <Globe className="h-4 w-4 text-muted-foreground" />
              <a
                href={`https://${supplier.website}`}
                className="text-primary hover:underline"
                target="_blank"
                rel="noopener noreferrer"
              >
                {supplier.website}
              </a>
            </div>
            <div className="flex items-center gap-2">
              <Phone className="h-4 w-4 text-muted-foreground" />
              <span>{supplier.phone}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
