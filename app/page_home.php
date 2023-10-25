<div class="ui main container">
        <div class="ui grid">
            <div class="eleven wide column">
                <!-- Placeholder for a header or title (currently commented out) -->
                <h2 class="ui header">User List</h2>
                <!-- Placeholder for a delete button (currently commented out) -->
                <!-- <button class="ui circular negative icon button" id="table1_delete">
                <i class="trash alternate icon"></i>
                </button> -->
                    
            </div>

            <div class="five wide column right floated right aligned">
                <!-- Input field for searching with a clear and search icon -->
                <div class="ui icon input">
                    <input type="text" placeholder="Search..." id="table1_search">
                    <i class="circular delete link icon" id="table1_clear_btn"></i>
                    <i class="inverted circular search link icon" id="table1_search_btn"></i>
                </div>
            </div>

            <!-- Placeholder for tabulated data -->
            <div id="table1_datawindow" class="table_datawindow"></div>

            <!-- Placeholder for information content -->
            <!-- <div class="content" id="info"></div> -->

            <!-- Placeholder for pagination controls -->
            <div id="table1_pagination" class="eleven wide column"></div>

            <div class="five wide column right floated right aligned">
                <!-- Information display -->
                <h4 class="ui right floated">
                    <div class="content" id="table1_info"></div>
                </h4>
            </div>
        </div>
    </div>

    <!-- Include jQuery and other scripts -->
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/semantic.min.js"></script>
    <script src="js/pagination.js"></script>
    <script src="js/tabulation.js"></script>

    <script>
        // JavaScript code for tabulation
        var table1 = new Tabulation({
            apiUrl: "<?=$app_root?>/api/?function=list_ufm&pgno=1",
            fetchUrl: "<?=$app_root?>/api/?function=list_ufm&row_id=",
            selectMulti: false,
            delete: false,
            paging: false,
            loaderFunction: loadPageFunction
        });

        // Initialize Semantic UI components
        $(function() {
            $('.selection.dropdown').dropdown();
            $('.ui.checkbox').checkbox();
            $('.ui.indicating.progress').progress();

            // Initialize and load data with the table1 object
            // table1.init();
            table1.loadPage(1, false);
        });

        // Function to load data for the current page
        function loadPageFunction(pgno, reset = false, access_url = '') {
            var search_text = '';
            if (this.search !== '') {
                search_text = "&search=" + this.search;
            }
            if (access_url !== '') {
                this.load_api_url = access_url;
            }

            access_url = this.load_api_url + pgno + search_text;

            var self = this;

            $.get(access_url, function(data, status) {
                if (status == 'success') {
                    var myObj = JSON.parse(data);
                    var table_output = '';
                    var key_names;
                    var className = '';
                    var numb = 0;
                    var offline = 0;
                    let re = /_/g;
                    var table_output = "<table class='ui celled compact striped teal table'><thead><tr>";

                    if (self.opt_select_multi) {
                        table_output += "<th class='center aligned'><div class='ui fitted checkbox'><input type='checkbox' id='" + self.prefix + "_all' onChange=\"" + self.prefix + ".check(this, 2)\"><label></label></div></th>";
                    }

                    if (myObj.records.length > 0) {
                        key_names = Object.keys(myObj.records[0]);
                        for (var ai = 1; ai < key_names.length; ai++) {
                            if (myObj.text_align) {
                                className = " class='" + myObj.text_align[ai] + " aligned'";
                            }
                            if (key_names[ai] != "device_type") {
                                table_output += "<th" + className + ">" + key_names[ai].replace(re, ' ').ucwords() + "</th>";
                            }
                        }
                    }

                    table_output += "</tr></thead><tbody>";

                    $.each(myObj.records, function(val) {
                        className = 'positive';

                        if (myObj.records[val][key_names[1]] === "OFFLINE") {
                            className = 'negative';
                            offline++;
                        }

                        table_output += "<tr class='" + className + "'>";

                        if (self.opt_select_multi) {
                            table_output += "<td class='center aligned'><div class='ui fitted checkbox'><input type='checkbox' name='" + self.prefix + "_check_list[]' value='" + myObj.records[val].row_id + "' onChange=\"" + self.prefix + ".check(this, 1)\"><label></label></div></td>";
                        }

                        for (var inx = 1; inx < key_names.length; inx++) {
                            if (myObj.text_align) {
                                className = " class='" + myObj.text_align[inx] + " aligned'";
                            }
                            if (key_names[inx] == "signal_strength") {
                                if (myObj.records[val]["device_type"] == "UFM") {
                                    table_output += "<td" + className + ">" + "<div class='ui indicating progress' data-percent='" + (myObj.records[val][key_names[inx]] == 0 ? 2 : myObj.records[val][key_names[inx]]) + "' id='progress" + numb + "'><div class='bar'></div><div class='label'>" + myObj.records[val][key_names[inx]] + "</div></div>" + "</td>";
                                } else {
                                    table_output += "<td" + className + ">ElectroMagnetic</td>";
                                }
                            } else if (key_names[inx] != "device_type") {
                                table_output += "<td" + className + ">" + myObj.records[val][key_names[inx]] + "</td>";
                            }
                        }

                        table_output += "</tr>";
                        numb++;
                    });

                    table_output += "</tbody></table><p><b>Total " + numb + " devices. &nbsp; " + offline + " Offline.</b></p>";

                    $('#'+self.prefix+'_datawindow').html(table_output);

                    if (numb > 0) {
                        numb--;
                    }
                    while (numb >= 0) {
                        $('#progress' + numb).progress();
                        numb--;
                    }
                } else {
                    // error message print
                }
            }).fail(function() {
                var table_output, myObj;
                table_output = "<table class='ui celled compact striped teal table'><tr><td class='center aligned'>No records found</td></tr></table>";

                $('#'+self.prefix+'_datawindow').html(table_output);
            });
        }
    </script>
</body>
</html>
