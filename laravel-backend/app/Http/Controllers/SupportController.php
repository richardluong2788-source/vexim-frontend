<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\SupportTicketReceived;
use App\Mail\SupportTicketReply;

class SupportController extends Controller
{
    /**
     * Create a new support ticket
     */
    public function createTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();

            $ticket = SupportTicket::create([
                'user_id' => $user->id,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'open',
            ]);

            AuditLog::log(
                'support_ticket_created',
                'SupportTicket',
                $ticket->id,
                null,
                $ticket->toArray(),
                'User created support ticket'
            );

            // Send confirmation email to user
            Mail::to($user->email)->queue(
                new SupportTicketReceived($ticket, $user)
            );

            // Notify admin (send to configured admin email)
            $adminEmail = config('mail.admin_email', 'admin@veximglobal.com');
            Mail::to($adminEmail)->queue(
                new SupportTicketReceived($ticket, $user)
            );

            return response()->json([
                'success' => true,
                'message' => 'Support ticket created successfully. Our team will respond within 24 hours.',
                'data' => $ticket
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's support tickets
     */
    public function getUserTickets(Request $request)
    {
        $user = auth()->user();

        $tickets = $user->supportTickets()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Get single ticket details
     */
    public function getTicket($id)
    {
        $user = auth()->user();

        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        // Check authorization (user can only view their own tickets, admin can view all)
        if (!$user->isAdmin() && $ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $ticket->load('user')
        ]);
    }

    /**
     * Admin: Get all support tickets
     */
    public function getAllTickets(Request $request)
    {
        $status = $request->get('status');
        
        $query = SupportTicket::with('user');

        if ($status) {
            $query->where('status', $status);
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Admin: Get pending tickets count
     */
    public function getPendingCount()
    {
        $count = SupportTicket::pending()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'pending_count' => $count
            ]
        ]);
    }

    /**
     * Admin: Reply to support ticket
     */
    public function replyToTicket(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_reply' => 'required|string|max:2000',
            'status' => 'required|in:in_progress,resolved,closed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = SupportTicket::with('user')->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        try {
            $oldValues = $ticket->toArray();

            $ticket->update([
                'admin_reply' => $request->admin_reply,
                'status' => $request->status,
                'replied_at' => now(),
            ]);

            AuditLog::log(
                'support_ticket_replied',
                'SupportTicket',
                $ticket->id,
                $oldValues,
                $ticket->fresh()->toArray(),
                'Admin replied to support ticket'
            );

            // Send reply email to user
            Mail::to($ticket->user->email)->queue(
                new SupportTicketReply($ticket, $ticket->user)
            );

            return response()->json([
                'success' => true,
                'message' => 'Reply sent successfully',
                'data' => $ticket->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reply',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Update ticket status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        try {
            $oldValues = $ticket->toArray();

            $ticket->update([
                'status' => $request->status,
            ]);

            AuditLog::log(
                'support_ticket_status_updated',
                'SupportTicket',
                $ticket->id,
                $oldValues,
                $ticket->fresh()->toArray(),
                'Admin updated ticket status to: ' . $request->status
            );

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $ticket->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
