<?php
$u_id = getValue('user_id', false, 0);
$database = new Database();
$stmt = $database->execute("SELECT  display_name, user_email, user_type FROM users WHERE id=" . $u_id);

// retrieve our table contents
if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $username = $row["display_name"];
    $user_type = ($row['user_type'] == 1) ? "Admin" : "User";
}
?>
<div class="ui main container">

    <!-- DATA FORM -->
    <h3 class="ui center aligned header">
        <?php if ($u_id) echo "Device Manager for $username ($user_type)";
        else echo "Device Manager for ALL"; ?>
    </h3>
    <a name="table1_user_form_target"></a>
    <h3 class="ui top attached header compact">
        Device Details
        <span>

            <button class="ui teal mini icon right floated button" id="table1_form_show">
                ADD &nbsp;
                <i class="add alternate icon"></i>
            </button>
            <button class="ui circular negative mini icon right floated button" id="table1_form_hide">
                <i class="close alternate icon"></i>
            </button>
        </span>
    </h3>

    <div class="ui teal attached segment compact" id="table1_user_form1">
        <form name="table1_userform" id="table1_userform" class="ui form">
            <div class="ui negative message transition hidden" id="table1_error_message"></div>
            <div class="two fields">
                <div class="field">
                    <label>Device Name</label>
                    <input type="text" name="device_name" placeholder="Device Name">
                </div>
                <div class="field">
                    <label>Device Number</label>
                    <input type="text" name="device_number" placeholder="Device Number">
                </div>
            </div>


            <div class="three fields">
                <div class="field">
                    <label>Device Type</label>
                    <div class="ui four column wide selection dropdown">
                        <input type="hidden" name="device_type" id="device_type">
                        <i class="dropdown icon"></i>
                        <div class="default text">Device Type</div>
                        <div class="menu">
                            <div class="item" data-value="1">UFM</div>
                            <div class="item" data-value="3">EM</div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label>Device X</label>
                    <input type="text" name="device_x" placeholder="">
                </div>
                <div class="field">
                    <label>Device Y</label>
                    <input type="text" name="device_y" placeholder="">
                </div>
            </div>

            <div class="two fields">
                <div class="field">
                    <label>User Link</label>
                    <div class="ui four column wide selection dropdown">
                        <?php
                        echo "<input type='hidden' name='user_id' id='user_id' value='$u_id'>  <i class='dropdown icon'></i> <div class='default text'>User</div> <div class='menu'>";
                        echo "<div class='item' data-value='0'>None</div>";
                        $stmt = $database->execute("SELECT id, display_name, user_type FROM users order by display_name");

                        // retrieve our table contents
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $username = $row["display_name"];
                            $user_id = $row["id"];
                            $user_type = ($row['user_type'] == 1) ? "Admin" : "User";
                            echo "<div class='item' data-value='$user_id'>$username ($user_type)</div>";
                        }

                        ?>
                    </div>
                </div>
            </div>
    </div>

    <button class="ui button" type="submit" id="table1_userform_submit">Submit</button>
    <button class="ui button red" type="reset" id="table1_userform_cancel">Reset</button>
    <div class="ui error message"></div>
    </form>
</div>

<div class="ui horizontal divider header">Device List</div>

<!-- DATA TABLE -->
<div class="ui grid ">
    <div class="eleven wide column">
        <button class="ui circular negative icon button" id="table1_delete">
            <i class="trash alternate icon"></i>
        </button>
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
</div>
</div>

