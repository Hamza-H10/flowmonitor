String.prototype.ucwords = function() {
    return this.toLowerCase().replace(/\b[a-z]/g, function(letter) {
        return letter.toUpperCase();
    });
}

class Tabulation  {

    mSearchFunc() {
        this.search = document.getElementById(this.prefix+"_search").value;
        this.search = this.search.trim();
        document.getElementById(this.prefix+"_clear_btn").style.visibility = "visible";
        document.getElementById(this.prefix+"_search_btn").style.visibility = "hidden";
        this.loadPage(1, true);        
    }

    mClearSearchFunc() {
        var searchBtn = document.getElementById(this.prefix+"_search_btn");
        searchBtn.style.visibility = "visible";
        this.search = '';
        document.getElementById(this.prefix+"_clear_btn").style.visibility = "hidden";
        document.getElementById(this.prefix+"_search").value = "";
        this.loadPage(1, true);        
    }

    mDelFunc() {
        var els = document.getElementsByName(this.prefix+"_check_list[]");
        var ids = '', cnt = 0;
        for(var i = 0; i < els.length; ++i) {
            if(els[i].checked) {
                if(cnt)
                    ids = ids + ',';
                ids = ids + els[i].value;
                cnt++;
            }
        }
        this.loadPage(0,true,this.del_url+ids+"&pgno=");
    }

    mSingleDelFunc(rowid) {
        this.loadPage(0,true,this.del_url+rowid+"&pgno=");
    }

    mEditFunc(rowid) {
        var self = this;
        var close_button_text="<i class='close icon' onclick=\"$('#"+self.prefix+"_error_message').transition('fade');\"></i>";
        self.showForm();
        //console.log(rowid);
        $.get(self.fetch_url+rowid).done( 
            function (response) {
              var myObj = JSON.parse(response);
              //var key_names;
              if(myObj.status === "success") {
                  // do something with response.message or whatever other data on success

                  $('#'+self.prefix+'_userform').form('set values', myObj);
                  self.edit_id = rowid;
                  location.href = '#'+self.prefix+'_user_form_target';
              } else if(myObj.status === "error") {
                  $("#"+self.prefix+"_error_message").removeClass("hidden").addClass("visible");
                  $("#"+self.prefix+"_error_message").html(close_button_text+myObj.message);
              }
          }).fail(function(){
              $("#"+self.prefix+"_error_message").removeClass("hidden").addClass("visible");
              $("#"+self.prefix+"_error_message").html(close_button_text+'Record could not be fetched.');
          });
  
    }

    resetForm() {
        var self = this;
        $('#'+self.prefix+'_userform').form('reset');
        self.edit_id = 0;
    }

    sendData(e) {
        var self = this;
        var close_button_text="<i class='close icon' onclick=\"$('#"+self.prefix+"_error_message').transition('fade');\"></i>";
        var post_url = self.add_url;
  
        $("#"+self.prefix+"_error_message").html('');
        $("#"+self.prefix+"_error_message").removeClass("visible").addClass("hidden");
        if( $('.ui.form').form('is valid')) {
              // form is valid (both email and name)
            //console.log("ID:"+self.edit_id);
            if(self.edit_id)
                post_url = self.edit_url+self.edit_id;
            $.post(post_url, 
            $( "#"+self.prefix+"_userform" ).serialize()).done( 
                function (response) {
                var myObj = JSON.parse(response);
                if(myObj.status === "success") {
                    // do something with response.message or whatever other data on success
                    //console.log("Success:"+myObj.message);
                    $('#'+self.prefix+'_userform').form('reset');
                    self.loadPage(0, (self.edit_id == 0));
                    if(self.edit_id) self.hideForm();
                    self.edit_id = 0;
                    
                } else if(myObj.status === "error") {
                    // do something with response.message or whatever other data on error
                    $("#"+self.prefix+"_error_message").removeClass("hidden").addClass("visible");
                    $("#"+self.prefix+"_error_message").html(close_button_text+myObj.message);
                    //console.log("Error:"+myObj.message);
                }
            }).fail(function(){
                $("#"+self.prefix+"_error_message").removeClass("hidden").addClass("visible");
                $("#"+self.prefix+"_error_message").html(close_button_text+'Record could not be added.');
            });
  
        }
        e.preventDefault();
    }
  

