var $j = $.noConflict();

$j(document).ready(function(){
    $j('.input-daterange').datepicker({
     todayBtn:'linked',
     format: "yyyy-mm-dd",
     autoclose: true
    });
});
