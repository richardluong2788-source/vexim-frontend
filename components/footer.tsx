import Link from "next/link"
import { Facebook, Twitter, Linkedin, Mail } from "lucide-react"

export function Footer() {
  return (
    <footer className="bg-primary text-primary-foreground">
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* About Column */}
          <div>
            <h3 className="text-lg font-bold mb-4">About Vexim</h3>
            <ul className="space-y-2">
              <li>
                <Link href="/about" className="text-sm hover:text-secondary transition-colors">
                  Our Story
                </Link>
              </li>
              <li>
                <Link href="/how-it-works" className="text-sm hover:text-secondary transition-colors">
                  How It Works
                </Link>
              </li>
              <li>
                <Link href="/verification" className="text-sm hover:text-secondary transition-colors">
                  Verification Process
                </Link>
              </li>
              <li>
                <Link href="/careers" className="text-sm hover:text-secondary transition-colors">
                  Careers
                </Link>
              </li>
            </ul>
          </div>

          {/* For Suppliers Column */}
          <div>
            <h3 className="text-lg font-bold mb-4">For Suppliers</h3>
            <ul className="space-y-2">
              <li>
                <Link href="/register" className="text-sm hover:text-secondary transition-colors">
                  Register Your Company
                </Link>
              </li>
              <li>
                <Link href="/pricing" className="text-sm hover:text-secondary transition-colors">
                  Pricing Plans
                </Link>
              </li>
              <li>
                <Link href="/supplier-dashboard" className="text-sm hover:text-secondary transition-colors">
                  Supplier Dashboard
                </Link>
              </li>
              <li>
                <Link href="/resources" className="text-sm hover:text-secondary transition-colors">
                  Resources
                </Link>
              </li>
            </ul>
          </div>

          {/* For Buyers Column */}
          <div>
            <h3 className="text-lg font-bold mb-4">For Buyers</h3>
            <ul className="space-y-2">
              <li>
                <Link href="/suppliers" className="text-sm hover:text-secondary transition-colors">
                  Find Suppliers
                </Link>
              </li>
              <li>
                <Link href="/categories" className="text-sm hover:text-secondary transition-colors">
                  Browse Categories
                </Link>
              </li>
              <li>
                <Link href="/buyer-dashboard" className="text-sm hover:text-secondary transition-colors">
                  Buyer Dashboard
                </Link>
              </li>
              <li>
                <Link href="/buyer-guide" className="text-sm hover:text-secondary transition-colors">
                  Buyer Guide
                </Link>
              </li>
            </ul>
          </div>

          {/* Contact Column */}
          <div>
            <h3 className="text-lg font-bold mb-4">Contact</h3>
            <ul className="space-y-2 mb-4">
              <li>
                <Link href="/contact" className="text-sm hover:text-secondary transition-colors">
                  Contact Us
                </Link>
              </li>
              <li>
                <Link href="/support" className="text-sm hover:text-secondary transition-colors">
                  Support Center
                </Link>
              </li>
              <li>
                <Link href="/privacy" className="text-sm hover:text-secondary transition-colors">
                  Privacy Policy
                </Link>
              </li>
              <li>
                <Link href="/terms" className="text-sm hover:text-secondary transition-colors">
                  Terms of Service
                </Link>
              </li>
            </ul>

            {/* Social Icons */}
            <div className="flex space-x-4">
              <a href="#" className="hover:text-secondary transition-colors" aria-label="Facebook">
                <Facebook className="h-5 w-5" />
              </a>
              <a href="#" className="hover:text-secondary transition-colors" aria-label="Twitter">
                <Twitter className="h-5 w-5" />
              </a>
              <a href="#" className="hover:text-secondary transition-colors" aria-label="LinkedIn">
                <Linkedin className="h-5 w-5" />
              </a>
              <a href="#" className="hover:text-secondary transition-colors" aria-label="Email">
                <Mail className="h-5 w-5" />
              </a>
            </div>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="mt-12 pt-8 border-t border-primary-foreground/20 text-center text-sm">
          <p>&copy; {new Date().getFullYear()} Vexim. All rights reserved.</p>
        </div>
      </div>
    </footer>
  )
}
