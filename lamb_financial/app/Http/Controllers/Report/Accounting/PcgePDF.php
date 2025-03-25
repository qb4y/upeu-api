<?php

namespace App\Http\Controllers\Report\Accounting;

use PDF;

class PcgePDF extends PDF
{
    public static function setEncabezado($params)
    {
        PDF::setHeaderCallback(function ($pdf) use ($params) {
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetLineWidth(0.1);
            $pdf->SetFont('', 'B', 6);
            $pdf->Ln(10);
            $pdf->Cell(0, 0, 'PLAN CONTABLE GENERAL EMPRESARIAL', 0, 0, 'L', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, 'ENTIDAD: ' . $params['empresa_rs'], 0, 0, 'L', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, 'AÑO: ' . $params['anho'], 0, 0, 'L', 0);
            $pdf->Ln(10);
            $pdf->Ln();
        });
    }

}
