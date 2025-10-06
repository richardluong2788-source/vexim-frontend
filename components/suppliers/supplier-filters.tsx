"use client"

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Label } from "@/components/ui/label"
import { Checkbox } from "@/components/ui/checkbox"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Button } from "@/components/ui/button"

interface FilterProps {
  filters: {
    industry: string
    country: string
    verified: boolean
    premium: boolean
    search: string
  }
  onFilterChange: (filters: FilterProps["filters"]) => void
}

const industries = [
  "All Industries",
  "Electronics",
  "Textiles & Apparel",
  "Machinery",
  "Food & Beverage",
  "Home & Garden",
  "Automotive",
  "Health & Beauty",
  "Chemicals",
  "Construction",
  "Agriculture",
]

const countries = [
  "All Countries",
  "China",
  "India",
  "USA",
  "Germany",
  "Vietnam",
  "Thailand",
  "Turkey",
  "South Korea",
  "Japan",
  "United Kingdom",
  "Italy",
  "Spain",
]

export function SupplierFilters({ filters, onFilterChange }: FilterProps) {
  const handleIndustryChange = (value: string) => {
    onFilterChange({
      ...filters,
      industry: value === "All Industries" ? "" : value,
    })
  }

  const handleCountryChange = (value: string) => {
    onFilterChange({
      ...filters,
      country: value === "All Countries" ? "" : value,
    })
  }

  const handleVerifiedChange = (checked: boolean) => {
    onFilterChange({
      ...filters,
      verified: checked,
    })
  }

  const handlePremiumChange = (checked: boolean) => {
    onFilterChange({
      ...filters,
      premium: checked,
    })
  }

  const handleReset = () => {
    onFilterChange({
      industry: "",
      country: "",
      verified: false,
      premium: false,
      search: filters.search, // Keep search query
    })
  }

  return (
    <Card className="sticky top-20">
      <CardHeader>
        <CardTitle>Filters</CardTitle>
      </CardHeader>
      <CardContent className="space-y-6">
        {/* Industry Filter */}
        <div className="space-y-2">
          <Label>Industry</Label>
          <Select value={filters.industry || "All Industries"} onValueChange={handleIndustryChange}>
            <SelectTrigger>
              <SelectValue placeholder="Select industry" />
            </SelectTrigger>
            <SelectContent>
              {industries.map((industry) => (
                <SelectItem key={industry} value={industry}>
                  {industry}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        {/* Country Filter */}
        <div className="space-y-2">
          <Label>Country</Label>
          <Select value={filters.country || "All Countries"} onValueChange={handleCountryChange}>
            <SelectTrigger>
              <SelectValue placeholder="Select country" />
            </SelectTrigger>
            <SelectContent>
              {countries.map((country) => (
                <SelectItem key={country} value={country}>
                  {country}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        {/* Verification Filters */}
        <div className="space-y-3">
          <Label>Verification Status</Label>
          <div className="flex items-center space-x-2">
            <Checkbox id="verified" checked={filters.verified} onCheckedChange={handleVerifiedChange} />
            <label
              htmlFor="verified"
              className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer"
            >
              Verified Only
            </label>
          </div>
          <div className="flex items-center space-x-2">
            <Checkbox id="premium" checked={filters.premium} onCheckedChange={handlePremiumChange} />
            <label
              htmlFor="premium"
              className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer"
            >
              Premium Members
            </label>
          </div>
        </div>

        {/* Reset Button */}
        <Button variant="outline" className="w-full bg-transparent" onClick={handleReset}>
          Reset Filters
        </Button>
      </CardContent>
    </Card>
  )
}
