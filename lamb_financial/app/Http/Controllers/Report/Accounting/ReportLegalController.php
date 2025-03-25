<?php

/**
 * Created by PhpStorm.
 * User: alexander.llacho
 * Date: 25/05/2017
 * Time: 4:12 PM
 */

namespace App\Http\Controllers\Report\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Data\ReportData;
use App\Http\Data\financialReportData;
use App\Http\Data\Report\AccountingLegalData;
use App\Http\Data\SetupData;
use App\Http\Data\Accounting\Setup\AccountingData;
use App\Http\Data\Purchases\PurchasesData;
use App\LambUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use PDF;
use DOMPDF;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
use TCPDF;

class ReportLegalController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function test0()
    {
        $jResponse = [
            'success' => false,
            'message' => 'no register'
        ];
        $results = LambUsuario::select('ID_PERSONA', 'LOGIN', 'CONTRASENHA')->GET();
        $count = count($results);
        if ($results) {
            $jResponse['success'] = true;
            $jResponse['message'] = 'OK';
            $jResponse['data'] = ['total_count' => $count, 'items' => $results->toArray()];
        }
        return response()->json($jResponse);
    }

    public static function test($empresa, $entidad, $anho, $mes)
    {
        $mes_data = AccountingData::getMonthById($mes);
        $mes_nombre = $mes_data->nombre;
        $list_razon_social = SetupData::enterpriseByIdEnterprise($empresa);

        $empresa_rs = '';
        $empresa_ruc = '';
        foreach ($list_razon_social as $item) {
            $empresa_rs = $item->nombre_legal;
            $empresa_ruc = $item->ruc;
        }

        $pdf = new CustomPDF();
        $params = [
            'debe' => 0,
            'haber' => 0,
            'ultimo' => 'N',
            'empresa_rs' => $empresa_rs,
            'empresa_ruc' => $empresa_ruc,
            'anho' => $anho,
            'mes_nombre' => $mes_nombre,
        ];
        CustomPDF::CustomHeader0($params);
        $pdf::SetTitle('FORMATO 5.1: LIBRO DIARIO');

        // set margins
        $pdf::SetMargins(PDF_MARGIN_LEFT, 19.1, PDF_MARGIN_RIGHT, 5);
        $pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf::SetFooterMargin(13.35);

        // set auto page breaks
        $pdf::SetAutoPageBreak(TRUE, 12);
        $pdf::AddPage();
        // $pdf::writeHTML($html, true, false, true, false, '');
        // $header = array(
        //     "#",
        //     "Cuenta",
        //     "Sub Cuenta",
        //     "Nombre de la Cuenta",
        //     "Glosa",
        //     "Débito",
        //     "Crédito",
        // );
        $data = AccountingLegalData::libro5_1($empresa, $entidad, $anho, $mes);
        $pdf::SetFillColor(224, 235, 255);
        $pdf::SetTextColor(0);
        // $pdf::SetDrawColor(128, 0, 0);
        $pdf::SetLineWidth(0.1);
        $pdf::SetFont('', 'B', 4.5);

        $w = array(7, 10, 10, 35, 100, 13, 13);
        $h = 1.8;

        $pdf::SetFillColor(224, 235, 255);
        $pdf::SetTextColor(0);
        $pdf::SetFont('', '', 4.5);
        // Data
        $fill = 0;
        $pdf::Ln();
        $pdf::Ln();
        $sub_total_debe = 0;
        $sub_total_haber = 0;
        foreach ($data as $row) {
            $pdf::Cell($w[0], $h, $row->num_aasi, 'LB', 0, 'L', $fill);
            $pdf::Cell($w[1], $h, $row->id_cuentaempresarial, 'B', 0, 'L', $fill);
            $pdf::Cell($w[2], $h, $row->id_ctacte, 'B', 0, 'L', $fill);
            $pdf::Cell($w[3], $h, $row->nombre, 'B', 0, 'L', $fill, '', 1);
            $pdf::Cell($w[4], $h, $row->comentario, 'B', 0, 'L', $fill, '', 1);
            $pdf::Cell($w[5], $h, number_format($row->debe, 2, '.', ','), 'LRB', 0, 'R', $fill);
            $pdf::Cell($w[6], $h, number_format($row->haber, 2, '.', ','), 'LRB', 0, 'R', $fill);
            $pdf::Ln();
            // $fill=!$fill;
            if ($row->orden == 2) {
                $sub_total_debe += $row->debe;
                $sub_total_haber += $row->haber;
            }
            $params = [
                'debe' => $sub_total_debe,
                'haber' => $sub_total_haber,
                'ultimo' => 'N',
                'empresa_rs' => $empresa_rs,
                'empresa_ruc' => $empresa_ruc,
                'anho' => $anho,
                'mes_nombre' => $mes_nombre,
            ];
            CustomPDF::CustomHeader0($params);
            CustomPDF::CustomFooterParams($params);
        }
        $pdf::SetFont('', 'B', 5);
        $pdf::Cell($w[0] + $w[1] + $w[2] + $w[3] + $w[4], $h, 'Total Mes   ', 'LB', 0, 'R', $fill);
        $pdf::Cell($w[5], $h, number_format($sub_total_debe, 2, '.', ','), 'LRB', 0, 'R', $fill);
        $pdf::Cell($w[6], $h, number_format($sub_total_haber, 2, '.', ','), 'LRB', 0, 'R', $fill);
        $pdf::Ln();
        // $fill=!$fill;
        $params = [
            'debe' => $sub_total_debe,
            'haber' => $sub_total_haber,
            'ultimo' => 'S',
            'empresa_rs' => $empresa_rs,
            'empresa_ruc' => $empresa_ruc,
            'anho' => $anho,
            'mes_nombre' => $mes_nombre,
        ];
        CustomPDF::CustomHeader0($params);
        CustomPDF::CustomFooterParams($params);
        $pdf::Cell(array_sum($w), 0, '', 'T');
        $pdf::Output('hello_world.pdf');
    }


    public static function libro_mayor($empresa, $entidad, $anho, $mes)
    {
        $pdf = new CustomPDF();

        $mes_data = AccountingData::getMonthById($mes);
        $mes_nombre = $mes_data->nombre;
        $list_razon_social = SetupData::enterpriseByIdEnterprise($empresa);

        $empresa_rs = '';
        $empresa_ruc = '';
        foreach ($list_razon_social as $item) {
            $empresa_rs = $item->nombre_legal;
            $empresa_ruc = $item->ruc;
        }

        $params = [
            'debe' => 0,
            'haber' => 0,
            'saldo' => 0,
            'ultimo' => 'N',
            'primero' => 'S',
            'empresa_rs' => $empresa_rs,
            'empresa_ruc' => $empresa_ruc,
            'anho' => $anho,
            'mes_nombre' => $mes_nombre,
        ];
        CustomPDF::CustomHeaderLibroMayor($params);
        $pdf::SetTitle('Libro Mayor');

        // set margins
        $pdf::SetMargins(PDF_MARGIN_LEFT, 24, PDF_MARGIN_RIGHT, 5);
        $pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf::SetFooterMargin(13);

        // set auto page breaks
        $pdf::SetAutoPageBreak(TRUE, 12);
        $pdf::AddPage();

        $header = array(
            "#",
            "Cuenta",
            "Sub Cuenta",
            "Nombre de la Cuenta",
            "Glosa",
            "Débito",
            "Crédito",
        );

        $data = AccountingLegalData::libro_mayor($empresa, $entidad, $anho, $mes);

        $pdf::SetFillColor(224, 235, 255);
        $pdf::SetTextColor(0);
        $pdf::SetLineWidth(0.3);
        $pdf::SetFont('', 'B', 5);

        $w = array(7, 10, 10, 122, 13, 13, 13, 10);
        $h = 4;

        $pdf::SetFillColor(224, 235, 255);
        $pdf::SetTextColor(0);
        $pdf::SetFont('', '', 5);
        $fill = 0;
        $h = 4;
        $pdf::Ln();
        $pdf::Ln();
        $sub_total_debe = 0;
        $sub_total_haber = 0;
        $sub_total_saldo = 0;

        foreach ($data as $row) {
            $pdf::Cell($w[0], $h, '-', 'LB', 0, 'L', $fill);  // Añadí un campo para la primera columna
            $pdf::Cell($w[1], $h, $row->fecha, 'B', 0, 'L', $fill);
            $pdf::Cell($w[2], $h, $row->lote, 'B', 0, 'L', $fill);
            $pdf::Cell($w[3], $h, $row->comentario, 'B', 0, 'L', $fill);

            // Comprobación de índice 4
            $pdf::Cell(isset($w[4]) ? $w[4] : 10, $h, number_format($row->debe, 2, '.', ','), 'LRB', 0, 'R', $fill);
            // Comprobación de índice 5
            $pdf::Cell(isset($w[5]) ? $w[5] : 10, $h, number_format($row->haber, 2, '.', ','), 'LRB', 0, 'R', $fill);
            // Comprobación de índice 6
            $pdf::Cell(isset($w[6]) ? $w[6] : 10, $h, number_format($row->saldo, 2, '.', ','), 'LRB', 0, 'R', $fill);
            $pdf::Ln();

            if ($row->orden == 2) {
                $sub_total_debe += $row->debe;
                $sub_total_haber += $row->haber;
                $sub_total_saldo += $row->saldo;
            }

            $params = [
                'debe' => $row->debe,
                'haber' => $row->haber,
                'saldo' => $row->saldo,
                'ultimo' => 'N',
                'primero' => 'N',
                'empresa_rs' => $empresa_rs,
                'empresa_ruc' => $empresa_ruc,
                'anho' => $anho,
                'mes_nombre' => $mes_nombre,
            ];
            CustomPDF::CustomHeaderLibroMayor($params);
            CustomPDF::CustomFooterParamsLibroMayor($params);
        }

        $pdf::SetFont('', 'B', 5);
        $total_width = 0;
        for ($i = 0; $i < 4; $i++) {
            $total_width += isset($w[$i]) ? $w[$i] : 10; // Valor por defecto
        }
        $pdf::Cell($total_width, $h, 'Total Mes   ', 'LB', 0, 'R', $fill);

        // Comprobación de índice 5
        $pdf::Cell(isset($w[5]) ? $w[5] : 10, $h, number_format($sub_total_debe, 2, '.', ','), 'LRB', 0, 'R', $fill);
        // Comprobación de índice 6
        $pdf::Cell(isset($w[6]) ? $w[6] : 10, $h, number_format($sub_total_haber, 2, '.', ','), 'LRB', 0, 'R', $fill);
        // Comprobación de índice 6
        $pdf::Cell(isset($w[6]) ? $w[6] : 10, $h, number_format($sub_total_debe - $sub_total_haber, 2, '.', ','), 'LRB', 0, 'R', $fill);
        $pdf::Ln();

        $params = [
            'debe' => $sub_total_debe,
            'haber' => $sub_total_haber,
            'saldo' => $sub_total_debe - $sub_total_haber,
            'ultimo' => 'S',
            'primero' => 'N',
            'empresa_rs' => $empresa_rs,
            'empresa_ruc' => $empresa_ruc,
            'anho' => $anho,
            'mes_nombre' => $mes_nombre,
        ];
        CustomPDF::CustomHeaderLibroMayor($params);
        CustomPDF::CustomFooterParamsLibroMayor($params);

        $pdf::Cell(array_sum($w), 0, '', 'T');
        $pdf::Output('hello_world.pdf');
    }


    public static function generarPdfUpn(
        $id_empresa,
        $id_entidad,
        $id_anho,
        $id_mes,
        $empresa_rs,
        $empresa_ruc,
        $mes_nombre,
        $entidad_anterior_pag_final,
        $total_paginas,
        $es_la_ultima_entidad,
        $es_la_primera_entidad,
        $anterior_d_total_mes_van,
        $anterior_c_total_mes_van,
        $d_total_mes_van,
        $c_total_mes_van,
        $d_total_mes,
        $c_total_mes
        // $d_total_acumulado_van,
        // $c_total_acumulado_van,
        // $anterior_d_total_acumulado_van,
        // $anterior_c_total_acumulado_van
    ) {
        // public static function testUPN()
        // {
        //     $id_empresa = 207;
        //     $id_entidad = 17211;
        //     $id_anho = 2021;
        //     $id_mes = 1;

        // $dataAcumuladoEntidad = AccountingLegalData::libro5_1_last_month_acumulado($id_empresa, $id_entidad, $id_anho, $id_mes);

        $pdf = new LibroDiarioUpnPDF('P', 'mm', 'A4');
        $params_header = [
            'empresa_rs' => $empresa_rs,
            'empresa_ruc' => $empresa_ruc,
            'anho' => $id_anho,
            'mes_nombre' => $mes_nombre,
        ];
        $pdf->Header($params_header);
        $pdf->SetTitle('FORMATO 5.1: LIBRO DIARIO');

        // Configuración de margen de la hoja principal
        $pdf->SetMargins(PDF_MARGIN_LEFT, 31.3, PDF_MARGIN_RIGHT);

        // Configuración de margen cabecera
        $pdf->SetHeaderMargin(9.2);

        // Configuración de margen de pie
        $pdf->SetFooterMargin(1);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 13);
        $pdf->AddPage();
        $data = AccountingLegalData::libro5_1_upn($id_empresa, $id_entidad, $id_anho, $id_mes);
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetLineWidth(0.1);
        $pdf->SetFont('', '', 6);

        $w = array(12, 12, 68, 12, 18, 15, 14, 42, 17, 17);
        $h = 1.8;
        // Data
        $fill = 0;
        $pdf->Ln();
        $pdf->Ln();
        $sub_total_debe = 0;
        $sub_total_haber = 0;

        $filasPorPagina = 0;
        if ($es_la_primera_entidad) {
            if ($id_mes > 1) {
                // Aqui jalamos del mes anterior
                $pdf->Cell($w[0] + $w[2]
                    // + $w[3] + $w[4] + $w[5] 
                    + $w[6] + $w[6] + $w[7], $h, 'VIENEN DEL MES ANTERIOR: ', 'B', 0, 'R', 0, '', 1);
                // $pdf->Cell($w[0] + $w[1] + $w[2] + $w[3] + $w[4] + $w[5] + $w[6] + $w[7], $h, 'Vienen', 'B', 0, 'R', 0, '', 1);
                $pdf->Cell($w[8], $h, number_format($anterior_d_total_mes_van, 2, '.', ','), 'B', 0, 'R', 0);
                $pdf->Cell($w[9], $h, number_format($anterior_c_total_mes_van, 2, '.', ','), 'B', 0, 'R', 0);
                $pdf->Ln();
                $filasPorPagina = $filasPorPagina + 1;
            }
        } else {
            $pdf->Cell($w[0] + $w[2]
                // + $w[3] + $w[4] + $w[5] 
                + $w[6] + $w[6] + $w[7], $h, 'VIENEN DE OTRA ENTIDAD: ', 'B', 0, 'R', 0, '', 1);
            // $pdf->Cell($w[0] + $w[1] + $w[2] + $w[3] + $w[4] + $w[5] + $w[6] + $w[7], $h, 'Vienen', 'B', 0, 'R', 0, '', 1);
            $pdf->Cell($w[8], $h, number_format($anterior_d_total_mes_van, 2, '.', ','), 'B', 0, 'R', 0);
            $pdf->Cell($w[9], $h, number_format($anterior_c_total_mes_van, 2, '.', ','), 'B', 0, 'R', 0);
            $pdf->Ln();
            $filasPorPagina = $filasPorPagina + 1;
        }

        foreach ($data as $key => $row) {
            if ($row->orden == 1) { // Nombre de lote
                $pdf->SetFont('', 'B', 6);
                $pdf->SetLineWidth(0.3);
                $pdf->Cell($w[0], $h, $row->num_aasi, 'B', 0, 'C', $fill);
                $pdf->Cell($w[2], $h, $row->comentario, 'B', 0, 'L', $fill, '', 1);
                $pdf->Cell($w[6], $h, $row->id_cuentaaasi, 'B', 0, 'C', $fill);
                $pdf->Cell($w[6], $h, $row->id_ctacte, 'B', 0, 'C', $fill);
                $pdf->Cell($w[7], $h, $row->nombre, 'B', 0, 'L', $fill);
                $pdf->Cell($w[8], $h, '', 'B', 0, 'R', $fill);
                $pdf->Cell($w[9], $h, '', 'B', 0, 'R', $fill);
            } else if ($row->orden == 2) { // Cuerpo de lote
                $pdf->SetFont('', '', 6);
                $pdf->SetLineWidth(0.1);
                $pdf->Cell($w[0], $h, $row->num_aasi, 'LB', 0, 'C', $fill);
                $pdf->Cell($w[2], $h, $row->comentario, 'B', 0, 'L', $fill, '', 1);
                $valueCuenta = $row->id_cuentaempresarial == '' ? $row->id_cuentaaasi : $row->id_cuentaempresarial;
                if ($row->id_cuentaempresarial == '') {
                    $pdf->SetTextColor(255, 0, 0); // Cambiar color a rojo
                }
                $pdf->Cell($w[6], $h, $valueCuenta, 'B', 0, 'L', $fill);
                $pdf->SetTextColor(0, 0, 0); // Restablecer color a negro
                $pdf->Cell($w[6], $h, $row->id_ctacte, 'B', 0, 'L', $fill, '', 1);
                $pdf->Cell($w[7], $h, $row->nombre, 'B', 0, 'L', $fill, '', 1);
                $pdf->Cell($w[8], $h, number_format($row->debe, 2, '.', ','), 'LRB', 0, 'R', $fill);
                $pdf->Cell($w[9], $h, number_format($row->haber, 2, '.', ','), 'RB', 0, 'R', $fill);
            } else { // total de lote
                $pdf->SetFont('', 'B', 6);
                $pdf->SetLineWidth(0.3);
                $pdf->Cell($w[0], $h, $row->num_aasi, 'TB', 0, 'C', $fill);
                $pdf->Cell($w[2], $h, $row->comentario, 'TB', 0, 'L', $fill, '', 1);
                $pdf->Cell($w[6], $h, $row->id_cuentaaasi, 'TB', 0, 'C', $fill);
                $pdf->Cell($w[6], $h, $row->id_ctacte, 'TB', 0, 'C', $fill);
                $pdf->Cell($w[7], $h, 'TOTAL LOTE: ', 'TB', 0, 'R', $fill);
                $pdf->Cell($w[8], $h, number_format($row->debe, 2, '.', ','), 'TB', 0, 'R', $fill);
                $pdf->Cell($w[9], $h, number_format($row->haber, 2, '.', ','), 'TB', 0, 'R', $fill);
            }

            if ($row->orden == 2) {
                $sub_total_debe += $row->debe;
                $sub_total_haber += $row->haber;
            }

            $pdf->Ln();
            $filasPorPagina = $filasPorPagina + 1;
            if ($filasPorPagina == 90) {

                $pdf->Cell($w[0] + $w[2]
                    // + $w[3] + $w[4] + $w[5] 
                    + $w[6] + $w[6] + $w[7], $h, 'VAN:  ', 'TB', 0, 'R', 0, '', 1);
                $pdf->Cell($w[8], $h, number_format($sub_total_debe, 2, '.', ','), 'BT', 0, 'R', 0);
                $pdf->Cell($w[9], $h, number_format($sub_total_haber, 2, '.', ','), 'BT', 0, 'R', 0);
                $pdf->Ln();

                $pdf->AddPage();
                $filasPorPagina = 0;
                $h_dh = 1.8;
                $pdf->SetLineWidth(0.3);

                $pdf->Cell($w[0] + $w[2]
                    // + $w[3] + $w[4] + $w[5] 
                    + $w[6] + $w[6] + $w[7], $h_dh, 'VIENEN: ', 'B', 0, 'R', 0, '', 1);
                // $pdf->Cell($w[0] + $w[1] + $w[2] + $w[3] + $w[4] + $w[5] + $w[6] + $w[7], $h_dh, 'Vienen', 'B', 0, 'R', 0, '', 1);
                $pdf->Cell($w[8], $h_dh, number_format($sub_total_debe, 2, '.', ','), 'B', 0, 'R', 0);
                $pdf->Cell($w[9], $h_dh, number_format($sub_total_haber, 2, '.', ','), 'B', 0, 'R', 0);
                $pdf->Ln();
                $filasPorPagina = $filasPorPagina + 1;
            }
        }
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('', 'B', 6);

        $pdf->Cell($w[0] + $w[2]
            // + $w[3] + $w[4] + $w[5] 
            + $w[6] + $w[6] + $w[7], $h, 'TOTAL MOVIMIENTOS DEL MES (ENTIDAD): ', 'BT', 0, 'R', $fill);
        $pdf->Cell($w[8], $h, number_format($d_total_mes, 2, '.', ','), 'BT', 0, 'R', $fill);
        $pdf->Cell($w[9], $h, number_format($c_total_mes, 2, '.', ','), 'BT', 0, 'R', $fill);
        $pdf->Ln();

        $label = $es_la_ultima_entidad ? 'TOTAL MOVIMIENTOS AL MES (TOTAL): ' : '(VAN): ';
        // $label = $es_la_ultima_entidad ? 'TOTAL MOVIMIENTOS DEL MES: ' : 'TOTAL MOVIMIENTOS ACUMULADOS DEL MES (VAN): ';
        $pdf->Cell($w[0] + $w[2]
            // + $w[3] + $w[4] + $w[5] 
            + $w[6] + $w[6] + $w[7], $h, $label, 'BT', 0, 'R', $fill);
        $pdf->Cell($w[8], $h, number_format($d_total_mes_van, 2, '.', ','), 'BT', 0, 'R', $fill);
        $pdf->Cell($w[9], $h, number_format($c_total_mes_van, 2, '.', ','), 'BT', 0, 'R', $fill);
        $pdf->Ln();

        // $pdf->Cell($w[0] + $w[2]
        //     // + $w[3] + $w[4] + $w[5]
        //     + $w[6] + $w[6] + $w[7], $h, 'TOTAL MOVIMIENTOS ACUMULADOS AL MES (ENTIDAD): ', 'BT', 0, 'R', $fill);
        // $pdf->Cell($w[8], $h, number_format($dataAcumulado->debe, 2, '.', ','), 'BT', 0, 'R', $fill);
        // $pdf->Cell($w[9], $h, number_format($dataAcumulado->haber, 2, '.', ','), 'BT', 0, 'R', $fill);
        // $pdf->Ln();

        // if ($es_la_ultima_entidad) {
        //     $pdf->Cell($w[0] + $w[2]
        //         // + $w[3] + $w[4] + $w[5] 
        //         + $w[6] + $w[6] + $w[7], $h, 'TOTAL MOVIMIENTOS AL MES (TOTAL): ', 'BT', 0, 'R', $fill);
        //     $pdf->Cell($w[8], $h, number_format($d_total_acumulado_van, 2, '.', ','), 'BT', 0, 'R', $fill);
        //     $pdf->Cell($w[9], $h, number_format($c_total_acumulado_van, 2, '.', ','), 'BT', 0, 'R', $fill);
        //     $pdf->Ln();
        // }

        $totalPages = $pdf->getNumPages();
        $params_footer = [
            'anho' => $id_anho,
            'mes_nombre' => $mes_nombre,
            'entidad_anterior_pag_final' => $entidad_anterior_pag_final,
            'total_paginas' => $total_paginas,
        ];
        for ($pageNo = 1; $pageNo <= $totalPages; $pageNo++) {
            $pdf->setPage($pageNo);
            $pdf->SetY(281);
            $pdf->Footer($params_footer); // Forzar actualización del pie
        }
        // $pdf->Output('LIBRO DIARIO.pdf', 'O');

        $item = DB::table('eliseo.conta_diario_paginacion')->where('id_empresa', $id_empresa)
            ->where('id_entidad', $id_entidad)
            ->where('id_anho', $id_anho)
            ->where('id_mes', $id_mes)
            ->first();
        if ($item) {
            DB::table('eliseo.conta_diario_paginacion')->where('id_empresa', $id_empresa)
                ->where('id_entidad', $id_entidad)
                ->where('id_anho', $id_anho)
                ->where('id_mes', $id_mes)
                ->update([
                    'paginas' => $totalPages,
                    'd_total_mes' => $sub_total_debe,
                    'c_total_mes' => $sub_total_haber,
                    // 'd_total_acumulado' => $dataAcumuladoEntidad->debe,
                    // 'c_total_acumulado' => $dataAcumuladoEntidad->haber,
                ]);
        } else {
            DB::table('eliseo.conta_diario_paginacion')->insert([
                'id_empresa' => $id_empresa,
                'id_entidad' => $id_entidad,
                'id_anho' => $id_anho,
                'id_mes' => $id_mes,
                'paginas' => $totalPages,
                'd_total_mes' => $sub_total_debe,
                'c_total_mes' => $sub_total_haber,
                // 'd_total_acumulado' => $dataAcumuladoEntidad->debe,
                // 'c_total_acumulado' => $dataAcumuladoEntidad->haber,
            ]);
        }
        unset($data);
        unset($dataAcumulado);
        // unset($pdf);
        return $pdf;
    }

    // public static function libro_mayor_upn()
    // {
    public static function libro_mayor_upn($id_empresa, $id_entidad, $id_anho, $id_mes)
    {
        // $empresa = 207;
        // $entidad = 17112;
        // $anho = 2020;
        // $mes = 1;

        $pdf = new LibroMayorUpnPDF();

        $mes_data = AccountingData::getMonthById($id_mes);
        $mes_nombre = $mes_data->nombre;
        $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);

        $empresa_rs = '';
        $empresa_ruc = '';
        foreach ($list_razon_social as $item) {
            $empresa_rs = $item->nombre_legal;
            $empresa_ruc = $item->ruc;
        }

        $params = [
            'debe' => 0,
            'haber' => 0,
            'saldo' => 0,
            'ultimo' => 'N',
            'primero' => 'S',
            'empresa_rs' => $empresa_rs,
            'empresa_ruc' => $empresa_ruc,
            'anho' => $id_anho,
            'mes_nombre' => $mes_nombre,
            'rowAccount' => null,
        ];
        LibroMayorUpnPDF::CustomHeaderLibroMayorUpn($params);
        $pdf::SetTitle('Libro Mayor UPN');

        // set margins
        // $pdf::SetMargins(PDF_MARGIN_LEFT, 24, PDF_MARGIN_RIGHT, 5);
        $pdf::SetMargins(PDF_MARGIN_LEFT, 34, PDF_MARGIN_RIGHT);
        // $pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
        // $pdf::SetHeaderMargin(4.2);
        $pdf::SetHeaderMargin(12);
        // $pdf::SetFooterMargin(13);
        $pdf::SetFooterMargin(14);

        // set auto page breaks
        $pdf::SetAutoPageBreak(TRUE, 13);
        $pdf::AddPage();

        // Llamada a la función que obtiene los datos para el PDF
        $data = AccountingLegalData::get_libro_mayor_upn($id_empresa, $id_entidad, $id_anho, $id_mes);
        $pdf::SetFillColor(224, 235, 255);
        $pdf::SetTextColor(0);
        $pdf::SetLineWidth(0.1);
        // $pdf::SetFont('', 'B', 6);
        $pdf::SetFont('', '', 6);

        // $w= array(20, 35, 90, 15,15);
        $w = array(15, 25, 80, 20, 20, 20); // Aumenté el ancho de las últimas dos columnas
        $h = 1.8;
        // $h = 4;
        // $pdf::SetFillColor(224, 235, 255);
        // $pdf::SetTextColor(0);
        // $pdf::SetFont('', '', 5);
        $fill = 0;
        $pdf::Ln();
        $pdf::Ln();
        // $saldo_mes_anterior_debe = 0;
        // $saldo_mes_anterior_haber = 0;
        $sub_total_debe = 0;
        $sub_total_haber = 0;
        $sub_total_saldo = 0;

        $rowAccount = null;
        foreach ($data as $key => $row) {
            if ($row->orden == 0) {
                $pdf::SetLineWidth(0.3);
                $pdf::SetFont('', 'B', 6); // 'B' para negrita
                $rowAccount = $row;
                $sub_total_debe = 0;
                $sub_total_haber = 0;
                $sub_total_saldo = 0;

                $pdf::Cell($w[0], $h, 'CUENTA ', 'B', 0, 'L', $fill);
                $pdf::SetTextColor(!empty($row->id_cuentaempresarial) ? 0 : 255, 0, 0); // Rojo para "(No tiene equivalencia)"
                $pdf::Cell($w[1], $h, !empty($row->id_cuentaempresarial) ? $row->id_cuentaempresarial : $row->id_cuentaaasi, 'B', 0, 'L', $fill);
                $pdf::Cell($w[2], $h, $row->comentario, 'B', 0, 'L', $fill);
                $pdf::SetTextColor(0, 0, 0); // Restablecer a negro para las demás celdas
                $pdf::Cell($w[3], $h, '', 'B', 0, 'R', $fill);
                $pdf::Cell($w[4], $h, '', 'B', 0, 'R', $fill);
                $pdf::Cell($w[5], $h, '', 'B', 0, 'R', $fill);
            } else if ($row->orden == 1) {
                $pdf::SetLineWidth(0.1);
                $pdf::SetFont('', '', 6); // 'B' para negrita
                $pdf::Cell($w[0], $h, '', 'LB', 0, 'L', $fill);
                $pdf::Cell($w[1], $h, '', 'B', 0, 'L', $fill);
                $pdf::Cell($w[2], $h, 'SALDO AL MES ANTERIOR: ', 'B', 0, 'R', $fill);
                $pdf::Cell($w[3], $h, number_format($row->debe, 2, '.', ','), 'LRB', 0, 'R', $fill);
                $pdf::Cell($w[4], $h, number_format($row->haber, 2, '.', ','), 'LRB', 0, 'R', $fill);
                $pdf::Cell($w[5], $h, number_format($row->saldo, 2, '.', ','), 'LRB', 0, 'R', $fill);
            } else if ($row->orden == 2) {
                $pdf::SetLineWidth(0.1);
                $pdf::SetFont('', '', 6); // 'B' para negrita
                $pdf::Cell($w[0], $h, $row->fecha, 'BL', 0, 'L', $fill);
                $pdf::Cell($w[1], $h, $row->lote, 'B', 0, 'L', $fill);
                $pdf::Cell($w[2], $h, $row->comentario, 'B', 0, 'L', $fill);
                $pdf::Cell($w[3], $h, number_format($row->debe, 2, '.', ','), 'LRB', 0, 'R', $fill);
                $pdf::Cell($w[4], $h, number_format($row->haber, 2, '.', ','), 'LRB', 0, 'R', $fill);
                $pdf::Cell($w[5], $h, number_format($row->saldo, 2, '.', ','), 'LRB', 0, 'R', $fill);
            } else if ($row->orden == 3) {
                $pdf::SetLineWidth(0.3);
                $pdf::SetFont('', 'B', 6); // 'B' para negrita
                $pdf::Cell($w[0], $h, '', 'BT', 0, 'L', $fill);
                $pdf::SetTextColor(!empty($row->id_cuentaempresarial) ? 0 : 255, 0, 0); // Rojo para "(No tiene equivalencia)"
                $pdf::Cell($w[1], $h, !empty($row->id_cuentaempresarial) ? $row->id_cuentaempresarial : $row->id_cuentaaasi, 'BT', 0, 'L', $fill);
                $pdf::Cell($w[2] / 2, $h, $row->comentario, 'BT', 0, 'L', $fill);
                $pdf::SetTextColor(0, 0, 0); // Restablecer a negro para las demás celdas
                $pdf::Cell($w[2] / 2, $h, 'SALDO DEL MES:', 'BT', 0, 'R', $fill);
                $pdf::Cell($w[3], $h, number_format($row->debe, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Cell($w[4], $h, number_format($row->haber, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Cell($w[5], $h, number_format($row->saldo, 2, '.', ','), 'BT', 0, 'R', $fill);
                // $pdf::Ln();
            } else if ($row->orden == 4) {

                $pdf::SetLineWidth(0.3);
                $pdf::SetFont('', 'B', 6); // 'B' para negrita
                $pdf::Cell($w[0], $h, '', 'BT', 0, 'L', $fill);
                $pdf::Cell($w[1], $h, '', 'BT', 0, 'L', $fill);
                $pdf::Cell($w[2], $h, 'SALDO AL MES:', 'BT', 0, 'R', $fill);
                $pdf::Cell($w[3], $h, number_format($row->debe, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Cell($w[4], $h, number_format($row->haber, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Cell($w[5], $h, number_format($row->saldo, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Ln();
                // $pdf::Ln();
            } else if ($row->orden == 5) {
                $pdf::SetLineWidth(0.3);
                $pdf::SetFont('', 'B', 6); // 'B' para negrita
                $pdf::Cell($w[0], $h, '', 'BT', 0, 'L', $fill);
                $pdf::Cell($w[1], $h, '', 'BT', 0, 'L', $fill);
                $pdf::Cell($w[2], $h, 'SALDO DE TODAS LA CUENTAS AL MES:', 'BT', 0, 'R', $fill);
                $pdf::Cell($w[3], $h, number_format($row->debe, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Cell($w[4], $h, number_format($row->haber, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Cell($w[5], $h, number_format($row->saldo, 2, '.', ','), 'BT', 0, 'R', $fill);
                // $pdf::Ln();
            }
            // Verificar si el comentario es 'Saldo mes anterior' para aplicar negrita
            // $isSaldoAnterior = (strpos($row->comentario, 'Saldo mes anterior') !== false);

            // Aplicar negrita y subrayado si es CUENTA o Saldo
            // $pdf::SetFont('', $boldUnderline ? 'BU' : ''); // 'BU' para Bold y Underline (negrita y subrayado)

            // Si tienes una sexta columna (saldo), ajusta como se indica
            // if (isset($w[5])) {
            //     // Aplicar negrita y subrayado para el saldo
            //     // $pdf::SetFont('', 'BU'); // 'BU' para Bold y Underline (negrita y subrayado)
            //     // $pdf::Cell($w[5], $h, number_format($row->saldo, 2, '.', ','), 'LRB', 0, 'R', $fill);
            //     // Restablecer la fuente a normal
            //     $pdf::SetFont('', '');
            // }
            $pdf::SetFont('', ''); // Restablecer el formato de fuente al predeterminado
            $pdf::Ln();

            // if ($row->orden == 0) {
            //     // Capturar el saldo del mes anterior
            //     $saldo_mes_anterior_debe = $row->debe;
            //     $saldo_mes_anterior_haber = $row->haber;

            //     $sub_total_debe += $row->debe;
            //     $sub_total_haber += $row->haber;
            //     $sub_total_saldo += $row->saldo;
            // } elseif ($row->orden == 2) {
            //     $sub_total_debe += $row->debe;
            //     $sub_total_haber += $row->haber;
            //     $sub_total_saldo += $row->saldo;
            // }

            // if (in_array($row->orden, [1, 2])) {
            if (in_array($row->orden, [2])) {
                // $sub_total_debe = $sub_total_debe + $row->debe;
                // $sub_total_haber = $sub_total_haber + $row->haber;
                // $sub_total_saldo = $sub_total_saldo + $row->saldo;
                $sub_total_debe += $row->debe;
                $sub_total_haber += $row->haber;
                $sub_total_saldo += $row->saldo;
            }
            $params = [
                'debe' => $sub_total_debe,
                'haber' => $sub_total_haber,
                'saldo' => $sub_total_saldo,
                'ultimo' => (($key + 1) == count($data)) ? 'S' : 'N',
                'primero' => 'N',
                'empresa_rs' => $empresa_rs,
                'empresa_ruc' => $empresa_ruc,
                'anho' => $id_anho,
                'mes_nombre' => $mes_nombre,
                'rowAccount' => $rowAccount,
            ];
            LibroMayorUpnPDF::CustomHeaderLibroMayorUpn($params);
            LibroMayorUpnPDF::CustomFooterParamsLibroMayorUpn($params);
        }

        // $total_acumulado_debe = $saldo_mes_anterior_debe + $sub_total_debe;
        // $total_acumulado_haber = $saldo_mes_anterior_haber + $sub_total_haber;

        // Calcular el ancho total de las primeras 3 columnas
        // $total_width = $w[0] + $w[1] + $w[2];

        // Posicionar "Total Mes" alineado a la derecha de las primeras 3 columnas
        // $pdf::Cell($total_width, $h, 'Total Movimientos del mes', 'LB', 0, 'R', $fill);

        // Añadir el total del Debe
        // $pdf::Cell($w[3], $h, number_format($sub_total_debe, 2, '.', ','), 'LRB', 0, 'R', $fill);

        // Añadir el total del Haber
        // $pdf::Cell($w[4], $h, number_format($sub_total_haber, 2, '.', ','), 'LRB', 0, 'R', $fill);

        // $pdf::Ln();
        // Posicionar "Total Mes" alineado a la derecha de las primeras 3 columnas
        // $pdf::Cell($total_width, $h, 'Total Movimientos Acumulados hasta la fecha ', 'LB', 0, 'R', $fill);


        // $pdf::Cell($w[3], $h, number_format($sub_total_debe, 2, '.', ','), 'LRB', 0, 'R', $fill);
        // $pdf::Cell($w[4], $h, number_format($sub_total_haber, 2, '.', ','), 'LRB', 0, 'R', $fill);
        // $pdf::Cell($w[3], $h, number_format($total_acumulado_debe, 2, '.', ','), 'LRB', 0, 'R', $fill);
        // $pdf::Cell($w[4], $h, number_format($total_acumulado_haber, 2, '.', ','), 'LRB', 0, 'R', $fill);

        // $pdf::Ln();

        // $params = [
        //     // 'debe' => $sub_total_debe,
        //     // 'haber' => $sub_total_haber,
        //     // 'saldo' => $sub_total_debe - $sub_total_haber,
        //     'debe' => $total_acumulado_debe,
        //     'haber' => $total_acumulado_haber,
        //     'saldo' => $total_acumulado_debe - $total_acumulado_haber,
        //     'ultimo' => 'S',
        //     'primero' => 'N',
        //     'empresa_rs' => $empresa_rs,
        //     'empresa_ruc' => $empresa_ruc,
        //     'anho' => $anho,
        //     'mes_nombre' => $mes_nombre,
        // ];
        // LibroMayorUpnPDF::CustomHeaderLibroMayorUpn($params);
        // LibroMayorUpnPDF::CustomFooterParamsLibroMayorUpn($params);

        // $pdf::Cell(array_sum($w), 0, '', 'T');
        $pdf::Output('hello_world.pdf');
    }


    public static function libro_mayor_upn_totalizado($id_empresa, $id_entidad, $id_anho, $id_mes)
    {
        // $empresa = 207;  
        // $entidad = 17112;
        // $anho = 2020;
        // $mes = 1;

        $pdf = new LibroMayorUpnPDF();

        $mes_data = AccountingData::getMonthById($id_mes);
        $mes_nombre = $mes_data->nombre;
        $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);

        $empresa_rs = '';
        $empresa_ruc = '';
        foreach ($list_razon_social as $item) {
            $empresa_rs = $item->nombre_legal;
            $empresa_ruc = $item->ruc;
        }

        $params = [
            'debe' => 0,
            'haber' => 0,
            'saldo' => 0,
            'ultimo' => 'N',
            'primero' => 'S',
            'empresa_rs' => $empresa_rs,
            'empresa_ruc' => $empresa_ruc,
            'anho' => $id_anho,
            'mes_nombre' => $mes_nombre,
            'rowAccount' => null,
        ];
        LibroMayorUpnPDF::CustomHeaderLibroMayorUpn($params);
        $pdf::SetTitle('Libro Mayor UPN');

        // set margins
        // $pdf::SetMargins(PDF_MARGIN_LEFT, 24, PDF_MARGIN_RIGHT, 5);
        $pdf::SetMargins(PDF_MARGIN_LEFT, 34, PDF_MARGIN_RIGHT);
        // $pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
        // $pdf::SetHeaderMargin(4.2);
        $pdf::SetHeaderMargin(12);
        // $pdf::SetFooterMargin(13);
        $pdf::SetFooterMargin(14);

        // set auto page breaks
        $pdf::SetAutoPageBreak(TRUE, 13);
        $pdf::AddPage();

        // Llamada a la función que obtiene los datos para el PDF
        $data = AccountingLegalData::get_libro_mayor_upn_totales($id_empresa, $id_entidad, $id_anho, $id_mes);
        $pdf::SetFillColor(224, 235, 255);
        $pdf::SetTextColor(0);
        $pdf::SetLineWidth(0.1);
        // $pdf::SetFont('', 'B', 6);
        $pdf::SetFont('', '', 6);

        // $w= array(20, 35, 90, 15,15);
        $w = array(15, 25, 80, 20, 20, 20); // Aumenté el ancho de las últimas dos columnas
        $h = 1.8;
        $fill = 0;
        $pdf::Ln();
        $pdf::Ln();
        $sub_total_debe = 0;
        $sub_total_haber = 0;
        $sub_total_saldo = 0;

        $rowAccount = null;
        foreach ($data as $key => $row) {
            if ($row->orden == 0) {
                $pdf::SetLineWidth(0.3);
                $pdf::SetFont('', 'B', 6); // 'B' para negrita
                $rowAccount = $row;
                $sub_total_debe = 0;
                $sub_total_haber = 0;
                $sub_total_saldo = 0;

                $pdf::Cell($w[0], $h, 'CUENTA ', 'B', 0, 'L', $fill);
                $pdf::SetTextColor(!empty($row->id_cuentaempresarial) ? 0 : 255, 0, 0); // Rojo para "(No tiene equivalencia)"
                $pdf::Cell($w[1], $h, !empty($row->id_cuentaempresarial) ? $row->id_cuentaempresarial : $row->id_cuentaaasi, 'B', 0, 'L', $fill);
                $pdf::Cell($w[2], $h, $row->comentario, 'B', 0, 'L', $fill);
                $pdf::SetTextColor(0, 0, 0); // Restablecer a negro para las demás celdas
                $pdf::Cell($w[3], $h, '', 'B', 0, 'R', $fill);
                $pdf::Cell($w[4], $h, '', 'B', 0, 'R', $fill);
                $pdf::Cell($w[5], $h, '', 'B', 0, 'R', $fill);
            } else if ($row->orden == 1) {
                $pdf::SetLineWidth(0.1);
                $pdf::SetFont('', '', 6); // 'B' para negrita
                $pdf::Cell($w[0], $h, '', 'LB', 0, 'L', $fill);
                $pdf::Cell($w[1], $h, '', 'B', 0, 'L', $fill);
                $pdf::Cell($w[2], $h, 'SALDO AL MES ANTERIOR: ', 'B', 0, 'R', $fill);
                $pdf::Cell($w[3], $h, number_format($row->debe, 2, '.', ','), 'LRB', 0, 'R', $fill);
                $pdf::Cell($w[4], $h, number_format($row->haber, 2, '.', ','), 'LRB', 0, 'R', $fill);
                $pdf::Cell($w[5], $h, number_format($row->saldo, 2, '.', ','), 'LRB', 0, 'R', $fill);
            } else if ($row->orden == 2) {
                $pdf::SetLineWidth(0.1);
                $pdf::SetFont('', '', 6); // 'B' para negrita
                $pdf::Cell($w[0], $h, $row->fecha, 'BL', 0, 'L', $fill);
                $pdf::Cell($w[1], $h, $row->lote, 'B', 0, 'L', $fill);
                $pdf::Cell($w[2], $h, $row->comentario, 'B', 0, 'L', $fill);
                $pdf::Cell($w[3], $h, number_format($row->debe, 2, '.', ','), 'LRB', 0, 'R', $fill);
                $pdf::Cell($w[4], $h, number_format($row->haber, 2, '.', ','), 'LRB', 0, 'R', $fill);
                $pdf::Cell($w[5], $h, number_format($row->saldo, 2, '.', ','), 'LRB', 0, 'R', $fill);
            } else if ($row->orden == 3) {
                $pdf::SetLineWidth(0.3);
                $pdf::SetFont('', 'B', 6); // 'B' para negrita
                $pdf::Cell($w[0], $h, '', 'BT', 0, 'L', $fill);
                $pdf::SetTextColor(!empty($row->id_cuentaempresarial) ? 0 : 255, 0, 0); // Rojo para "(No tiene equivalencia)"
                $pdf::Cell($w[1], $h, !empty($row->id_cuentaempresarial) ? $row->id_cuentaempresarial : $row->id_cuentaaasi, 'BT', 0, 'L', $fill);
                $pdf::Cell($w[2] / 2, $h, $row->comentario, 'BT', 0, 'L', $fill);
                $pdf::SetTextColor(0, 0, 0); // Restablecer a negro para las demás celdas
                $pdf::Cell($w[2] / 2, $h, 'SALDO DEL MES:', 'BT', 0, 'R', $fill);
                $pdf::Cell($w[3], $h, number_format($row->debe, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Cell($w[4], $h, number_format($row->haber, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Cell($w[5], $h, number_format($row->saldo, 2, '.', ','), 'BT', 0, 'R', $fill);
                // $pdf::Ln();
            } else if ($row->orden == 4) {

                $pdf::SetLineWidth(0.3);
                $pdf::SetFont('', 'B', 6); // 'B' para negrita
                $pdf::Cell($w[0], $h, '', 'BT', 0, 'L', $fill);
                $pdf::Cell($w[1], $h, '', 'BT', 0, 'L', $fill);
                $pdf::Cell($w[2], $h, 'SALDO AL MES:', 'BT', 0, 'R', $fill);
                $pdf::Cell($w[3], $h, number_format($row->debe, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Cell($w[4], $h, number_format($row->haber, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Cell($w[5], $h, number_format($row->saldo, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Ln();
                // $pdf::Ln();
            } else if ($row->orden == 5) {
                $pdf::SetLineWidth(0.3);
                $pdf::SetFont('', 'B', 6); // 'B' para negrita
                $pdf::Cell($w[0], $h, '', 'BT', 0, 'L', $fill);
                $pdf::Cell($w[1], $h, '', 'BT', 0, 'L', $fill);
                $pdf::Cell($w[2], $h, 'SALDO DE TODAS LA CUENTAS AL MES:', 'BT', 0, 'R', $fill);
                $pdf::Cell($w[3], $h, number_format($row->debe, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Cell($w[4], $h, number_format($row->haber, 2, '.', ','), 'BT', 0, 'R', $fill);
                $pdf::Cell($w[5], $h, number_format($row->saldo, 2, '.', ','), 'BT', 0, 'R', $fill);
                // $pdf::Ln();
            }
           
            $pdf::SetFont('', ''); // Restablecer el formato de fuente al predeterminado
            $pdf::Ln();

            // if (in_array($row->orden, [1, 2])) {
            if (in_array($row->orden, [2])) {
                $sub_total_debe += $row->debe;
                $sub_total_haber += $row->haber;
                $sub_total_saldo += $row->saldo;
            }
            $params = [
                'debe' => $sub_total_debe,
                'haber' => $sub_total_haber,
                'saldo' => $sub_total_saldo,
                'ultimo' => (($key + 1) == count($data)) ? 'S' : 'N',
                'primero' => 'N',
                'empresa_rs' => $empresa_rs,
                'empresa_ruc' => $empresa_ruc,
                'anho' => $id_anho,
                'mes_nombre' => $mes_nombre,
                'rowAccount' => $rowAccount,
            ];
            LibroMayorUpnPDF::CustomHeaderLibroMayorUpn($params);
            LibroMayorUpnPDF::CustomFooterParamsLibroMayorUpn($params);
        }
        // $pdf::Cell(array_sum($w), 0, '', 'T');
        $pdf::Output('hello_world.pdf');
    }

    public static function libro_compras_8_1($id_empresa, $id_entidad, $id_depto, $id_anho, $id_mes, $id_user)
    {
        // $html = '<h1>Hello world</h1>';
        $pdf = new CustomPDF();

        $mes_data = AccountingData::getMonthById($id_mes);
        $mes_nombre = $mes_data->nombre;
        $list_razon_social = SetupData::enterpriseByIdEnterprise($id_empresa);

        $empresa_rs = '';
        $empresa_ruc = '';
        foreach ($list_razon_social as $item) {
            $empresa_rs = $item->nombre_legal;
            $empresa_ruc = $item->ruc;
        }

        $params = [
            'debe' => 0,
            'haber' => 0,
            'ultimo' => 'N',
            'empresa_rs' => $empresa_rs,
            'empresa_ruc' => $empresa_ruc,
            'anho' => $id_anho,
            'mes_nombre' => $mes_nombre,
        ];
        CustomPDF::CustomHeaderRegistroDeCompras($params);
        $pdf::SetTitle('Registro de Compras');


        // set margins
        $pdf::SetMargins(PDF_MARGIN_LEFT, 37.1, PDF_MARGIN_RIGHT, 5);
        $pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf::SetFooterMargin(13.35);

        // set auto page breaks
        $pdf::SetAutoPageBreak(TRUE, 12);
        $pdf::setPageOrientation('L');
        $pdf::AddPage();
        // $pdf::writeHTML($html, true, false, true, false, '');
        $header = array(
            "#",
            "Cuenta",
            "Sub Cuenta",
            "Nombre de la Cuenta",
            "Glosa",
            "Débito",
            "Crédito",
        );
        $w = array(
            10,
            10,
            9,
            8,
            8,
            5,
            5,
            5,
            8,
            5,
            9,
            45,
            8,
            8,
            8,
            8,
            8,
            8,
            8,
            8,
            8,
            8,
            8,
            8,
            8,
            8,
            8,
            5,
            8,
            8,
            4,
        );

        $data = PurchasesData::listReportPurchases($id_anho, $id_mes, $id_empresa, $id_entidad, $id_depto);
        $items_sum = PurchasesData::listReportPurchases_total($id_anho, $id_mes, $id_empresa, $id_entidad, $id_depto);

        $pdf::SetFillColor(224, 235, 255);
        $pdf::SetTextColor(0);
        // $pdf::SetDrawColor(128, 0, 0);
        $pdf::SetLineWidth(0.1);
        $pdf::SetFont('', 'B', 4.5);

        $h = 1.8;

        $pdf::SetFillColor(171, 208, 255);
        $pdf::SetTextColor(43, 105, 144);
        $pdf::SetDrawColor(187, 200, 208);

        // $pdf::SetFillColor(224, 235, 255);
        // $pdf::SetTextColor(0);
        $pdf::SetFont('', '', 3.5);
        // Data
        $fill = 0;
        $pdf::Ln();
        $pdf::Ln();
        $sub_total_debe = 0;
        $sub_total_haber = 0;
        foreach ($data as $row) {
            $pdf::Cell($w[0], $h, $row->entidad . '-' . $row->id_depto . '-' . $row->lote_numero . '-' . $row->correlativo, 'LB', 0, 'L', $fill, '', 1);
            $pdf::Cell($w[1], $h, $row->cuo, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[2], $h, $row->username, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[3], $h, $row->fecha_emision, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[4], $h, $row->fecha_vto, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[5], $h, $row->comp_pago_tipo, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[6], $h, $row->comp_pago_serie, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[7], $h, $row->comp_pago_anho_emision, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[8], $h, $row->comp_pago_nro, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[9], $h, $row->infor_proveedor_tipo, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[10], $h, $row->infor_proveedor_numero, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[11], $h, $row->infor_proveedor_razon_social, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[12], $h, number_format($row->compra_gravada_bi, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[13], $h, number_format($row->compra_gravada_igv, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[14], $h, number_format($row->exportacion_bi, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[15], $h, number_format($row->exportacion_igv, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[16], $h, number_format($row->sincredito_bi, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[17], $h, number_format($row->sincredito_igv, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[18], $h, number_format($row->compras_no_grabadas, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[19], $h, number_format($row->isc, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[20], $h, number_format($row->otros_tributos, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[21], $h, number_format($row->importe_total, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[22], $h, $row->comprob_emit_sujet_no_domi, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[23], $h, $row->const_depsi_detrac_numero, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[24], $h, $row->const_depsi_detrac_fecha, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[25], $h, $row->tc, 1, 0, 'R', $fill, '', 1);
            $pdf::Cell($w[26], $h, $row->ref_comp_pago_doc_fecha, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[27], $h, $row->ref_comp_pago_doc_tipo, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[28], $h, $row->ref_comp_pago_doc_serie, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[29], $h, $row->ref_comp_pago_doc_numero, 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[30], $h, $row->retencion, 1, 0, 'L', $fill, '', 1);
            $pdf::Ln();
            // //$fill=!$fill;
            // if($row->orden == 2 ){
            //     $sub_total_debe += $row->debe;
            //     $sub_total_haber += $row->haber;    
            // }
            $params = [
                'debe' => 0,
                'haber' => 0,
                'ultimo' => 'N',
                'empresa_rs' => $empresa_rs,
                'empresa_ruc' => $empresa_ruc,
                'anho' => $id_anho,
                'mes_nombre' => $mes_nombre,
            ];
            CustomPDF::CustomHeaderRegistroDeCompras($params);
            CustomPDF::CustomFooterRegistroDeCompras($params);
        }

        $pdf::SetFillColor(43, 105, 144);
        $pdf::SetTextColor(255, 255, 255);
        $pdf::SetDrawColor(187, 200, 208);
        $fill = true;
        foreach ($items_sum as $row) {
            $pdf::Cell($w[0] + $w[1] + $w[2] + $w[3] + $w[4] + $w[5] + $w[6] + $w[7] + $w[8] + $w[9] + $w[10] + $w[11], $h, 'Total Mes   ', 'LB', 0, 'R', $fill);
            $pdf::Cell($w[12], $h, number_format($row->compra_gravada_bi, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[13], $h, number_format($row->compra_gravada_igv, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[14], $h, number_format($row->exportacion_bi, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[15], $h, number_format($row->exportacion_igv, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[16], $h, number_format($row->sincredito_bi, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[17], $h, number_format($row->sincredito_igv, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[18], $h, number_format($row->compras_no_grabadas, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[19], $h, number_format($row->isc, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[20], $h, number_format($row->otros_tributos, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[21], $h, number_format($row->importe_total, 2, '.', ','), 1, 0, 'R', $fill);
            $pdf::Cell($w[22], $h, '', 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[23], $h, '', 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[24], $h, '', 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[25], $h, '', 1, 0, 'R', $fill, '', 1);
            $pdf::Cell($w[26], $h, '', 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[27], $h, '', 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[28], $h, '', 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[29], $h, '', 1, 0, 'L', $fill, '', 1);
            $pdf::Cell($w[30], $h, '', 1, 0, 'L', $fill, '', 1);
            $pdf::Ln();
            // $fill=!$fill;
            $params = [
                'debe' => 0,
                'haber' => 0,
                'ultimo' => 'N',
                'empresa_rs' => $empresa_rs,
                'empresa_ruc' => $empresa_ruc,
                'anho' => $id_anho,
                'mes_nombre' => $mes_nombre,
            ];
            CustomPDF::CustomHeaderRegistroDeCompras($params);
            CustomPDF::CustomFooterRegistroDeCompras($params);
        }

        $pdf::Cell(array_sum($w), 0, '', 'T');
        $pdf::Output('hello_world.pdf');
    }


    public static function ExportPCGEPDF($empresa, $entidad, $anho, $mes)
    {
        // Crear el objeto PDF
        $pdf = new PcgePDF();
        $pdf::Ln();
        // Configurar márgenes y otros parámetros del PDF
        $pdf::SetMargins(10, 10, 10);
        $pdf::SetHeaderMargin(0);
        $pdf::SetFooterMargin(14);
        $pdf::SetAutoPageBreak(true, 13);
        $pdf::AddPage();
        // Establecer fuente y color de fondo para el título
        $pdf::SetFont('helvetica', 'B', 7);
        // $pdf::SetFillColor(135, 206, 235);
        $pdf::SetFillColor(211, 211, 211);
        // $pdf::SetFillColor(240, 240, 240); // Gris muy claro
        // Título
        $pdf::Cell(190, 8, 'PLAN CONTABLE GENERAL EMPRESARIAL', 1, 1, 'C', true);
        // Definir los anchos de las celdas (ajustados para ocupar todo el ancho de la página)
        $w = [20, 20, 150];  // Anchos de las columnas (ajustados para 180mm en total)
        $h = 4;  // Altura de las celdas
        // Imprimir encabezados de la tabla
        $pdf::Cell($w[0], $h, 'Padre', 1, 0, 'C');
        $pdf::Cell($w[1], $h, 'Cuenta', 1, 0, 'C');
        $pdf::Cell($w[2], $h, 'Nombre de Plan Contable General Empresarial', 1, 0, 'L');
        $pdf::Ln();
        // Imprimir los datos de la tabla
        $pdf::SetFont('helvetica', '', 6);
        $data = AccountingLegalData::getPCGEExportData($empresa, $entidad, $anho, $mes);
        foreach ($data as $key => $row) {
            // Alternar color de fondo
            $fill = ($key % 2 == 0) ? true : false;

            // Pintar las celdas con los valores correspondientes de la fila
            $pdf::Cell($w[0], $h, $row->codigo_parent, 'B', 0, 'C', $fill);
            $pdf::Cell($w[1], $h, $row->codigo_empresarial, 'B', 0, 'C', $fill);
            $pdf::Cell($w[2], $h, $row->nombre_empresarial, 'B', 0, 'L', $fill);
            $pdf::Ln();
        }
        // Generar el PDF
        $pdf::Output('Plan Contable General Empresarial.pdf', 'I');
    }



    public static function ExportPCGEaPCDTab($empresa, $entidad, $anho, $mes)
    {
        // Crear el objeto PDF
        $pdf = new PcgePDF();
        $pdf::Ln();
        // Configurar márgenes y otros parámetros del PDF
        $pdf::SetMargins(10, 10, 10);
        $pdf::SetHeaderMargin(0);
        $pdf::SetFooterMargin(14);
        $pdf::SetAutoPageBreak(true, 13);
        $pdf::AddPage();

        // Establecer fuente y color de fondo para el título
        $pdf::SetFont('helvetica', 'B', 7);
        // $pdf::SetFillColor(135, 206, 235);
        // $pdf::SetFillColor(211, 211, 211); 
        $pdf::SetFillColor(240, 240, 240); // Gris muy claro



        // Título
        $pdf::Cell(190, 8, 'PLAN CONTABLE GENERAL EMPRESARIAL', 1, 1, 'C', true);

        // Parámetros para el encabezado
        $params = [
            'empresa_rs' => 'Empresa SA',
            'anho' => $anho,
            'mes_nombre' => $mes,
        ];

        // Establecer el encabezado
        // $pdf::setEncabezado($params);

        // Definir los anchos de las celdas (ajustados para ocupar todo el ancho de la página)
        $w = [15, 15, 15, 15, 55, 20, 55];  // Anchos de las columnas (ajustados para 180mm en total)
        $h = 4;  // Altura de las celdas

        // Imprimir encabezados de la tabla
        $pdf::Cell($w[0], $h, 'Entidad', 1, 0, 'C', true);
        $pdf::Cell($w[1], $h, 'Año', 1, 0, 'C', true);
        $pdf::Cell($w[3], $h, 'Padre', 1, 0, 'C', true);
        $pdf::Cell($w[2], $h, 'Cuenta', 1, 0, 'C', true);
        $pdf::Cell($w[4], $h, 'Nombre', 1, 0, 'C', true);
        $pdf::Cell($w[5], $h, 'Cod.Aasinet', 1, 0, 'C', true);
        $pdf::Cell($w[6], $h, 'Desc. Cuenta - Aasinet', 1, 0, 'C', true);
        $pdf::Ln();
        $pdf::Ln();
        // Imprimir los datos de la tabla
        $pdf::SetFont('helvetica', '', 6);
        $data = AccountingLegalData::getExportPCGEaPCDDataTab($empresa, $entidad, $anho, $mes);
        foreach ($data as $key => $row) {
            // Alternar color de fondo
            $fill = ($key % 2 == 0) ? true : false;

            // Pintar las celdas con los valores correspondientes de la fila
            $pdf::Cell($w[0], $h, '', 'B', 0, 'C'); // Entidad
            $pdf::Cell($w[1], $h, '', 'B', 0, 'C'); // Año
            $pdf::Cell($w[2], $h, $row->codigo_parent, 'B', 0, 'L', false);
            $pdf::Cell($w[3], $h, $row->codigo_empresarial, 'B', 0, 'L', false);
            $pdf::Cell($w[4], $h, $row->nombre_empresarial, 'B', 0, 'L', false);
            $pdf::Cell($w[2], $h, $row->codigo_aaasi, 'B', 0, 'L', false);
            $pdf::Cell($w[6], $h, $row->nombre_denominacional, 'B', 1, 'L', false);
            $pdf::Ln(); // Salto de línea para la siguiente fila
        }

        // Generar el PDF
        $pdf::Output('Plan Contable General Empresarial.pdf', 'I');
    }
}