<script src="js/jquery-3.3.1.min.js"></script>
<script src="js/semantic.min.js"></script>
<script src="js/pagination.js"></script>
<script src="js/tabulation.js"></script>
<script>
    var table1 = new Tabulation({
        apiUrl: "<?= $app_root ?>/api/?function=list_ufm&user_id=<?= $u_id ?>&pgno=",
        addUrl: "<?= $app_root ?>/api/?function=devices_add",
        delUrl: "<?= $app_root ?>/api/?function=devices_delete&user_id=<?= $u_id ?>&del_id=",
        editUrl: "<?= $app_root ?>/api/?function=devices_edit&row_id=",
        fetchUrl: "<?= $app_root ?>/api/?function=devices_fetch&row_id=",
        selectMulti: true,
        paging: true,
        loaderFunction: loadPageFunction
    });

    $(function() {
        $('.selection.dropdown').dropdown();
        $('.ui.checkbox').checkbox();
        $('.ui.indicating.progress').progress();

        table1.loadPage(1, true, '');
        $('.ui.form').form({
            fields: {
                type: {
                    identifier: 'device_type',
                    rules: [{
                        type: 'empty',
                        prompt: 'Please select a device type'
                    }]
                },
                devicename: {
                    identifier: 'device_name',
                    rules: [{
                        type: 'empty',
                        prompt: 'Please enter a user device name'
                    }]
                },
                devicenumber: {
                    identifier: 'device_number',
                    rules: [{
                        type: 'number',
                        prompt: 'You must enter a valid device number'
                    }]
                }
            }
        });

    });

    function loadPageFunction(myObj, table_name) {
        var key_names;
        var className = '';
        var numb = 0;
        var offline = 0;
        let re = /_/g;
        var table_output = "<table class='ui celled compact striped teal table'><thead><tr>";

        table_output += "<th class='center aligned'><div class='ui fitted checkbox'><input type='checkbox' id='" + table_name + "_all' onChange=\"" + table_name + ".check(this, 2)\"><label></label></div></th>";
        if (myObj.records.length > 0) {
            key_names = Object.keys(myObj.records[0]);
            for (var ai = 1; ai < key_names.length; ai++) {
                if (myObj.text_align)
                    className = " class='" + myObj.text_align[ai] + " aligned'";
                if (key_names[ai] != "device_type")
                    table_output += "<th" + className + ">" + key_names[ai].replace(re, ' ').ucwords() + "</th>";
            }
        }
        table_output += "<th class='center aligned'>Action</th>";
        table_output += "</tr></thead><tbody>";
        $.each(myObj.records, function(val) {
            className = 'positive';

            if (myObj.records[val][key_names[1]] === "OFFLINE") {
                className = 'negative';
                offline++;
            }
            table_output += "<tr class='" + className + "'>";

            table_output += "<td class='center aligned'><div class='ui fitted checkbox'><input type='checkbox' name='" + table_name + "_check_list[]' value='" + myObj.records[val].row_id + "' onChange=\"" + table_name + ".check(this, 1)\"><label></label></div></td>";
            for (var inx = 1; inx < key_names.length; inx++) {
                if (myObj.text_align)
                    className = " class='" + myObj.text_align[inx] + " aligned'";
                if (key_names[inx] == "signal_strength") {
                    if (myObj.records[val]["device_type"] == "UFM")
                        table_output += "<td" + className + ">" + "<div class='ui indicating progress' data-percent='" + (myObj.records[val][key_names[inx]] == 0 ? 2 : myObj.records[val][key_names[inx]]) + "' id='progress" + numb + "'><div class='bar'></div><div class='label'>" + myObj.records[val][key_names[inx]] + "</div></div>" + "</td>";
                    else
                        table_output += "<td" + className + ">ElectroMagnetic</td>";
                } else if (key_names[inx] != "device_type")
                    table_output += "<td" + className + ">" + myObj.records[val][key_names[inx]] + "</td>";
            }
            table_output += "<td class='center aligned'>";
            table_output += "<button class='ui circular small inverted blue icon button' onClick=\"" + table_name + ".mEditFunc(" + myObj.records[val].row_id + ")\"><i class='edit icon'></i></button>";
            table_output += "<button class='ui circular small inverted red icon button' onClick=\"" + table_name + ".mSingleDelFunc(" + myObj.records[val].row_id + ")\"><i class='trash icon'></i></button>";
            table_output += "</td>";

            table_output += "</tr>";
            numb++;
        });

        table_output += "</tbody></table><p><b>Total " + numb + " devices. &nbsp; " + offline + " Offline.</b></p>";
        $('#' + table_name + '_datawindow').html(table_output);

        if (numb > 0) numb--;

        while (numb >= 0) {
            $('#progress' + numb).progress();
            numb--;
        }

        //return(table_output);

    }
</script>
</body>

</html>