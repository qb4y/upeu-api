<?php
namespace App\Http\Controllers\Report\Accounting;

use PDF;

class CustomPDF extends PDF {

    public static function CustomHeader0($params) {

        PDF::setHeaderCallback(function($pdf) use ($params) {
            // Set font

            $header = array(
                "#",
                "Cuenta",
                "Sub Cuenta",
                "Nombre de la Cuenta",
                "Glosa",
                "Débito",
                "Crédito",
            ); 
    
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            // $pdf->SetDrawColor(128, 0, 0);
            $pdf->SetLineWidth(0.1);
            $pdf->SetFont('', 'B',4.5);
            if($params['debe'] == 0 || $params['haber'] == 0 ){
                $pdf->SetY($pdf->GetY()+2);
            }
            // Header
            $pdf->Cell(0, 0, 'LIBRO DIARIO', 0, 0, 'C', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, $params['empresa_rs'], 0, 0, 'C', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, 'RUC: '.$params['empresa_ruc'], 0, 0, 'C', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, $params['mes_nombre'].' - '.$params['anho'], 0, 0, 'C', 0);
            $pdf->Ln();
            $pdf->Ln();
            $w = array(7,10, 10, 35, 100,13,13);
            $h = 1.8;
            $num_headers = count($header);
            // for($i = 0; $i < $num_headers; ++$i) {
                $pdf->Cell($w[0], $h, $header[0], '1', 0, 'C', 1);
                $pdf->Cell($w[1], $h, $header[1], '1', 0, 'C', 1);
                $pdf->Cell($w[2], $h, $header[2], '1', 0, 'C', 1, '', 1);
                $pdf->Cell($w[3], $h, $header[3], '1', 0, 'C', 1);
                $pdf->Cell($w[4], $h, $header[4], '1', 0, 'C', 1);
                $pdf->Cell($w[5], $h, $header[5], '1', 0, 'C', 1);
                $pdf->Cell($w[6], $h, $header[6], '1', 0, 'C', 1);
            // }
            $pdf->Ln();
            if($params['debe'] <> 0 || $params['haber'] <> 0 ){
                $pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4], $h, 'Vienen', 'B', 0, 'R', 0, '', 1);
                $pdf->Cell($w[5], $h, number_format($params['debe'], 2, '.', ','), 'LRB', 0, 'R', 0);
                $pdf->Cell($w[6], $h, number_format($params['haber'], 2, '.', ','), 'LRB', 0, 'R', 0);
                $pdf->Ln();
            }

        });
    }


    // Page footer
        // Page footer
    public static function CustomFooterParams($params) {
        PDF::setFooterCallback(function($pdf) use ($params){
            // $text = '';

            // if ($pdf->getAliasNumPage() == $pdf->getAliasNbPages())
            // {
            //     $text = 'last page';
            // }

            // $hoy = date("d/m/Y H:i:s");
            $w = array(7,10, 10, 35, 100,13,13);
            $h = 1.8;
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            // $pdf->SetDrawColor(128, 0, 0);
            $pdf->SetLineWidth(0.1);
            $pdf->SetFont('', 'B',4.5);

            if ($params['ultimo'] == 'N'){
                $pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4], $h, 'Van  ', 'LB', 0, 'R', 0, '', 1);
                $pdf->Cell($w[5], $h, number_format($params['debe'], 2, '.', ','), 'LRB', 0, 'R', 0);
                $pdf->Cell($w[6], $h, number_format($params['haber'], 2, '.', ','), 'LRB', 0, 'R', 0);
                $pdf->Ln();
            }

            $pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4], $h, 'Libro Diario ', 0, 0, 'C', 0, '', 1);
            $pdf->Cell($w[5]+$w[6], $h, '  '.$params['mes_nombre'].' '.$params['anho'].' Pag.'.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, 0, 'L', 0);
            $pdf->Ln();
        });

    }

    // --Libro Mayor

    public static function CustomHeaderLibroMayor($params) {

        PDF::setHeaderCallback(function($pdf) use ($params) {
            // Set font

            $header = array(
                "#",
                "Fecha",
                "Lote",
                "Glosa",
                "Débito",
                "Crédito",
                "Saldo",
            ); 
    
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            // $pdf->SetDrawColor(128, 0, 0);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B',5);
            if($params['primero'] == 'S' ){
                $pdf->SetY($pdf->GetY()+4);
            }
            // Header
            $pdf->Cell(0, 0, 'LIBRO MAYOR', 0, 0, 'C', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, $params['empresa_rs'], 0, 0, 'C', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, 'RUC: '.$params['empresa_ruc'], 0, 0, 'C', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, $params['mes_nombre'].' - '.$params['anho'], 0, 0, 'C', 0);
            $pdf->Ln();
            $pdf->Ln();
            $w = array(7,10, 10, 122,13,13,13);
            $h = 4;
            $num_headers = count($header);
            // for($i = 0; $i < $num_headers; ++$i) {
                $pdf->Cell($w[0], $h, $header[0], '1', 0, 'C', 1);
                $pdf->Cell($w[1], $h, $header[1], '1', 0, 'C', 1);
                $pdf->Cell($w[2], $h, $header[2], '1', 0, 'C', 1, '', 1);
                $pdf->Cell($w[3], $h, $header[3], '1', 0, 'C', 1);
                $pdf->Cell($w[4], $h, $header[4], '1', 0, 'C', 1);
                $pdf->Cell($w[5], $h, $header[5], '1', 0, 'C', 1);
                $pdf->Cell($w[6], $h, $header[6], '1', 0, 'C', 1);
            // }
            $pdf->Ln();
            if($params['debe'] <> 0 || $params['haber'] <> 0 ){
                $pdf->Cell($w[0]+$w[1]+$w[2]+$w[3], $h, 'Vienen', 'B', 0, 'R', 0, '', 1);
                $pdf->Cell($w[4], $h, number_format($params['debe'], 2, '.', ','), 'LRB', 0, 'R', 0);
                $pdf->Cell($w[5], $h, number_format($params['haber'], 2, '.', ','), 'LRB', 0, 'R', 0);
                $pdf->Cell($w[6], $h, number_format($params['saldo'], 2, '.', ','), 'LRB', 0, 'R', 0);
                $pdf->Ln();
            }

        });
    }
    public static function CustomFooterParamsLibroMayor($params) {
        PDF::setFooterCallback(function($pdf) use ($params){
            $text = '';

            if ($pdf->getAliasNumPage() == $pdf->getAliasNbPages())
            {
                $text = 'last page';
            }

            $hoy = date("d/m/Y H:i:s");
            $w = array(7,10, 10, 122,13,13,13);
            $h = 4;
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            // $pdf->SetDrawColor(128, 0, 0);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B',5);

            if ($params['ultimo'] == 'N'){
                $pdf->Cell($w[0]+$w[1]+$w[2]+$w[3], $h, 'Van  ', 'B', 0, 'R', 0, '', 1);
                $pdf->Cell($w[4], $h, number_format($params['debe'], 2, '.', ','), 'LRB', 0, 'R', 0);
                $pdf->Cell($w[5], $h, number_format($params['haber'], 2, '.', ','), 'LRB', 0, 'R', 0);
                $pdf->Cell($w[6], $h, number_format($params['saldo'], 2, '.', ','), 'LRB', 0, 'R', 0);
                $pdf->Ln();
            }

            $pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4], $h, 'Libro Mayor UPN ', 0, 0, 'C', 0, '', 1);
            $pdf->Cell($w[5]+$w[6], $h, '  '.$params['mes_nombre'].' '.$params['anho'].' Pag.'.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, 0, 'L', 0);
            $pdf->Ln();
        });

    }
    
    // --Libro Mayor upn

    public static function CustomHeaderLibroMayorUpn($params) {
        PDF::setHeaderCallback(function($pdf) use ($params) {
            // Set font
            $header = array(
                "Fecha de la Operación",
                "Número Correlativo del Libro Diario",
                "Descripción o Glosa de la Operación",
                "Saldos y Movimientos",
            ); 
    
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B', 5);
    
            if($params['primero'] == 'S' ){
                $pdf->SetY($pdf->GetY() + 4);
            }
    
            // Header information
            $pdf->Cell(0, 0, 'FORMATO 6.1: "LIBRO MAYOR PCGE - UPN"', 0, 0, 'L', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, 'PERÍODO: ' . $params['mes_nombre'] . ' - ' . $params['anho'], 0, 0, 'L', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, 'RUC: ' . $params['empresa_ruc'], 0, 0, 'L', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, 'APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL: ' . $params['empresa_rs'], 0, 0, 'L', 0);
            $pdf->Ln();
            // $pdf->Cell(0, 0, 'CODIGO Y/O DENOMINACIÓN DE LA CUENTA CONTABLE (1)', 0, 0, 'L', 0);
            
    
            // Table headers
            $w = array(20, 35, 90, 30);
            $h = 6;
            
            for ($i = 0; $i < count($header); $i++) {
                if ($header[$i] === 'Saldos y Movimientos') {
                    // Crear celda combinada para "Saldos y Movimientos"
                    $pdf->Cell($w[1]+5, $h / 2, 'Saldos y Movimientos', 1, 2, 'C', 1);
                    // Subtítulos "Deudor" y "Acreedor"
                    $pdf->Cell($w[0], $h / 2, 'Deudor', 1, 0, 'C', 1);
                    $pdf->Cell($w[0], $h / 2, 'Acreedor', 1, 0, 'C', 1);
                } else {
                    $pdf->Cell($w[$i], $h, $header[$i], '1', 0, 'C', 1);
                }
            }
            $pdf->Ln();
            if($params['debe'] <> 0 || $params['haber'] <> 0 ){
                $pdf->Cell($w[0] + $w[1] + $w[2], $h/1.5, 'Vienen', 1, 0, 'R', 0, '', 1);
                $pdf->Cell($w[3]-10, $h/1.5, number_format($params['debe'], 2, '.', ','), 1, 0, 'R', 0);
                $pdf->Cell($w[3]-10, $h/1.5, number_format($params['haber'], 2, '.', ','), 1, 0, 'R', 0);        
                $pdf->Ln();
            }
        });
    }

    

    public static function CustomFooterParamsLibroMayorUpn($params) {
        PDF::setFooterCallback(function($pdf) use ($params){
            $text = '';
    
            if ($pdf->getAliasNumPage() == $pdf->getAliasNbPages()) {
                $text = 'Última página';
            }
    
            $hoy = date("d/m/Y H:i:s");
            $w = array(10, 10, 95, 20, 20,0);
            // $w = array(30,35, 80, 43,13,1);
            // $w = array(30,35, 80,50,13,13,13);
            $h = 5;
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B', 5);
    
            if ($params['ultimo'] == 'N'){
                $pdf->Cell($w[2] + $w[3] + 30 , $h, 'Van  ', 'B', 0, 'R', 0, '', 1);
                $pdf->Cell($w[3], $h, number_format($params['debe'], 1, '.', ','), 'TLRB', 0, 'R', 0);  // Doble raya
                $pdf->Cell($w[4], $h, number_format($params['haber'],1, '.', ','), 'TLRB', 0, 'R', 0);  // Doble raya
                // $pdf->Cell($w[5], $h, number_format($params['saldo'], 2, '.', ','), 'TLRB', 0, 'R', 0);  // Doble raya
                $pdf->Ln();
        
                
            }
    
            $pdf->Cell(array_sum($w), $h, 'Libro Mayor PCGE - UPN ', 0, 0, 'C', 0, '', 1);
            $pdf->Cell(array_sum($w), $h, $params['mes_nombre'] . ' ' . $params['anho'] . ' Pag. ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, 0, 'L', 0);
            $pdf->Ln();
        });
    }
    


    // LIBRO REGISTRO DE COMPRAS

    public static function CustomFooterRegistroDeCompras($params) {
        PDF::setFooterCallback(function($pdf) use ($params){
            $text = '';

            if ($pdf->getAliasNumPage() == $pdf->getAliasNbPages())
            {
                $text = 'last page';
            }

            $hoy = date("d/m/Y H:i:s");
            $w = array(
                10,  10, 9, 8, 8,  
                5,  5,  5,  8,  5,  
                9, 45,  8,  8,  8,  
                8,  8,  8,  8,  8, 
                8,  8,  8,  8,  8, 
                8,  8,  5,  8,  8,
                4, 
            );
            $h = 1.8;
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            // $pdf->SetDrawColor(128, 0, 0);
            $pdf->SetLineWidth(0.1);
            $pdf->SetFont('', 'B',4.5);

            // if ($params['ultimo'] == 'N'){
            //     $pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4], $h, 'Van  ', 'LB', 0, 'R', 0, '', 1);
            //     $pdf->Cell($w[5], $h, number_format($params['debe'], 2, '.', ','), 'LRB', 0, 'R', 0);
            //     $pdf->Cell($w[6], $h, number_format($params['haber'], 2, '.', ','), 'LRB', 0, 'R', 0);
            //     $pdf->Ln();
            // }
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Cell(
                0
            , $h, 'Registro de Compras ', 0, 0, 'C', 0, '', 1);
            // $pdf->Cell($w[25]+$w[26]+$w[27]+$w[28]+$w[29]+$w[30], $h, '  '.$params['mes_nombre'].' '.$params['anho'].' Pag.'.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, 0, 'L', 0);
            $pdf->Ln();
        });

    }

    public static function CustomHeaderRegistroDeCompras($params) {

        PDF::setHeaderCallback(function($pdf) use ($params) {
            // Set font

            $header = array(
                "Correlativo",
                "CUO",
                "Usuario",
                "Fecha de Emisión",
                "Fecha de Vencimiento",
                // "Comprobante de pago o documento",
                "Tipo",
                "Serie",
                "Año",
                "Nro del comprobante",
                // "Información del proveedor",
                // "Documento",
                "Tipo",
                "Número",
                "Apellidos y Nombres o razón social",
                // "Adquisiciones gravadas destinadas a operaciones gravadas y/o de exportación",
                "B. Imp.",
                "IGV",
                // "Adquisiciones gravadas destinadas a operaciones gravadas y/o de exportación y a operaciones no gravadas",
                "B. Imp.",
                "IGV",
                // "Adquisiciones gravadas destinadas a operaciones no gravadas",
                "B. Imp.",
                "IGV",
                "Valor de las adquisiciones no gravadas",
                "ISC",
                "Otros tributos y cargos",
                "Importe total",
                "Nro de comprovante de pago emitido",
                // "Constancia de depósito de detracción",
                "Nro",
                "Fecha",
                "Tipo de Cambio",
                // "Referecia del comprobante de pago o documento original que se modificará",
                "Fecha",
                "Tipo",
                "Serie",
                "Número",
                "RET",
            ); 
    
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            // $pdf->SetDrawColor(128, 0, 0);
            $pdf->SetLineWidth(0.1);
            $pdf->SetFont('', 'B',4.5);
            if($params['debe'] == 0 || $params['haber'] == 0 ){
                $pdf->SetY($pdf->GetY()+2);
            }
            // Header
            $pdf->Cell(0, 0, 'REGISTRO DE COMPRAS', 0, 0, 'C', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, $params['empresa_rs'], 0, 0, 'C', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, 'RUC: '.$params['empresa_ruc'], 0, 0, 'C', 0);
            $pdf->Ln();
            $pdf->Cell(0, 0, $params['mes_nombre'].' - '.$params['anho'], 0, 0, 'C', 0);
            $pdf->Ln();
            $pdf->Ln();
            $w = array(
                10,  10, 9, 8, 8,  
                5,  5,  5,  8,  5,  
                9, 45,  8,  8,  8,  
                8,  8,  8,  8,  8, 
                8,  8,  8,  8,  8, 
                8,  8,  5,  8,  8,
                4, 
            );
            $h = 20;
            $num_headers = count($header);

            $pdf->SetFillColor(43, 105, 144);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetDrawColor(187, 200, 208);
            // $pdf->MultiCell($w[0], $h, 'ÁREAS' ,1,'C',1,false,'','',true,false,false,true,'','M',true);
                

            // for($i = 0; $i < $num_headers; ++$i) {
                $pdf->Cell($w[0], $h, $header[0], '1', 0, 'C', 1);
                $pdf->Cell($w[1], $h, $header[1], '1', 0, 'C', 1);
                $pdf->Cell($w[2], $h, $header[2], '1', 0, 'C', 1, '', 1);
                $pdf->Cell($w[3], $h/2, 'Fecha de', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'B');
                $pdf->Cell($w[3], $h/2, 'Emisión', 'RBL', 0, 'C', 1, '', 1, false, 'T', 'T');
                $pdf->SetY($pdf->GetY()-($h/2), false);
                $pdf->Cell($w[4], $h/2, 'Fecha de', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'B');
                $pdf->Cell($w[4], $h/2, 'Vencimiento', 'RBL', 0, 'C', 1, '', 1, false, 'T', 'T');
                $pdf->SetY($pdf->GetY()-$h/2, false);
                $pdf->Cell($w[5]+$w[6]+$w[7], (3*$h)/(4*2), 'Comprobante de', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'B');
                $pdf->Cell($w[5]+$w[6]+$w[7], (3*$h)/(4*2), 'pago o documento', 'RBL', 2, 'C', 1, '', 1, false, 'T', 'T');
                $pdf->Cell($w[5], $h/4, $header[5], '1', 0, 'C', 1);
                $pdf->Cell($w[6], $h/4, $header[6], '1', 0, 'C', 1);
                $pdf->Cell($w[7], $h/4, $header[7], '1', 0, 'C', 1);
                $pdf->SetY($pdf->GetY()-(3*$h)/(4), false);
                $pdf->Cell($w[8], $h/2, 'Nro del', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'B');
                $pdf->Cell($w[8], $h/2, 'comprobante', 'RBL', 0, 'C', 1, '', 1, false, 'T', 'T');
                $pdf->SetY($pdf->GetY()-$h/2, false);
                // $pdf->Cell($w[8], $h, $header[8], '1', 0, 'C', 1);
                $pdf->Cell($w[9]+$w[10]+$w[11], (3*$h)/(4*2), 'Información del Proveedor', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[9]+$w[10], (3*$h)/(4*2), 'Documento', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[9], $h/4, $header[9], '1', 0, 'C', 1);
                $pdf->Cell($w[10], $h/4, $header[10], '1', 0, 'C', 1);

                $pdf->SetY($pdf->GetY()-(3*$h)/(4*2), false);
                $pdf->Cell($w[11], (5*$h)/(4*2), 'Apellidos y Nombres o razón social', 'RTL', 0, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->SetY($pdf->GetY()-(3*$h)/(4*2), false);
                
                $pdf->Cell($w[12]+$w[13], 3, ' ', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[12]+$w[13], 2, 'Aquisiciones ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[12]+$w[13], 2, 'gravadas destinadas ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[12]+$w[13], 2, 'a operaciones ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[12]+$w[13], 2, 'gravadas y/o ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[12]+$w[13], 2, 'de exportación ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[12]+$w[13], 2, ' ', 'RBL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[12], $h/4, $header[12], '1', 0, 'C', 1);
                $pdf->Cell($w[13], $h/4, $header[13], '1', 0, 'C', 1);
                $pdf->SetY($pdf->GetY()-(3*$h)/(4), false);

                // $pdf->Cell($w[12]+$w[13], 1, '', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[14]+$w[15], 3, 'Adquisiciones', 'RL', 2, 'C', 1, '', 1, false, 'T', 'B');
                $pdf->Cell($w[14]+$w[15], 2, 'gravadas destinadas ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[14]+$w[15], 2, 'a operaciones ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[14]+$w[15], 2, 'gravadas y/o ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[14]+$w[15], 2, 'de exportación ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[14]+$w[15], 2, 'y a operaciones', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[14]+$w[15], 2, ' no gravadas ', 'RBL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[14], $h/4, $header[14], '1', 0, 'C', 1);
                $pdf->Cell($w[15], $h/4, $header[15], '1', 0, 'C', 1);
                $pdf->SetY($pdf->GetY()-(3*$h)/(4), false);

                $pdf->Cell($w[16]+$w[17], 3, ' ', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[16]+$w[17], 2, 'Aquisiciones ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[16]+$w[17], 2, 'gravadas ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[16]+$w[17], 2, 'destinadas ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[16]+$w[17], 2, 'a operaciones ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[16]+$w[17], 2, 'no gravadas', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[16]+$w[17], 2, ' ', 'RBL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[16], $h/4, $header[16], '1', 0, 'C', 1);
                $pdf->Cell($w[17], $h/4, $header[17], '1', 0, 'C', 1);
                $pdf->SetY($pdf->GetY()-(3*$h)/(4), false);

                $pdf->Cell($w[18], 4, ' ', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[18], 2, 'Valor', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[18], 2, 'de las', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[18], 2, 'adquisi-', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[18], 2, 'ciones', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[18], 2, 'no', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[18], 2, 'gravadas', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[18], 4, ' ', 'RBL', 0, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->SetY($pdf->GetY()-16, false);

                $pdf->Cell($w[19], $h, $header[19], '1', 0, 'C', 1);

                $pdf->Cell($w[20], 6, ' ', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[20], 2, 'Otros', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[20], 2, 'tributos', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[20], 2, 'y', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[20], 2, 'cargos', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[20], 6, ' ', 'RBL', 0, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->SetY($pdf->GetY()-14, false);

                $pdf->Cell($w[21], 8, ' ', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[21], 2, 'Importe', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[21], 2, 'Total', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[21], 8, ' ', 'RBL', 0, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->SetY($pdf->GetY()-12, false);
            
                $pdf->Cell($w[22], 5, ' ', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[22], 2, 'Nro de', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[22], 2, 'Compro-', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[22], 2, 'bante', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[22], 2, 'de pago', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[22], 2, 'emitido', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[22], 5, ' ', 'RBL', 0, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->SetY($pdf->GetY()-15, false);
            
                $pdf->Cell($w[23]+$w[24], 5, ' ', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[23]+$w[24], 2, 'Constancia', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[23]+$w[24], 2, 'de depósito de', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[23]+$w[24], 2, 'detracción', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[23]+$w[24], 4, ' ', 'RBL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[23], $h/4, $header[23], '1', 0, 'C', 1);
                $pdf->Cell($w[24], $h/4, $header[24], '1', 0, 'C', 1);
                $pdf->SetY($pdf->GetY()-(3*$h)/(4), false);

                $pdf->Cell($w[25], 8, ' ', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[25], 2, 'Tipo de', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[25], 2, 'Cambio', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[25], 8, ' ', 'RBL', 0, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->SetY($pdf->GetY()-12, false);

                $pdf->Cell($w[26]+$w[27]+$w[28]+$w[29], 3, ' ', 'RTL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[26]+$w[27]+$w[28]+$w[29], 2, 'Referencia del', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[26]+$w[27]+$w[28]+$w[29], 2, 'comprobante de ', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[26]+$w[27]+$w[28]+$w[29], 2, 'pago o', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[26]+$w[27]+$w[28]+$w[29], 2, 'documento original', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[26]+$w[27]+$w[28]+$w[29], 2, 'que se modifica', 'RL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[26]+$w[27]+$w[28]+$w[29], 2, ' ', 'RBL', 2, 'C', 1, '', 1, false, 'T', 'M');
                $pdf->Cell($w[26], $h/4, $header[26], '1', 0, 'C', 1);
                $pdf->Cell($w[27], $h/4, $header[27], '1', 0, 'C', 1);
                $pdf->Cell($w[28], $h/4, $header[28], '1', 0, 'C', 1);
                $pdf->Cell($w[29], $h/4, $header[29], '1', 0, 'C', 1);
                $pdf->SetY($pdf->GetY()-(3*$h)/(4), false);

                $pdf->Cell($w[30], $h, $header[30], '1', 0, 'C', 1, '', 1, false, 'T', 'M');
                
            // }
            $pdf->Ln();
            // if($params['debe'] <> 0 || $params['haber'] <> 0 ){
            //     $pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4], $h, 'Vienen', 'B', 0, 'R', 0, '', 1);
            //     $pdf->Cell($w[5], $h, number_format($params['debe'], 2, '.', ','), 'LRB', 0, 'R', 0);
            //     $pdf->Cell($w[6], $h, number_format($params['haber'], 2, '.', ','), 'LRB', 0, 'R', 0);
            //     $pdf->Ln();
            // }

        });
    }

}
