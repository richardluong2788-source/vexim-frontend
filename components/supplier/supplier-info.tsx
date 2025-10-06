import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Award, MapPin } from "lucide-react"
import type { SupplierProfile } from "./supplier-profile-content"

interface SupplierInfoProps {
  supplier: SupplierProfile
}

export function SupplierInfo({ supplier }: SupplierInfoProps) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Company Information</CardTitle>
      </CardHeader>
      <CardContent className="space-y-6">
        <div>
          <h3 className="font-semibold mb-2 flex items-center gap-2">
            <Award className="h-5 w-5 text-primary" />
            Certifications
          </h3>
          <div className="flex flex-wrap gap-2">
            {supplier.certificates.map((cert, index) => (
              <Badge key={index} variant="outline">
                {cert}
              </Badge>
            ))}
          </div>
        </div>

        <div>
          <h3 className="font-semibold mb-2 flex items-center gap-2">
            <MapPin className="h-5 w-5 text-primary" />
            Address
          </h3>
          <p className="text-muted-foreground">{supplier.address}</p>
        </div>
      </CardContent>
    </Card>
  )
}
