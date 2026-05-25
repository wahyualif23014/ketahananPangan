<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait RespondsWithRekapitulasiAjax
{
    protected function rekapitulasiAjaxResponse(Request $request, LengthAwarePaginator $dataRekap): JsonResponse
    {
        $groupedData = collect($dataRekap->items())->groupBy('nama_polres');

        return response()->json([
            'status' => 'ok',
            'html' => [
                'table' => view('components.rekapitulasi.table-block', compact('dataRekap', 'groupedData'))->render(),
                'mobile' => view('components.rekapitulasi.mobile-block', compact('dataRekap', 'groupedData'))->render(),
            ],
            'meta' => [
                'current_page' => $dataRekap->currentPage(),
                'last_page' => $dataRekap->lastPage(),
                'total' => $dataRekap->total(),
                'summary' => sprintf(
                    'Hal %d dari %d · Total %s data',
                    $dataRekap->currentPage(),
                    $dataRekap->lastPage(),
                    number_format($dataRekap->total())
                ),
            ],
        ]);
    }
}
