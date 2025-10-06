import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { SupplierListingContent } from "@/components/suppliers/supplier-listing-content"

export default function SuppliersPage() {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <SupplierListingContent />
      </main>
      <Footer />
    </div>
  )
}