    check(cb, type){
        var all = document.getElementById(this.prefix+"_all");
        var els = document.getElementsByName(this.prefix+"_check_list[]");

        if (type == 2) {
            for(var i = 0; i < els.length; ++i)
                els[i].checked = cb.checked;
        } 
        else if( type == 1 && cb.checked == false) {
            all.checked = false;
        }

        var one_checked = false;
        var all_checked = true;

        for(var i = 0; i < els.length; ++i) {
            if(els[i].checked)
                one_checked = true;
            else
                all_checked = false; 
        }
        if(all_checked) all.checked = true;
        document.getElementById(this.prefix+"_delete").disabled = !one_checked;
    }
    
    showForm() {
        $("#"+this.prefix+"_user_form1").show(); 
        $("#"+this.prefix+"_form_show").hide(); 
        $("#"+this.prefix+"_form_hide").show();
    }

    hideForm() {
        $("#"+this.prefix+"_user_form1").hide(); 
        $("#"+this.prefix+"_form_show").show(); 
        $("#"+this.prefix+"_form_hide").hide();
    }

    loadPage(pgno, reset=false, access_url='') {
        var search_text = '';
        if(this.search != '')
            search_text = "&search=" + this.search;
        if(access_url != '')
            this.load_api_url = access_url;

        access_url = this.load_api_url + (pgno?pgno:this.cur_page_num) + search_text; 

        var self = this;

        $.get(access_url, function(data, status) {
            if(status == 'success') {

                var myObj = JSON.parse(data);
                var table_output = '';
                if(self.loader_function)
                    table_output = self.loader_function(myObj, self.prefix);
                else {
                    var key_names;
                    var className = ''; 
                    let re = /_/g;
                    var table_output = "<table class='ui celled compact striped teal table'><thead><tr>";

                    if(self.opt_select_multi)
                        table_output += "<th class='center aligned'><div class='ui fitted checkbox'><input type='checkbox' id='" + self.prefix + "_all' onChange=\"" + self.prefix + ".check(this, 2)\"><label></label></div></th>";
                    if(myObj.records.length > 0) {
                        key_names = Object.keys(myObj.records[0]);
                        for(var ai = 1; ai < key_names.length; ai++) {
                            if(myObj.text_align)
                                className = " class='"+myObj.text_align[ai]+" aligned'";

                            table_output += "<th"+className+">" + key_names[ai].replace(re, ' ').ucwords() + "</th>";
                        }
                    }
                    if(self.opt_edit || self.opt_delete)
                        table_output += "<th class='center aligned'>Action</th>";
                    table_output += "</tr></thead><tbody>";
                    $.each(myObj.records, function (val) {
                        className = ''; 
                        
                        table_output += "<tr>";
                        
                        if(self.opt_select_multi)
                            table_output += "<td class='center aligned'><div class='ui fitted checkbox'><input type='checkbox' name='" + self.prefix + "_check_list[]' value='" + myObj.records[val].row_id + "' onChange=\"" + self.prefix + ".check(this, 1)\"><label></label></div></td>";
                        for(var inx = 1; inx < key_names.length; inx++) {
                            if(myObj.text_align)
                                className = " class='"+myObj.text_align[inx]+" aligned'";
                            table_output += "<td"+className+">"+myObj.records[val][key_names[inx]]+"</td>";
                        }
                        if(self.opt_edit || self.opt_delete) {
                            table_output += "<td class='center aligned'>";
                            if(self.opt_edit)
                                table_output += "<button class='ui circular small inverted blue icon button' onClick=\"" + self.prefix + ".mEditFunc(" + myObj.records[val].row_id + ")\"><i class='edit icon'></i></button>";
                            if(self.opt_delete)
                                table_output += "<button class='ui circular small inverted red icon button' onClick=\"" + self.prefix + ".mSingleDelFunc(" + myObj.records[val].row_id + ")\"><i class='trash icon'></i></button>";
                            table_output += "</td>";
                        }
                        table_output += "</tr>";
                    });
                    table_output += "</tbody></table>";

                }

                $('#'+self.prefix+'_datawindow').html(table_output);
        
                if(self.paging) {
                    $('#'+self.prefix+'_info').html(String(myObj.page_start+1) + ' to ' + String(myObj.page_start+myObj.page_limit) + ' of ' + String(myObj.total_records) + ' records');

                    self.cur_page_num = myObj.cur_page; 
                    if(reset) {
                        var step_size = $(window).width() < 750 ? 1 : 3;
                        Pagination.Init(document.getElementById(self.prefix+'_pagination'), {
                            size: myObj.total_pages, // pages size
                            page: myObj.cur_page,  // selected page
                            step: step_size,   // pages before and after current
                            myFunc: function(pgno) {self.loadPage(pgno);}
                        });
                    }
                }
                if(self.opt_select_multi)
                    document.getElementById(self.prefix+"_delete").disabled = true;
            }
            else {
                // error message print
            }
        }).fail(function() {
            var table_output, myObj;
            if(self.loader_function)
                table_output = self.loader_function(myObj, self.prefix);
            else
                table_output = "<table class='ui celled compact striped teal table'><tr><td class='center aligned'>No records found</td></tr></table>";

            $('#'+self.prefix+'_datawindow').html(table_output);
        });  

    }
    
