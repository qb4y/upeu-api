<?php

namespace App\Http\Controllers\Report\Accounting;

use PDF;

class LibroMayorUpnPDF extends PDF
{

    public static function CustomHeaderLibroMayorUpn($params)
    {
        PDF::setHeaderCallback(function ($pdf) use ($params) {
            // Set font
            // $header = array(
            //     "Fecha de la Operación",
            //     "Número Correlativo del Libro Diario",
            //     "Descripción o Glosa de la Operación",
            //     "Saldos y Movimientos",
            // ); 

            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetLineWidth(0.1);
            $pdf->SetFont('', 'B', 6);

            if($params['primero'] == 'S' ){
                // $pdf->SetY($pdf->GetY() + 8);
            } else {
                $pdf->SetMargins(PDF_MARGIN_LEFT, 39.5, PDF_MARGIN_RIGHT);
                // $pdf->SetHeaderMargin(7.5);
            }

            // Header information
            $pdf->Cell(0, 0, 'FORMATO 6.1: "LIBRO MAYOR"', 0, 0, 'L', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, 'PERÍODO: ' . $params['mes_nombre'] . ' - ' . $params['anho'], 0, 0, 'L', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, 'RUC: ' . $params['empresa_ruc'], 0, 0, 'L', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, 'APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL: ' . $params['empresa_rs'], 0, 0, 'L', 0);
            $pdf->Ln();
            $pdf->Ln();
            // $pdf->Cell(0, 0, 'CODIGO Y/O DENOMINACIÓN DE LA CUENTA CONTABLE (1)', 0, 0, 'L', 0);

            // Table headers
            $w = array(15, 25, 80, 20, 20, 20);
            // $h = 6;
            $h = 4.3;

            $pdf->MultiCell($w[0], $h * 2, "FECHA DE LA OPERACIÓN", 1, 'C', 1, 0, '', '', true, 0, false, true, $h * 2, 'M');
            $pdf->MultiCell($w[1], $h * 2, "NÚMERO CORRELATIVO DEL LIBRO DIARIO (2)", 1, 'C', 1, 0, '', '', true, 0, false, true, $h * 2, 'M');
            $pdf->MultiCell($w[2], $h * 2, "DESCRIPCIÓN O GLOSA DE LA OPERACIÓN", 1, 'C', 1, 0, '', '', true, 0, false, true, $h * 2, 'M');
            $pdf->MultiCell($w[3] + $w[4] + $w[5], $h, "SALDOS Y MOVIMIENTOS", 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');
            $pdf->Ln($h);
            $pdf->SetX($w[0] + $w[1] + $w[2] + 15);

            $pdf->MultiCell($w[3], $h, 'DEUDOR', 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');
            $pdf->MultiCell($w[4], $h, 'ACREEDOR', 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');
            $pdf->MultiCell($w[5], $h, 'SALDO', 1, 'C', 1, 0, '', '', true, 0, false, true, $h, 'M');

            // for ($i = 0; $i < count($header); $i++) {
            //     if ($header[$i] === 'Saldos y Movimientos') {
            //         // Crear celda combinada para "Saldos y Movimientos"
            //         $pdf->Cell($w[1]+5, $h / 2, 'Saldos y Movimientos', 1, 2, 'C', 1);
            //         // Subtítulos "Deudor" y "Acreedor"
            //         $pdf->Cell($w[0], $h / 2, 'Deudor', 1, 0, 'C', 1);
            //         $pdf->Cell($w[0], $h / 2, 'Acreedor', 1, 0, 'C', 1);
            //     } else {
            //         $pdf->Cell($w[$i], $h, $header[$i], '1', 0, 'C', 1);
            //     }
            // }
            $h_dh = 1.8;
            if ($params['rowAccount']) {
                $pdf->Ln();

                $pdf->Cell($w[0], $h_dh, 'CUENTA ', 'B', 0, 'L', 0);       
                $pdf->SetTextColor(!empty($params['rowAccount']->id_cuentaempresarial) ? 0 : 255, 0, 0); // Rojo para "(No tiene equivalencia)"
                $pdf->Cell($w[1], $h_dh, !empty($params['rowAccount']->id_cuentaempresarial) ? $params['rowAccount']->id_cuentaempresarial : $params['rowAccount']->id_cuentaaasi, 'B', 0, 'L', 0);
                $pdf->Cell($w[2], $h_dh, $params['rowAccount']->comentario, 'B', 0, 'L', 0);
                $pdf->SetTextColor(0, 0, 0); // Restablecer a negro para las demás celdas
                $pdf->Cell($w[3], $h_dh, '', 'B', 0, 'R', 0);
                $pdf->Cell($w[4], $h_dh, '', 'B', 0, 'R', 0);
                $pdf->Cell($w[5], $h_dh, '', 'B', 0, 'R', 0);

                // $pdf->Cell($w[0], $h_dh, $params['rowAccount']->fecha, 'BL', 0, 'L', 0);
                // $pdf->Cell($w[0], $h_dh, 'CUENTA', 'BL', 0, 'L', 0);
                // if (!empty($params['rowAccount']->lote)) {
                //     $pdf->SetTextColor(0, 0, 0); // Negro para el texto normal
                //     $lote_text = $params['rowAccount']->lote;
                // } else {
                //     $pdf->SetTextColor(255, 0, 0); // Rojo para "(No tiene equivalencia)"
                //     $lote_text = 'No equivalencia';
                // }
                // $pdf->Cell($w[1], $h_dh, $lote_text, 'B', 0, 'L', 0);
                // $pdf->SetTextColor(0, 0, 0); // Restablecer a negro para las demás celdas
                // $pdf->Cell($w[2], $h_dh, $params['rowAccount']->comentario, 'B', 0, 'L', 0);
            }
            $pdf->Ln();

            if ($params['debe'] <> 0 || $params['haber'] <> 0) {
                $pdf->SetFont('', ''); // Quitar la negrita
                // $pdf->Cell($w[0] + $w[1] + $w[2], $h_dh, 'VIENEN: ', 1, 0, 'R', 0, '', 1);
                $pdf->Cell($w[0] + $w[1] + $w[2], $h_dh, 'VIENEN: ', 1, 0, 'R', 0);
                $pdf->Cell($w[3], $h_dh, number_format($params['debe'], 2, '.', ',').'  ', 1, 0, 'R', 0);
                $pdf->Cell($w[4], $h_dh, number_format($params['haber'], 2, '.', ',').'  ', 1, 0, 'R', 0);
                $pdf->Cell($w[5], $h_dh, number_format($params['saldo'], 2, '.', ',').'  ', 1, 0, 'R', 0);
                $pdf->Ln();
                $pdf->SetFont('', 'B', 6); // Restablecer el formato de fuente al predeterminado
            }
        });
    }

    public static function CustomFooterParamsLibroMayorUpn($params)
    {
        PDF::setFooterCallback(function ($pdf) use ($params) {
            // $text = '';

            // if ($pdf->getAliasNumPage() == $pdf->getAliasNbPages()) {
            //     $text = 'Última página';
            // }

            // $hoy = date("d/m/Y H:i:s");
            // $w = array(10, 10, 95, 20, 20,0);
            $w = array(15, 25, 79, 20, 20, 20);
            // $w = array(30,35, 80, 43,13,1);
            // $w = array(30,35, 80,50,13,13,13);
            // $h = 4.3;
            $h = 1.8;

            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B', 6);

            if ($params['ultimo'] == 'N') {
                // $pdf->SetFont('', ''); // Quitar la negrita
                $pdf->Cell($w[0] + $w[1] + $w[2], $h, 'VAN: ', 'TB', 0, 'R', 0, '', 1);
                $pdf->Cell($w[3], $h, number_format($params['debe'], 2, '.', ','), 'TB', 0, 'R', 0);  // Doble raya
                $pdf->Cell($w[4], $h, number_format($params['haber'], 2, '.', ','), 'TB', 0, 'R', 0);  // Doble raya
                $pdf->Cell($w[5], $h, number_format($params['saldo'], 2, '.', ','), 'TB', 0, 'R', 0);  // Doble raya
                $pdf->Ln();
                // $pdf->SetFont('', 'B', 6); // Restablecer el formato de fuente al predeterminado
            }
            $pdf->Cell($w[3] + $w[4], $h, '', 0, 0, 'C', 0, '', 1);
            $pdf->Cell($w[2] + 23, $h, 'LIBRO MAYOR ', 0, 0, 'C', 0, '', 1);
            $pdf->Cell($w[3] + $w[4], $h, $params['mes_nombre'] . ' ' . $params['anho'] . ' - Página ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, 0, 'L', 0);
            $pdf->Ln();
        });
    }
}
