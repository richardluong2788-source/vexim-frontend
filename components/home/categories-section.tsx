import { Card, CardContent } from "@/components/ui/card"
import Link from "next/link"
import { Package, Shirt, Cpu, Wrench, Leaf, Home, Car, Pill } from "lucide-react"

const categories = [
  { name: "Electronics", icon: Cpu, count: "2,450+" },
  { name: "Textiles & Apparel", icon: Shirt, count: "1,890+" },
  { name: "Machinery", icon: Wrench, count: "1,230+" },
  { name: "Food & Beverage", icon: Leaf, count: "980+" },
  { name: "Home & Garden", icon: Home, count: "1,560+" },
  { name: "Automotive", icon: Car, count: "890+" },
  { name: "Health & Beauty", icon: Pill, count: "1,120+" },
  { name: "General Products", icon: Package, count: "3,200+" },
]

export function CategoriesSection() {
  return (
    <section className="py-16 md:py-24 bg-background">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">Browse by Category</h2>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            Explore suppliers across diverse industries and product categories
          </p>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          {categories.map((category, index) => (
            <Link key={index} href={`/suppliers?category=${encodeURIComponent(category.name)}`}>
              <Card className="hover:border-primary hover:shadow-md transition-all cursor-pointer h-full">
                <CardContent className="pt-6">
                  <div className="flex flex-col items-center text-center">
                    <div className="mb-3 p-3 bg-primary/10 rounded-lg">
                      <category.icon className="h-8 w-8 text-primary" />
                    </div>
                    <h3 className="font-bold mb-1">{category.name}</h3>
                    <p className="text-sm text-muted-foreground">{category.count} suppliers</p>
                  </div>
                </CardContent>
              </Card>
            </Link>
          ))}
        </div>
      </div>
    </section>
  )
}
