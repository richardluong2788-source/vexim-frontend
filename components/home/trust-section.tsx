import { Shield, Factory, Globe, Lock } from "lucide-react"
import { Card, CardContent } from "@/components/ui/card"

const trustFeatures = [
  {
    icon: Shield,
    title: "Verified Profiles",
    description: "Every supplier undergoes rigorous verification to ensure authenticity and credibility.",
  },
  {
    icon: Factory,
    title: "Factory Transparency",
    description: "View real factory photos, certifications, and production capabilities before connecting.",
  },
  {
    icon: Globe,
    title: "Global Reach",
    description: "Access suppliers from over 100 countries with diverse product categories and expertise.",
  },
  {
    icon: Lock,
    title: "Secure Contact",
    description: "Protected communication channels and verified contact information for safe business dealings.",
  },
]

export function TrustSection() {
  return (
    <section className="py-16 md:py-24 bg-background">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">Why Trust Vexim</h2>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            We provide the tools and verification you need to make confident business decisions
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {trustFeatures.map((feature, index) => (
            <Card key={index} className="border-2 hover:border-primary transition-colors">
              <CardContent className="pt-6">
                <div className="flex flex-col items-center text-center">
                  <div className="mb-4 p-3 bg-primary/10 rounded-lg">
                    <feature.icon className="h-8 w-8 text-primary" />
                  </div>
                  <h3 className="text-xl font-bold mb-2">{feature.title}</h3>
                  <p className="text-muted-foreground leading-relaxed">{feature.description}</p>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </section>
  )
}
