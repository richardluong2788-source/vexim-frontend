import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { SupplierProfileContent } from "@/components/supplier/supplier-profile-content"

export default function SupplierProfilePage({ params }: { params: { id: string } }) {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <SupplierProfileContent supplierId={params.id} />
      </main>
      <Footer />
    </div>
  )
}
