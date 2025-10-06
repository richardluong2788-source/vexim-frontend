import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { HeroSection } from "@/components/home/hero-section"
import { TrustSection } from "@/components/home/trust-section"
import { FeaturedSuppliers } from "@/components/home/featured-suppliers"
import { CategoriesSection } from "@/components/home/categories-section"
import { HowItWorksSection } from "@/components/home/how-it-works-section"

export default function HomePage() {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <HeroSection />
        <TrustSection />
        <FeaturedSuppliers />
        <CategoriesSection />
        <HowItWorksSection />
      </main>
      <Footer />
    </div>
  )
}
