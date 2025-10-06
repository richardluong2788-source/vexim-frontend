"use client"

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Bar, BarChart, CartesianGrid, XAxis, YAxis, Tooltip, ResponsiveContainer } from "recharts"

interface ExportHistoryProps {
  data: {
    year: number
    value: number
  }[]
}

export function ExportHistory({ data }: ExportHistoryProps) {
  const formattedData = data.map((item) => ({
    year: item.year.toString(),
    value: item.value,
    displayValue: `$${(item.value / 1000000).toFixed(1)}M`,
  }))

  return (
    <Card>
      <CardHeader>
        <CardTitle>Export History</CardTitle>
      </CardHeader>
      <CardContent>
        <ResponsiveContainer width="100%" height={300}>
          <BarChart data={formattedData}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="year" />
            <YAxis tickFormatter={(value) => `$${(value / 1000000).toFixed(0)}M`} />
            <Tooltip
              formatter={(value: number) => [`$${(value / 1000000).toFixed(2)}M`, "Export Value"]}
              labelStyle={{ color: "#000" }}
            />
            <Bar dataKey="value" fill="hsl(var(--primary))" radius={[8, 8, 0, 0]} />
          </BarChart>
        </ResponsiveContainer>
      </CardContent>
    </Card>
  )
}
