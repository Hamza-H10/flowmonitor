<?php
$d_id = getValue('device_id', false, 0);
$database = new Database();
$stmt = $database->execute("SELECT device_number, device_friendly_name FROM devices WHERE id=" . $d_id);

// retrieve our table contents
if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $device_name = $row["device_friendly_name"];
  $device_number = $row["device_number"];
}
?>
<?php
include_once('export.php');
//include('inc/db_connect.php'); // Include your PDO database connection code
?>

<!-- ----------------------- -->
<!-- the date buttons will style from bootstrappcdn library but enabling this delete function is not working fix this. -->

<!-- ----------------------- -->

<div class="ui main container">

  <h3 class="ui center aligned header">
    <?php if ($d_id) echo "Device history for $device_name ($device_number)"; ?>
  </h3>

  <!-- DATA FORM -->
  <a name="table1_user_form_target"></a>
  <h3 class="ui top attached header compact">
    Device Log Details
    <span>
      <button class="ui teal mini icon right floated button" id="table1_form_show">
        ADD &nbsp;
        <i class="add alternate icon"></i>
      </button>
      <button class="ui circular negative mini icon right floated button" id="table1_form_hide">
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
      <button class="ui circular negative icon button" id="table1_delete">
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

      <!-- Download Button -->
      <button class="ui circular primary icon button" id="download">
        <i class="download icon"></i> Download
      </button>
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script>
        // Add an event listener to the download button
        document.getElementById("download").addEventListener("click", function() {
          // Display a message on the user interface
          var message = document.createElement("p");
          message.textContent = "Downloading all rows, please wait...";
          document.body.appendChild(message);
          // Trigger an AJAX request
          $.ajax({
            //url: 'http://localhost/flowmonitor/app/download.php?action=downloadCSV',
            Url: "<?= $app_root ?>/api/?function=history_export&device_id=<?= $d_id ?>",
            method: 'GET',
            xhrFields: {
              responseType: 'blob' // Receive data as a binary blob
            },
            success: function(data, status, xhr) {
              if (xhr.getResponseHeader('Content-Disposition')) {
                console.log('Download started');
                var filename = xhr.getResponseHeader('Content-Disposition').split('filename=')[1];
                var blob = new Blob([data], {
                  type: 'application/octet-stream'
                });
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
            error: function() {
              console.log('AJAX request failed');
            },
            complete: function() {
              message.remove();
            }
          });
        });
      </script>

      <!-- <div class="second-eleven-wide-column">
        <style>
          @media (max-width: 768px) {

            .eleven-wide-column,
            .second-eleven-wide-column {
              width: 100%;
            }
          }
        </style>
        <br>
        <div class="row">
          <form method="post">
            <div class="input-daterange">
              <div class="col-md-4">
                From<input type="text" name="fromDate" class="form-control" value="<?php echo date("Y-m-d"); ?>" readonly />
                <?php echo $startDateMessage; ?>
              </div>
              <div class="col-md-3">
                To<input type="text" name="toDate" class="form-control" value="<?php echo date("Y-m-d"); ?>" readonly />
                <?php echo $endDateMessage; ?>
              </div>
            </div>
            <div class="col-md-2">
              <div>&nbsp;</div>
              <input type="submit" name="export" value="Export to CSV" class="btn btn-info" />
            </div>
          </form>
        </div>

        <div class="row">
          <div class="col-md-8">
            <?php echo $noResult; ?>
          </div>
        </div>
        <br />
      </div>
    </div> -->

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


  <!--NOTE: At this place including this jquery-3.3.1.min.js in any form is cause the error of datepicker is not a function because the same script is declared above and not cause the issue.  -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.js"></script>

  <script src="js/datepickers.js"></script>
  <script src="js/jquery-3.3.1.min.js"></script>
  <script src="js/semantic.min.js"></script>
  <script src="js/calendar.min.js"></script>
  <script src="js/pagination.js"></script>
  <script src="js/tabulation.js"></script>



  <script>
    var table1 = new Tabulation({
      apiUrl: "<?= $app_root ?>/api/?function=device_history&device_id=<?= $d_id ?>&pgno=",

      downloadUrl: "<?= $app_root ?>/api/?function=history_download&device_id=<?= $d_id ?>&pgno=",

      addUrl: "<?= $app_root ?>/api/?function=history_add",
      delUrl: "<?= $app_root ?>/api/?function=history_delete&device_id=<?= $d_id ?>&del_id=", //this is creating the url for delete by concatinating values of "app_root", "function", "device_id", and a static string "del_id=". 
      editUrl: "<?= $app_root ?>/api/?function=history_edit&row_id=",
      fetchUrl: "<?= $app_root ?>/api/?function=history_fetch&row_id=",
      selectMulti: true,
    });

    $(function() {
      $('.selection.dropdown').dropdown();
      $('.ui.checkbox').checkbox();
      $('#update_date_div').calendar({
        type: 'date',
        formatter: {
          date: function(date, settings) {
            if (!date) return '';
            var day = date.getDate();
            var month = date.getMonth() + 1;
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
            identifier: 'total_pos_flow',
            rules: [{
              type: 'integer',
              prompt: 'Please enter an integer value'
            }]
          },
          signalstrength: {
            identifier: 'signal_strength',
            rules: [{
              type: 'integer[0..100]',
              prompt: 'Please enter an integer value from 0 to 100'
            }]
          },
          flowrate: {
            identifier: 'flow_rate',
            rules: [{
              type: 'decimal',
              prompt: 'Please enter a valid decimal'
            }]
          }
        }
      });

    });
  </script>
  </body>

  </html>