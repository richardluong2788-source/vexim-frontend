import { Card, CardContent, CardFooter } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { CheckCircle2, MapPin, Calendar } from "lucide-react"
import Link from "next/link"
import type { Supplier } from "./supplier-listing-content"

interface SupplierCardProps {
  supplier: Supplier
}

export function SupplierCard({ supplier }: SupplierCardProps) {
  return (
    <Card className="hover:shadow-lg transition-shadow h-full flex flex-col">
      <CardContent className="pt-6 flex-1">
        <div className="flex items-start gap-4 mb-4">
          <div className="relative flex-shrink-0">
            <img
              src={supplier.logo || "/placeholder.svg"}
              alt={supplier.name}
              className="h-16 w-16 object-contain rounded-lg border"
              crossOrigin="anonymous"
            />
            {supplier.verified && (
              <CheckCircle2 className="absolute -top-1 -right-1 h-5 w-5 text-green-600 bg-background rounded-full" />
            )}
          </div>
          <div className="flex-1 min-w-0">
            <h3 className="font-bold text-lg mb-1 truncate">{supplier.name}</h3>
            <div className="flex flex-wrap gap-1 mb-2">
              <Badge variant="secondary" className="text-xs">
                {supplier.industry}
              </Badge>
              {supplier.premium && <Badge className="bg-secondary text-secondary-foreground text-xs">Premium</Badge>}
            </div>
          </div>
        </div>

        <p className="text-sm text-muted-foreground mb-4 line-clamp-2">{supplier.description}</p>

        <div className="space-y-2 text-sm">
          <div className="flex items-center gap-2 text-muted-foreground">
            <MapPin className="h-4 w-4" />
            <span>{supplier.country}</span>
          </div>
          <div className="flex items-center gap-2 text-muted-foreground">
            <Calendar className="h-4 w-4" />
            <span>Est. {supplier.establishedYear}</span>
          </div>
        </div>
      </CardContent>

      <CardFooter className="pt-0">
        <Button variant="outline" className="w-full bg-transparent" asChild>
          <Link href={`/supplier/${supplier.id}`}>View Profile</Link>
        </Button>
      </CardFooter>
    </Card>
  )
}
