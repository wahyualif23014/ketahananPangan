<?php
$opFile = 'C:\laragon\www\ketahananPangan\resources\views\operator\kelola-lahan\operator_riwayat\index.blade.php';
$viewFile = 'C:\laragon\www\ketahananPangan\resources\views\view\kelola-lahan\view_riwayat\index.blade.php';

$opHtml = file_get_contents($opFile);
$viewHtml = file_get_contents($viewFile);

// 1. Extract detailModalData state & methods
$stateToAdd = "                detailModalData: { isOpen: false, type: '', data: {} },\n\n                openDetailModal(type, data) {\n                    this.detailModalData = {\n                        isOpen: true,\n                        type,\n                        data: data || {}\n                    };\n                },\n\n                closeDetailModal() {\n                    this.detailModalData.isOpen = false;\n                },";

if (strpos($viewHtml, 'detailModalData: {') === false) {
    $viewHtml = str_replace('openResors: [],', "openResors: [],\n" . $stateToAdd, $viewHtml);
}

// 2. Extract the modal HTML block
$modalStart = '<!-- MODAL DETAIL RIWAYAT -->';
$modalEndPos = strpos($opHtml, '<!-- MODAL PROSES TANAM -->'); // next modal
if ($modalEndPos === false) {
    $modalEndPos = strlen($opHtml);
}

$startPos = strpos($opHtml, $modalStart);
if ($startPos !== false) {
    $modalHtml = substr($opHtml, $startPos, $modalEndPos - $startPos);
    
    // remove existing if already there (for idempotency)
    if (strpos($viewHtml, $modalStart) !== false) {
        // if already there, we might not need to add it, but let's replace it
        $existingStart = strpos($viewHtml, $modalStart);
        $existingEnd = strpos($viewHtml, '<!-- MODAL PROSES PANEN -->');
        if ($existingEnd === false) {
            $existingEnd = strpos($viewHtml, '</div>', $existingStart);
        }
        $viewHtml = substr_replace($viewHtml, $modalHtml, $existingStart, $existingEnd - $existingStart);
    } else {
        // Insert right before <!-- MODAL PROSES PANEN --> if exists, or before the final </div>
        $insertPoint = strpos($viewHtml, '<!-- MODAL PROSES PANEN -->');
        if ($insertPoint === false) {
            $insertPoint = strrpos($viewHtml, '</div>');
        }
        $viewHtml = substr_replace($viewHtml, $modalHtml . "\n", $insertPoint, 0);
    }
}

file_put_contents($viewFile, $viewHtml);
echo "Added detail modal successfully!";
