import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { SupplierDashboardContent } from "@/components/dashboard/supplier-dashboard-content"

export default function SupplierDashboardPage() {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        <SupplierDashboardContent />
      </main>
      <Footer />
    </div>
  )
}
