"use client"

import type { ColumnDef } from "@tanstack/react-table"
import { MoreHorizontal } from "lucide-react"

import { Button } from "@/components/ui/button"
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { DataTableColumnHeader } from "@/components/data-table/data-table-column-header"

// This type is used to define the shape of our data.
export type Payment = {
    id: string
    time: string
    status: "pending" | "processing" | "success" | "failed"
    filename: string
}

export const columns: ColumnDef<Payment>[] = [
    {
        accessorKey: "updated_at",
        header: ({ column }) => <DataTableColumnHeader column={column} title="Time" />,
    },
    {
        accessorKey: "filename",
        header: ({ column }) => <DataTableColumnHeader column={column} title="File Name" />,
    },
    {
        accessorKey: "status",
        header: ({ column }) => <DataTableColumnHeader column={column} title="Status" />,
        cell: ({ row }) => {
            const status = row.getValue("status") as string
            return (
                <div className="flex items-center">
                    <div
                        className={`mr-2 h-2 w-2 rounded-full ${status === "pending"
                            ? "bg-yellow-500"
                            : status === "processing"
                                ? "bg-blue-500"
                                : status === "completed"
                                    ? "bg-green-500"
                                    : "bg-red-500"
                            }`}
                    />
                    <span className="capitalize">{status}</span>
                </div>
            )
        },
    },
]
