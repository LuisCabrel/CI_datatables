$(document).ready(function() { 
    tblreporteventa();
    var start = moment().subtract(4, 'days');
		var end = moment();
    $('#fecha').daterangepicker({
        //timePicker: true,        
        autoApply: true,
        autoUpdateInput: false,
        locale: {
            format: 'DD/MM/YYYY',
            monthNames: [
                "Enero",
                "Febrero",
                "Marzo",
                "Abril",
                "Mayo",
                "Junio",
                "Julio",
                "Agosto",
                "Septiembre",
                "Octubre",
                "Noviembre",
                "Diciembre"
            ],
            separator: " - ",
            applyLabel: "Aceptar",
            cancelLabel: "Cancelar",
            fromLabel: "De",
            toLabel: "A",
            customRangeLabel: "Pers.",
            weekLabel: "S",
            daysOfWeek: [
                "Do",
                "Lu",
                "Ma",
                "Mi",
                "Ju",
                "Vi",
                "Sá"
            ],
            firstDay: 1,
            cancelLabel: 'Clear'
        },
        startDate: start,
        endDate: end,
        autoApply: true,
        showDropdowns: false
    }, function(start, end) {
        startDate = start.format('YYYY-MM-DD');
        endDate = end.format('YYYY-MM-DD');
    });

    $('input[name="fecha"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
            'DD/MM/YYYY'));
    });

    $('#fechaventa').daterangepicker({
        autoApply: true,
        autoUpdateInput: false,
        locale: {
            format: 'DD/MM/YYYY',
            monthNames: [
                "Enero",
                "Febrero",
                "Marzo",
                "Abril",
                "Mayo",
                "Junio",
                "Julio",
                "Agosto",
                "Septiembre",
                "Octubre",
                "Noviembre",
                "Diciembre"
            ],
            separator: " - ",
            applyLabel: "Aceptar",
            cancelLabel: "Cancelar",
            fromLabel: "De",
            toLabel: "A",
            customRangeLabel: "Pers.",
            weekLabel: "S",
            daysOfWeek: [
                "Do",
                "Lu",
                "Ma",
                "Mi",
                "Ju",
                "Vi",
                "Sá"
            ],
            firstDay: 1,
            cancelLabel: 'Clear'
        },
        startDate: start,
        endDate: end,
        autoApply: true,
        showDropdowns: false
    }, function(start, end) {
        startDate = start.format('DD/MM/YYYY');
        endDate = end.format('DD/MM/YYYY');
    });
    $('input[name="fechaventa"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
            'DD/MM/YYYY'));
    });

    
    /*var url=new URLSearchParams(location.search);
    document.getElementById('ruta').value = url.get('ruta');
    document.getElementById('buses').value = url.get('buses');
    document.getElementById('vendedor').value = url.get('vendedor');
    document.getElementById('sede').value = url.get('sede');
*/
});

/*
$( "#ruta, #buses, #vendedor, #sede" ).change(function() {
    tblReporteVenta()
});

$( ".fechaventa, .fechaviaje" ).click(function() {
    tblReporteVenta()
});

  function pagelink(link){
     var uri=link;
      var newTitle=link;
      var state=({
        url: uri, title: newTitle        
        }); 
     history.pushState(state, newTitle, uri);
     tblReporteVenta()
  }
*/
function tblReporteVenta__(){
    var url=new URLSearchParams(location.search);
    page = url.get('page');
    var search =$("#buscar").val();
    var ruta =$("#ruta").val();
    var buses =$("#buses").val();
    var vendedor =$("#vendedor").val();
    var sede =$("#sede").val();
    var fecha =$("#fecha").val();
    var fechaventa =$("#fechaventa").val();
    $.ajax({
        url:base_url+"/reportes/listaReporteVenta/0/0/0/"+page,
        type: "post",
        dataType: "json",
        data:{
            page:page,
            search:search,
            ruta:ruta,
            buses:buses,
            vendedor:vendedor,
            sede:sede,
            fecha:fecha,
            fechaventa:fechaventa
        },
        success: function(data) {
            $("#datosRegistros").html(data.tableBody);
            $("#paginacion").html(data.pagination);
        }        
    });
}

//var tblReporteVenta;
function tblreporteventa() {
    var ruta =$("#ruta").val();
    var buses =$("#buses").val();
    var vendedor =$("#vendedor").val();
    var sede =$("#sede").val();
    var fecha =$("#fecha").val();
    var fechaventa =$("#fechaventa").val();

    var tblReporteVenta = $("#tbl_reporteventa").DataTable({
    serverSide: true,
    processing: true,
    stateSave: false,
    autoWidth: false,
    scrollCollapse: true,
    searching: false,
    lengthChange: false,
    bDestroy: true,
    ajax: {
      url: base_url + "/reportes/tblreporteventas",
      type: "GET",
      data:{
            ruta:ruta,
            buses:buses,
            vendedor:vendedor,
            sede:sede,
            fecha:fecha,
            fechaventa:fechaventa
        },
      datatype: "json",
    },
    columns: [
        { data: 'nombresede', },
        { data: 'ruta', },
        { data: 'fechaviaje', },
        { data: 'horasalida', },
        { data: 'nombreschofer', },
        { data: 'numerodocumentocliente', },
        { data: 'cliente', },
        { data: 'destinocliente', },
        { data: 'precioboleto', },
        { data: 'butacacliente', },
        { data: 'vendedor', },
    ],
    language: {
      url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
    },
    //order: [[1, "asc"]],
    aoColumnDefs: [
                {
                    bSortable: false,
                    aTargets: [0],
                },
            ],
  });
}

function exportarexcelventas() {
    var formdata = $("#formVentas").serialize();
    window.location.href = base_url+"/reportes/exportarexcelventas?"+formdata;
}