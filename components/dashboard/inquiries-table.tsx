"use client"

import { useState } from "react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

interface Inquiry {
  id: string
  buyerName: string
  buyerEmail: string
  company: string
  message: string
  date: string
  status: "new" | "replied" | "archived"
}

interface InquiriesTableProps {
  inquiries: Inquiry[]
}

export function InquiriesTable({ inquiries }: InquiriesTableProps) {
  const [selectedInquiry, setSelectedInquiry] = useState<Inquiry | null>(null)

  const getStatusBadge = (status: Inquiry["status"]) => {
    switch (status) {
      case "new":
        return <Badge className="bg-green-100 text-green-800">New</Badge>
      case "replied":
        return <Badge className="bg-blue-100 text-blue-800">Replied</Badge>
      case "archived":
        return <Badge className="bg-gray-100 text-gray-800">Archived</Badge>
      default:
        return <Badge className="bg-red-100 text-red-800">Unknown</Badge>
    }
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Inquiries</CardTitle>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>ID</TableHead>
              <TableHead>Buyer Name</TableHead>
              <TableHead>Buyer Email</TableHead>
              <TableHead>Company</TableHead>
              <TableHead>Message</TableHead>
              <TableHead>Date</TableHead>
              <TableHead>Status</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {inquiries.map((inquiry) => (
              <TableRow key={inquiry.id}>
                <TableCell>{inquiry.id}</TableCell>
                <TableCell>{inquiry.buyerName}</TableCell>
                <TableCell>{inquiry.buyerEmail}</TableCell>
                <TableCell>{inquiry.company}</TableCell>
                <TableCell>{inquiry.message}</TableCell>
                <TableCell>{inquiry.date}</TableCell>
                <TableCell>{getStatusBadge(inquiry.status)}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  )
}
