<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Get audit logs with filters
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        // Filter by user
        if ($request->has('user_id')) {
            $query->byUser($request->user_id);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->byAction($request->action);
        }

        // Filter by model type
        if ($request->has('model_type')) {
            $query->byModel($request->model_type);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by recent days
        if ($request->has('recent_days')) {
            $query->recent($request->recent_days);
        }

        // Search in description
        if ($request->has('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get audit log details
     */
    public function show($id)
    {
        $log = AuditLog::with(['user', 'auditable'])->find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Audit log not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'log' => $log,
                'changes' => $log->getChanges(),
            ]
        ]);
    }

    /**
     * Get audit logs for specific entity
     */
    public function getEntityLogs($modelType, $modelId)
    {
        $logs = AuditLog::with('user')
            ->where('auditable_type', $modelType)
            ->where('auditable_id', $modelId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get user activity logs
     */
    public function getUserActivity($userId)
    {
        $logs = AuditLog::byUser($userId)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get audit statistics
     */
    public function getStatistics(Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);

        $stats = [
            'total_actions' => AuditLog::where('created_at', '>=', $startDate)->count(),
            'unique_users' => AuditLog::where('created_at', '>=', $startDate)
                ->distinct('user_id')
                ->count('user_id'),
            'by_action' => AuditLog::where('created_at', '>=', $startDate)
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->get(),
            'by_model' => AuditLog::where('created_at', '>=', $startDate)
                ->selectRaw('auditable_type, COUNT(*) as count')
                ->groupBy('auditable_type')
                ->orderBy('count', 'desc')
                ->get(),
            'most_active_users' => AuditLog::with('user')
                ->where('created_at', '>=', $startDate)
                ->selectRaw('user_id, COUNT(*) as action_count')
                ->groupBy('user_id')
                ->orderBy('action_count', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get timeline of actions
     */
    public function getTimeline(Request $request)
    {
        $days = $request->input('days', 7);
        $startDate = now()->subDays($days);

        $timeline = AuditLog::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $timeline
        ]);
    }

    /**
     * Export audit logs
     */
    public function export(Request $request)
    {
        $query = AuditLog::with('user');

        // Apply same filters as index
        if ($request->has('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->has('action')) {
            $query->byAction($request->action);
        }

        if ($request->has('model_type')) {
            $query->byModel($request->model_type);
        }

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // Convert to CSV format
        $csv = $this->convertToCSV($logs);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="audit-logs-' . now()->format('Y-m-d') . '.csv"');
    }

    /**
     * Convert logs to CSV format
     */
    private function convertToCSV($logs)
    {
        $headers = ['ID', 'User', 'Action', 'Model Type', 'Model ID', 'Description', 'IP Address', 'Date'];
        $rows = [];

        foreach ($logs as $log) {
            $rows[] = [
                $log->id,
                $log->user ? $log->user->name : 'System',
                $log->action,
                $log->auditable_type,
                $log->auditable_id,
                $log->description,
                $log->ip_address,
                $log->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
