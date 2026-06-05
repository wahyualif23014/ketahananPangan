<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;

trait DeliversDashboardMetricsJson
{
    protected function dashboardMetricsResponse(array $data): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'data' => [
                'potensiTotal' => $this->formatMetricNumber($data['potensiTotal'] ?? 0),
                'tanamTotal' => $this->formatMetricNumber($data['tanamTotal'] ?? 0),
                'panenTotal' => $this->formatMetricNumber($data['panenTotal'] ?? 0),
                'totalTitikLahan' => number_format((int) ($data['totalTitikLahan'] ?? 0), 0, ',', '.'),
                'totalSerapan' => $this->formatMetricNumber($data['totalSerapan'] ?? 0),
                'totalPendingPotensi' => (int) ($data['totalPendingPotensi'] ?? 0),
                'totalPendingKelola' => (int) ($data['totalPendingKelola'] ?? 0),
                'chartTotalLabel' => $this->formatMetricNumber($data['potensiTotal'] ?? 0) . ' Ha',
            ],
            'meta' => [
                'refreshed_at' => now()->toIso8601String(),
            ],
        ]);
    }

    private function formatMetricNumber($value): string
    {
        return number_format((float) $value, 2, ',', '.');
    }
}
