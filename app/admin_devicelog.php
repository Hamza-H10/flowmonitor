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

<!-- DATA FORM -->
<a name="table1_user_form_target"></a>
<h3 class="ui top attached header compact">
  Device Log Details
  <span>
    <button class="ui teal mini icon right floated button" id="table1_form_show" >
      ADD &nbsp;
      <i class="add alternate icon"></i>
    </button>
    <button class="ui circular negative mini icon right floated button" id="table1_form_hide" >
      <i class="close alternate icon"></i>
    </button>
  </span>
    <!-- <i class='close mini icon' onclick="$('#user_form1').transition('fade');"></i> -->
</h3>

  <div class="ui teal attached segment compact" id="table1_user_form1">
  <form name="table1_userform" id="table1_userform" class="ui form">
    <div class="ui negative message transition hidden" id="table1_error_message"></div>
        <div class="two fields">
            <div class="field">
                <label>Flow Rate</label>
                <input type="text" name="flow_rate" placeholder="Flow Rate">
            </div>
            <div class="field">
                <label>Total Positive Flow</label>
                <input type="text" name="total_pos_flow" placeholder="Total Positive Flow">
            </div>
        </div>


        <div class="two fields">
            <div class="field">
                <label>Signal Strength</label>
                <input type="text" name="signal_strength" placeholder="Signal Strength">
            </div>
            <div class="field">
                <label>Update Date</label>
                <div class="ui calendar" id="update_date_div">
                  <div class="ui input left icon">
                    <i class="calendar icon"></i>
                    <input type="text" name="update_date" placeholder="Date">
                  </div>
                </div>

                <!--<input type="text" name="update_date" placeholder="Update Date">-->
            </div>
              <?php 
                  echo "<input type='hidden' name='device_id' id='device_id' value='$d_id'>  ";
    
              ?>
        </div>

        <button class="ui button" type="submit" id="table1_userform_submit">Submit</button>
        <button class="ui button red" type="reset" id="table1_userform_cancel">Reset</button>
        <div class="ui error message"></div>
    </form>
    </div>

    <div class="ui horizontal divider header">Device History</div>
  <!-- DATA LIST -->

    <div class="ui grid ">
      <div class="eleven wide column">
        <!-- <h2 class="ui header">User List</h2> -->
        <button class="ui circular negative icon button" id="table1_delete" >
          <i class="trash alternate icon"></i>
        </button>
       <!-- From Date -->
    <div class="ui calendar" id="fromDate">
        <div class="ui input left icon">
            <i class="calendar icon"></i>
            <input type="date" placeholder="From Date">
        </div>
    </div>
    <!-- To Date -->
    <div class="ui calendar" id="toDate">
        <div class="ui input left icon">
            <i class="calendar icon"></i>
            <input type="date" placeholder="To Date">
        </div>
    </div>
<!-- Download Button --><button class="ui circular primary icon button" id="download">
    <i class="download icon"></i> Download
</button>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Add an event listener to the download button
    document.getElementById("download").addEventListener("click", function () {
        // Display a message on the user interface
        var message = document.createElement("p");
        message.textContent = "Downloading all rows, please wait...";
        document.body.appendChild(message);
        // Trigger an AJAX request
$.ajax({
    url: 'http://localhost/flowmonitor/app/download.php?action=downloadCSV',
    method: 'GET',
    xhrFields: {
        responseType: 'blob' // Receive data as a binary blob
    },
    success: function (data, status, xhr) {
        if (xhr.getResponseHeader('Content-Disposition')) {
            console.log('Download started');
            var filename = xhr.getResponseHeader('Content-Disposition').split('filename=')[1];
            var blob = new Blob([data], { type: 'application/octet-stream' });
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } else {
            console.log('Download failed');
        }
    },
    error: function () {
        console.log('AJAX request failed');
    },
    complete: function () {
        message.remove();
    }
});
    });
</script>


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
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  <!-- <script src="js/jquery-3.3.1.min.js"></script> -->
  <script src="js/semantic.min.js"></script>
  <script src="js/calendar.min.js"></script>
  <script src="js/pagination.js"></script>
  <script src="js/tabulation.js"></script>
  <script>
// This code defines a Tabulation object and sets its properties. 
//The Tabulation object is used to manage a table that displays device history. 
//It makes API calls to retrieve, add, delete, and edit device history data. The table supports multi-selection.
    var table1= new Tabulation({
            apiUrl: "<?=$app_root?>/api/?function=device_history&device_id=<?=$d_id?>&pgno=",
            addUrl:"<?=$app_root?>/api/?function=history_add",
            delUrl:"<?=$app_root?>/api/?function=history_delete&device_id=<?=$d_id?>&del_id=", 
            editUrl:"<?=$app_root?>/api/?function=history_edit&row_id=",
            fetchUrl:"<?=$app_root?>/api/?function=history_fetch&row_id=",         
            selectMulti: true,
            });

//fetchUrl: "<?=$app_root?>/api/?function=history_fetch",
        
    $(function() {
        $('.selection.dropdown').dropdown();
        $('.ui.checkbox').checkbox();
        $('#update_date_div').calendar({
            type: 'date',
            formatter: {
              date: function (date, settings) {
                if (!date) return '';
                var day = date.getDate();
                var month = date.getMonth()+1;
                var year = date.getFullYear();
                return year + '-' + month + '-' + day;              
              }
            }
        });

        //table1.init();
        table1.loadPage(1, true);


        //In the below snippet when the commented or uncommented in both ways the Add (device log details) are working.
        //and also there is no effect on the data table as well. Findings - don't know what the snippet is used for
        $('.ui.form').form({
        fields: {
            totalflow: {
            identifier  : 'total_pos_flow',
            rules: [
              {
                type   : 'integer',
                prompt : 'Please enter an integer value'
              }
            ]
            },
            signalstrength: {
            identifier  : 'signal_strength',
            rules: [
              {
                type   : 'integer[0..100]',
                prompt : 'Please enter an integer value from 0 to 100'
              }
            ]
            },
            flowrate: {
            identifier  : 'flow_rate',
            rules: [
              {
                type   : 'decimal',
                prompt : 'Please enter a valid decimal'
              }
            ]
            }            
          }
        });
    });
// --------------    
    function downloadCSV() {
    
        var tabulationData = table1.getData();

        // Create a CSV string from the tabulation data
        var csv = "Flow Rate (Cubic Meter/ Hour),Total Pos Flow (Cubic Meter),Signal Strength,Update Date\n"; // Replace with your column headers
        
        for (var i = 0; i < tabulationData.length; i++) {
            var row = tabulationData[i];
            csv += row.column1 + "," + row.column2 + "," + row.column3 + "\n"; // Replace with your row data
        }
        // Create a Blob object from the CSV string
        var blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
        
        // Create a temporary download link
        var link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "Device_data.csv"; // Replace with your desired file name

        // Trigger the download
        link.click();
    }
// ------------------


</script>

  </script>
</body>  
</html>
