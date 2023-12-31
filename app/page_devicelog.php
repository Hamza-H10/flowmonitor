<?php
    $d_id = getValue('device_id',false,0);
    $database = new Database();
    $stmt = $database->execute("SELECT device_number, device_friendly_name FROM devices WHERE id=".$d_id);
        
    // retrieve our table contents
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $device_name = $row["device_friendly_name"];
        $device_number = $row["device_number"];
    }
?>
<div class="ui main container">

<h3 class="ui center aligned header">
<?php if ($d_id) echo "Device history for $device_name ($device_number)"; ?>
</h3>


  <!-- DATA LIST -->

    <div class="ui grid ">
      <div class="eleven wide column">
        <button class="ui circular black icon button" id="btnExport" >
          <i class="file excel icon"></i>
        </button>
        <!-- <h2 class="ui header">User List</h2> -->

      </div>
      <div class="five wide column right floated right aligned">
        <div class="ui icon input">
          <input type="text" placeholder="Search..." id="table1_search">
          <i class="circular delete link icon" id="table1_clear_btn"></i>
          <i class="inverted circular search link icon" id="table1_search_btn"></i>
        </div>        
      </div>
      <div id="table1_datawindow" class="table_datawindow"></div>
      <!-- <div class="content" id="info"></div> -->
      <div id="table1_pagination" class="eleven wide column"></div>
      <div class="five wide column right floated right aligned">
        <h4 class="ui right floated">
          <div class="content" id="table1_info"></div>
        </h4>
      </div>
      <div id="Div_exceltable" style="display:none"></div>
    </div>
  </div>

  <script src="js/jquery-3.3.1.min.js"></script>
  <script src="js/tableToExcel.js"></script>
  <script src="js/semantic.min.js"></script>
  <script src="js/pagination.js"></script>
  <script src="js/tabulation.js"></script>
  <script>
    var table1= new Tabulation({
            apiUrl: "<?=$app_root?>/api/?function=device_history&device_id=<?=$d_id?>&pgno=",
            fetchUrl:"<?=$app_root?>/api/?function=user_fetch&row_id=",
            delete: false, edit: false, selectMulti: false,
            });

    $(function() {
        $('.selection.dropdown').dropdown();
        $('.ui.checkbox').checkbox();
        $('.ui.indicating.progress').progress();
        $('#btnExport').popup({content : 'Export to Excel format'});

        //table1.init();
        table1.loadPage(1, true);
        $("#btnExport").click(function() {

            $.get("<?=$app_root?>/api/?function=device_history_print&device_id=<?=$d_id?>", function(data, status) {
                if(status == 'success') {
    
                    var myObj = JSON.parse(data);
                    var table_output = '';
                    var key_names;
                    var className = ''; 
                    let re = /_/g;
                    var table_output = "<table class='ui celled compact striped teal table'><thead><tr>";
    
                    if(myObj.records.length > 0) {
                        key_names = Object.keys(myObj.records[0]);
                        for(var ai = 1; ai < key_names.length; ai++) {
                            if(myObj.text_align)
                                className = " class='"+myObj.text_align[ai]+" aligned'";
    
                            table_output += "<th"+className+">" + key_names[ai].replace(re, ' ').ucwords() + "</th>";
                        }
                    }
                    table_output += "</tr></thead><tbody>";
                    $.each(myObj.records, function (val) {
                        className = ''; 
                        
                        table_output += "<tr>";
                        
                        for(var inx = 1; inx < key_names.length; inx++) {
                            if(myObj.text_align)
                                className = " class='"+myObj.text_align[inx]+" aligned'";
                            table_output += "<td"+className+">"+myObj.records[val][key_names[inx]]+"</td>";
                        }
                        table_output += "</tr>";
                    });
                    table_output += "</tbody></table>";
    
                    $('#Div_exceltable').html(table_output);
                    let table = document.getElementsByTagName("table");
            
                    TableToExcel.convert(table[1], { // html code may contain multiple tables so here we are refering to 1st table tag
                       name: `deviceLog.xlsx`, // fileName you could use any name
                       sheet: {
                          name: 'Sheet 1' // sheetName
                       }
                    });
            
                }
                else {
                    // error message print
                }
            }).fail(function() {
                var table_output, myObj;
                table_output = "<table class='ui celled compact striped teal table'><tr><td class='center aligned'>No records found</td></tr></table>";
    
                $('#Div_exceltable').html(table_output);
            });
            
        });
    });

  </script>
</body>  
</html>
