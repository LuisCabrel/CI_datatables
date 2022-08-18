<?php 
namespace App\Controllers;
//use App\Models\Home_model;
use App\Models\Procesos_model;
use App\Models\Butacas_model;
use App\Models\Reportes_model;


use App\Libraries\Pdf;
use FPDF;
use App\Libraries\Ciqrcode;

class Reportes extends BaseController
{
public function tblreporteventas()
    {
        $valorBuscado = $this->request->getGet('search')['value'];
        $dataColumna = $this->request->getGet('order');
        $nombreColumna = $dataColumna[0]['column'];
        $order = $dataColumna[0]['dir'];
        $start = $this->request->getGet('start');
        $end = $this->request->getGet('length');
        $draw = $this->request->getGet('draw');

        $ruta = $this->request->getGet('ruta');
		$buses = $this->request->getGet('buses');
		$vendedor = $this->request->getGet('vendedor');
		$sede = $this->request->getGet('sede');
		$fechaviaje = $this->request->getGet('fecha');
		$fechaventa = $this->request->getGet('fechaventa');


        $listasReporteVenta = $this->reportes_model->datatable_reporteventas($valorBuscado, $dataColumna, $nombreColumna, $order, $start, $end, $draw,$ruta,$buses,$vendedor,$sede,$fechaviaje,$fechaventa);
        echo json_encode($listasReporteVenta);
    }
}