    constructor(parameters) {

        var options = {
            prefix: "table1",
            apiUrl: "",
            addUrl: "",
            delUrl: "",
            editUrl: "",
            fetchUrl: "",
            selectMulti: false,
            edit: true,
            delete: true,
            paging: true,
            loaderFunction: null
        };
        for (var property in parameters) { 
            options[property] = parameters[property]; 
        }

        this.prefix = options.prefix;
        this.load_api_url = options.apiUrl;
        this.add_url = options.addUrl;
        this.del_url = options.delUrl;
        this.edit_url = options.editUrl;
        this.fetch_url = options.fetchUrl;
        this.cur_page_num = 1;
        this.opt_select_multi = options.selectMulti;
        this.opt_edit = options.edit;
        this.opt_delete = options.delete;
        this.paging = options.paging;
        this.loader_function = options.loaderFunction;
        this.search = '';
        this.edit_id = 0;

        var self = this;
        if (document.getElementById(self.prefix+"_clear_btn"))
            document.getElementById(self.prefix+"_clear_btn").style.visibility = "hidden";
        if($("#"+self.prefix+"_user_form1"))
            $("#"+self.prefix+"_user_form1").hide();
        if($("#"+self.prefix+"_form_hide"))
            $("#"+self.prefix+"_form_hide").hide(); 

        if(document.getElementById(self.prefix+"_form_show"))
            document.getElementById(self.prefix+"_form_show").addEventListener('click', function() { self.showForm(); });
        if(document.getElementById(self.prefix+"_form_hide"))
            document.getElementById(self.prefix+"_form_hide").addEventListener('click', function() { self.hideForm(); });
        if(document.getElementById(self.prefix+"_delete"))
            document.getElementById(self.prefix+"_delete").addEventListener('click', function() { self.mDelFunc(); });
        if(document.getElementById(self.prefix+"_clear_btn"))
            document.getElementById(self.prefix+"_clear_btn").addEventListener('click', function() { self.mClearSearchFunc(); });
        if(document.getElementById(self.prefix+"_search_btn"))
            document.getElementById(self.prefix+"_search_btn").addEventListener('click', function() { self.mSearchFunc(); });
        if(document.getElementById(self.prefix+"_userform"))
            document.getElementById(self.prefix+"_userform").addEventListener('submit', function(e) { table1.sendData(e); });
        if(document.getElementById(self.prefix+"_userform_cancel"))
            document.getElementById(self.prefix+"_userform_cancel").addEventListener('click', function(e) { table1.resetForm(); });

    }


}