<?php

namespace App\Http\Controllers\Report\Accounting;

// use PDF;
// use TCPDF;
use Elibyy\TCPDF\TCPDF;

class LibroDiarioUpnPDF extends TCPDF
{
    // private $totalPages = 0;

    public function Header($headerParams)
    {
        $this->setHeaderCallback(function ($pdf) use ($headerParams) {
            $this->SetFillColor(224, 235, 255);
            $this->SetTextColor(0);
            // $this->SetDrawColor(128, 0, 0);
            $this->SetLineWidth(0.1);
            $this->SetFont('', 'B', 6);

            // Header
            $this->Cell(0, 0, 'FORMATO 5.1: "LIBRO DIARIO"', 0, 0, 'L', 0);
            $this->Ln();
            $this->Cell(0, 0, 'PERIODO: ' . $headerParams['mes_nombre'] . '-' . $headerParams['anho'], 0, 0, 'L', 0);
            $this->Ln();
            $this->Cell(0, 0, 'RUC: ' . $headerParams['empresa_ruc'], 0, 0, 'L', 0);
            $this->Ln();
            $this->Cell(0, 0, 'APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL: ' . $headerParams['empresa_rs'], 0, 0, 'L', 0);
            $this->Ln();
            $this->Ln();
            $h = 4.3;

            $w = array(12, 12, 68, 12, 18, 15, 14, 42, 17, 17);

            $this->MultiCell($w[0], $h * 2, "CUO", 1, 'C', 1, 0, '', '', true, 0, false, true, $h * 2, 'M');
            // $this->MultiCell($w[0], $h * 2, "NÚMERO CORRELATIVO DEL ASIENTO O CÓDIGO ÚNICO DE LA OPERACIÓN", 1, 'C', 1, 0);
            // $this->MultiCell($w[1], $h * 2, "FECHA DE LA OPERACIÓN", 1, 'C', 1, 0, '', '', true, 0, false, true, $h * 2, 'M');
            $this->MultiCell($w[2], $h * 2, "GLOSA O DESCRIPCIÓN DE LA OPERACIÓN", 1, 'C', 1, 0, '', '', true, 0, false, true, $h * 2, 'M');
            // $this->MultiCell($w[3] + $w[4] + $w[5], $h, "REFERENCIA DE LA OPERACIÓN", 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');
            $this->MultiCell($w[6] + $w[6] + $w[7], $h, "CUENTA CONTABLE ASOCIADA A LA OPERACIÓN", 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');
            $this->MultiCell($w[8] + $w[9], $h, "MOVIMIENTO", 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');

            $this->Ln($h);
            // $this->SetX($w[0] + $w[1] + $w[2] + 15);
            $this->SetX($w[0] + $w[1] + $w[2] + 3);
            // $this->MultiCell($w[3], $h, "CÓDIGO DEL LIBRO O REGISTRO (TABLA 8)", 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');
            // $this->MultiCell($w[4], $h, 'NÚMERO CORRELATIVO', 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');
            // $this->MultiCell($w[5], $h, 'NÚMERO DEL DOCUMENTO SUSTENTATORIO', 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');

            $this->MultiCell($w[6], $h, 'CÓDIGO', 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');
            $this->MultiCell($w[6], $h, 'SUB', 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');
            $this->MultiCell($w[7], $h, 'DENOMINACIÓN', 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');

            $this->MultiCell($w[8], $h, 'DEBE', 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');
            $this->MultiCell($w[9], $h, 'HABER', 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');

            $this->Ln();
        });
    }

    public function Footer($footerParams)
    {
        $cellWidths = [12, 12, 68, 12, 18, 15, 14, 42, 17, 17];
        $rowHeight = 1.8;

        // $this->setFooterCallback(function ($pdf) use ($footerParams, $cellWidths, $rowHeight, $totalPages) {

        $this->SetFillColor(224, 235, 255, 255);
        $this->SetTextColor(0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B', 6);

        $paginaActual = ($this->getPage()) + ($footerParams['entidad_anterior_pag_final'] ?? 0);

        // $this->Cell($w[3] + $w[4], $h, $params['mes_nombre'] . ' ' . $params['anho'] . ' - Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'L', 0);
        $this->Cell($cellWidths[8] + $cellWidths[9] + 7, $rowHeight, '', 0, 0, 'C', 0, '', 1);
        $this->Cell($cellWidths[2] + $cellWidths[7], $rowHeight, 'LIBRO DIARIO ', 0, 0, 'C', 0, '', 1);

        $textoPie = sprintf(
            '%s %d - Página %d/%s',
            $footerParams['mes_nombre'] ?? '',
            $footerParams['anho'] ??  '',
            $paginaActual,
            $footerParams['total_paginas']
        );
        $this->Cell($cellWidths[8] + $cellWidths[9], $rowHeight, $textoPie, 0, 0, 'L', 0);
        $this->Ln();
        // });
    }
}
