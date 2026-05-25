<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

trait DeliversRekapitulasiAjax
{
    protected function rekapitulasiAjaxResponse(LengthAwarePaginator $dataRekap): JsonResponse
    {
        $groupedData = collect($dataRekap->items())->groupBy('nama_polres');

        return response()->json([
            'status' => 'ok',
            'html' => [
                'table' => view('components.rekapitulasi.table-block', [
                    'dataRekap' => $dataRekap,
                    'groupedData' => $groupedData,
                ])->render(),
                'mobile' => view('components.rekapitulasi.mobile-block', [
                    'dataRekap' => $dataRekap,
                    'groupedData' => $groupedData,
                ])->render(),
            ],
            'meta' => [
                'current_page' => $dataRekap->currentPage(),
                'last_page' => $dataRekap->lastPage(),
                'total' => $dataRekap->total(),
                'summary' => sprintf(
                    'Hal %d dari %d • Total %s data',
                    $dataRekap->currentPage(),
                    $dataRekap->lastPage(),
                    number_format($dataRekap->total())
                ),
            ],
        ]);
    }
}
