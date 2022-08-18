<?php

namespace App\Models;

use CodeIgniter\Model;
require_once './vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Reportes_model extends Model{
    protected $table      = 'v_reporteventa';

    public function search($busca,$ruta,$buses,$vendedor,$sede,$fecha,$fechaventa){
        $builder = $this->table('v_reporteventa');
        if($busca!=''){
            $builder->like('nombresede',$busca);
            $builder->orLike('ruta',$busca);
            $builder->orLike('nombreschofer',$busca);            
            $builder->orLike('cliente',$busca);            
            $builder->orLike('destinocliente',$busca);            
            $builder->orLike('vendedor',$busca);            
        } 
        if($ruta!=''){
            $builder->where('idruta',$ruta);
        }
        if($buses!=''){
            $builder->where('idbuses',$buses);
        }
        if($vendedor!=''){
            $builder->where('idvendedor',$vendedor);
        }
        if($sede!=''){
            $builder->where('idsede',$sede);
        }

        
        if($fecha!=''){
            $arrayFecha =  explode(" - ",$fecha);
            $f1 = explode("/",$arrayFecha[0]);
            $f2 = explode("/",$arrayFecha[1]);
            $fecha1 = $f1[2]."-".$f1[1]."-".$f1[0];
            $fecha2 = $f2[2]."-".$f2[1]."-".$f2[0];
             
            $builder->where('fechaviaje >=', $fecha1);
            $builder->where('fechaviaje <=', $fecha2);
        }

        if($fechaventa!=''){
            $arrayFechaVenta =  explode(" - ",$fechaventa);
            $fv1 = explode("/",$arrayFechaVenta[0]);
            $fv2 = explode("/",$arrayFechaVenta[1]);
            $fechav1 = $fv1[2]."-".$fv1[1]."-".$fv1[0];
            $fechav2 = $fv2[2]."-".$fv2[1]."-".$fv2[0];
             
            $builder->where('fecha_venta >=', $fechav1);
            $builder->where('fecha_venta <=', $fechav2);
        }

        //echo $builder->getCompiledSelect();exit;
              
        return $builder ;        
    }

    public function totalVentaReservado($idestadoBoleta=1,$idhorarioviajes){
        $builder = $this->db->table('ventas_boletos');
        $builder->select("count(1)as total");
        $builder->where("idestadoboleto",$idestadoBoleta);
        $builder->where("idhorario",$idhorarioviajes);
        $query = $builder->get();
        return $query->getRow();
    }

    public function datatable_reporteventas($valorBuscado, $dataColumna, $nombreColumna, $order, $start, $end, $draw,$ruta,$buses,$vendedor,$sede,$fechaviaje,$fechaventa){

        $idperfil = session()->get('idperfil');
        $table_map = [
            0 => 'nombresede',
            1 => 'ruta',
            2 => 'fechaviaje',
            3 => 'horasalida',
            4 => 'nombreschofer',
            5 => 'numerodocumentocliente',
            6 => 'cliente',
            7 => 'destinocliente',
            8 => 'precioboleta',
            9 => 'butacacliente',
            10 => 'vendedor',
        ];

        $sql_count = $this->datosReporteVentasTotal($ruta,$buses,$vendedor,$sede,$fechaviaje,$fechaventa);
        $sql_data = $this->datosReporteVentas($ruta,$buses,$vendedor,$sede,$fechaviaje,$fechaventa);
        $condicion = "";
        $conWhere =" WHERE ";
        if (!empty($valorBuscado)) {
            foreach ($table_map as $key => $val) {
                if ($table_map[$key] != '') {
                    if ($table_map[$key] === 'nombresede') {
                        $condicion .= " AND LOWER(" . $val . ")  LIKE LOWER('%" . $valorBuscado . "%') ";
                    } else {
                        $condicion .= " OR LOWER(" . $val . ") LIKE LOWER('%" . $valorBuscado . "%') ";
                    }
                }
            }
            $conWhere='';
        }

        $sql_count = $sql_count . $condicion;
        $sql_data = $sql_data . $condicion; //echo $sql_data;exit;

        $total_count = $this->db->query($sql_count)->getRow();
        $sql_data .= " ORDER BY " . $table_map[$nombreColumna] . " " . $order . " LIMIT " . $start . "," . $end . " ";
        
        $data = $this->db->query($sql_data)->getResult();
        $tabla = array();
        $x=1;
        if(count($data)>0){ 
            foreach ($data as $fila) {

                $html = array(
                    'nombresede' =>$fila->nombresede,
                    'ruta' =>$fila->ruta,
                    'fechaviaje' =>$fila->fechaviaje,
                    'horasalida' =>$fila->horasalida,
                    'nombreschofer' =>$fila->nombreschofer,
                    'numerodocumentocliente' =>$fila->numerodocumentocliente,
                    'cliente' =>$fila->cliente,
                    'destinocliente' =>$fila->destinocliente,
                    'precioboleto' => $fila->precioboleto,
                    'butacacliente' =>$fila->butacacliente,
                    'vendedor' =>$fila->vendedor,             
                );
                $x++;

                array_push($tabla, $html);
            }
        }

        $json_data = [
            'draw' => intval($draw),
            'recordsTotal' => $total_count->total,
            'recordsFiltered' => $total_count->total,
            'data' => $tabla,
            'debug_query' => null, //$sql_data,
        ];

        return $json_data;
    }

    public function datosReporteVentas($ruta,$buses,$vendedor,$sede,$fechaviaje,$fechaventa)
    {
        $idperfil = session()->get('idperfil');
        $whereVededor ='';
        if($idperfil!=1){
            $whereVededor =' AND idvendedor ='.$idperfil;
        }

        $whereRuta='';
        if($ruta !=''){
            $whereRuta=' AND idruta='.$ruta." ";
        }

        $whereBuses='';
        if($buses !=''){
            $whereBuses=' AND idbuses='.$buses." ";
        }

        $whereVendedor='';
        if($vendedor !=''){
            $whereVendedor=' AND idvendedor='.$vendedor." ";
        }

        $whereSede='';
        if($sede !=''){
            $whereSede=' AND idsede='.$sede." ";
        }

        $fecha ='';
        if($fechaviaje!=''){
            $arrayFecha =  explode(" - ",$fechaviaje);
            $f1 = explode("/",$arrayFecha[0]);
            $f2 = explode("/",$arrayFecha[1]);
            $fecha1 = $f1[2]."-".$f1[1]."-".$f1[0];
            $fecha2 = $f2[2]."-".$f2[1]."-".$f2[0];

           $fecha=" AND fechaviaje BETWEEN '".$fecha1."' AND '".$fecha2."' ";
        }

        $fechaV='';
        if($fechaventa!=''){
            $arrayFechaVenta =  explode(" - ",$fechaventa);
            $fv1 = explode("/",$arrayFechaVenta[0]);
            $fv2 = explode("/",$arrayFechaVenta[1]);
            $fechav1 = $fv1[2]."-".$fv1[1]."-".$fv1[0];
            $fechav2 = $fv2[2]."-".$fv2[1]."-".$fv2[0];

            $fechaV=" AND fecha_venta BETWEEN '".$fechav1."' AND '".$fechav2."' ";
        }

        $sql="SELECT * FROM(SELECT * FROM v_reporteventa WHERE idbuses>0 {$whereVededor} {$whereRuta} {$whereBuses} {$whereVendedor} {$whereSede} {$fecha} {$fechaV} )tblVentas WHERE idbuses>0 ";
        return $sql;
    }

    public function datosReporteVentasTotal($ruta,$buses,$vendedor,$sede,$fechaviaje,$fechaventa)
    {
        $idperfil = session()->get('idperfil');
        $whereVededor ='';
        if($idperfil!=1){
            $whereVededor =' AND idvendedor ='.$idperfil;
        }

        $whereRuta='';
        if($ruta !=''){
            $whereRuta=' AND idruta='.$ruta." ";
        }

        $whereBuses='';
        if($buses !=''){
            $whereBuses=' AND idbuses='.$buses." ";
        }

        $whereVendedor='';
        if($vendedor !=''){
            $whereVendedor=' AND idvendedor='.$vendedor." ";
        }

        $whereSede='';
        if($sede !=''){
            $whereSede=' AND idsede='.$sede." ";
        }

        $fecha ='';
        if($fechaviaje!=''){
            $arrayFecha =  explode(" - ",$fechaviaje);
            $f1 = explode("/",$arrayFecha[0]);
            $f2 = explode("/",$arrayFecha[1]);
            $fecha1 = $f1[2]."-".$f1[1]."-".$f1[0];
            $fecha2 = $f2[2]."-".$f2[1]."-".$f2[0];

           $fecha=" AND fechaviaje BETWEEN '".$fecha1."' AND '".$fecha2."' ";
        }

        $fechaV='';
        if($fechaventa!=''){
            $arrayFechaVenta =  explode(" - ",$fechaventa);
            $fv1 = explode("/",$arrayFechaVenta[0]);
            $fv2 = explode("/",$arrayFechaVenta[1]);
            $fechav1 = $fv1[2]."-".$fv1[1]."-".$fv1[0];
            $fechav2 = $fv2[2]."-".$fv2[1]."-".$fv2[0];

            $fechaV=" AND fecha_venta BETWEEN '".$fechav1."' AND '".$fechav2."' ";
        }

        $sql="SELECT count(1)as total FROM(
            SELECT * FROM v_reporteventa WHERE idbuses>0 {$whereVededor} {$whereRuta} {$whereBuses} {$whereVendedor} {$whereSede} {$fecha} {$fechaV}
        )tblVentas WHERE idbuses>0 ";
        return $sql;
    }

    public function excel_exportarVentas($ruta,$buses,$vendedor,$sede,$fechaviaje,$fechaventa)
    {
        $sql_data = $this->datosReporteVentas($ruta,$buses,$vendedor,$sede,$fechaviaje,$fechaventa);        
        $lista = $this->db->query($sql_data)->getResult();

        $objPHPExcel = new Spreadsheet();
        $objPHPExcel->getProperties()->setCreator("BusDriver")
            ->setLastModifiedBy("BusDriver")
            ->setTitle("Reporte de ventas")
            ->setSubject("Reporte de ventas")
            ->setDescription("Información brindada por BusDriver - Esta información es confidencial")
            ->setKeywords("office 2007")
            ->setCategory("Reportes");

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getFont()->setBold(true);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'BUS')
            ->setCellValue('B1', 'SEDE')
            ->setCellValue('C1', 'RUTA')
            ->setCellValue('D1', 'FECHA VIAJE')
            ->setCellValue('E1', 'HORA SALIDA')
            ->setCellValue('F1', 'CONDUCTORES')
            ->setCellValue('G1', 'TIPO DOC')
            ->setCellValue('H1', '# DOCUMENTO')
            ->setCellValue('I1', 'PASAJEROS')
            ->setCellValue('J1', 'FECHA VENTA')
            ->setCellValue('K1', 'DESTINO')
            ->setCellValue('L1', 'PRECIO BOLETO')
            ->setCellValue('M1', '# BUTACA')
            ->setCellValue('N1', 'VENDEDOR');

        $inicioCelda = 2;

        foreach ($lista as $key) {
            $dataBus = $this->detallebus($key->idbuses);
            $detalleBus = $dataBus->numeroplaca." [ASIENTOS: ".$dataBus->totalpasajero."]";
            $tipodocumento = $this->tipodocumento($key->idtipodocumentocliente)->siglas;

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $inicioCelda, $detalleBus);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B' . $inicioCelda, $key->nombresede);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C' . $inicioCelda, $key->ruta);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D' . $inicioCelda, $key->fechaviaje);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E' . $inicioCelda, $key->horasalida);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F' . $inicioCelda, $key->nombreschofer);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G' . $inicioCelda, $tipodocumento);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H' . $inicioCelda, $key->numerodocumentocliente);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I' . $inicioCelda, $key->cliente);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J' . $inicioCelda, $key->fecha_venta);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K' . $inicioCelda, $key->destinocliente);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L' . $inicioCelda, $key->precioboleto);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M' . $inicioCelda, $key->butacacliente);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N' . $inicioCelda, $key->vendedor);
            $inicioCelda++;
        }

        $objPHPExcel->getActiveSheet()->getStyle('A2:C' . $inicioCelda)->getAlignment()->setHorizontal('left');
        $objPHPExcel->getActiveSheet()->getStyle("A1:N1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('26a69a');
        $objPHPExcel->getActiveSheet()->freezePane('A2');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('Reporte Ventas');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reportes-Ventas.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 02 Abr 2022 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = new Xlsx($objPHPExcel);
        ob_clean();
        flush();
        $objWriter->save('php://output');
        exit;
    }

    public function detallebus($id)
    {
        $builder = $this->db->table('buses');
        $builder->where("idbuses",$id);
        $query = $builder->get();
        return $query->getRow();
    }

    public function tipodocumento($id)
    {
        $builder = $this->db->table('tipodocumento');
        $builder->where("idtipodocumento",$id);
        $query = $builder->get();
        return $query->getRow();
    }
}