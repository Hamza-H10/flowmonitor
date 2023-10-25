<div class="ui main container">

<!-- DATA FORM -->
<a name="table1_user_form_target"></a>
<h3 class="ui top attached header compact">
  User Details
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
                <label>Display Name</label>
                <input type="text" name="display_name" placeholder="Display Name">
            </div>
            <div class="field">
                <label>Email</label>
                <input type="text" name="user_email" placeholder="User Email">
            </div>
        </div>


        <div class="two fields">
            <div class="field">
                <label>Password</label>
                <input type="password" name="user_password" placeholder="Password">
            </div>
            <div class="field">
                <label>Confirm Password</label>
                <input type="password" name="confirm-password" placeholder="Confirm Password">
            </div>
        </div>

        <div class="three fields">
            <div class="field">
                <label>User Type</label>
                <div class="ui four column wide selection dropdown">
                    <input type="hidden" name="user_type" id="user_type">
                    <i class="dropdown icon"></i>
                    <div class="default text">User Type</div>
                    <div class="menu">
                        <div class="item" data-value="1">Admin</div>
                        <div class="item" data-value="2">User</div>
                    </div>
                </div>
            </div>
            <div class="field">
                <label>Unit Flow</label>
                <input type="text" name="unit_flow" placeholder="">
            </div>
            <div class="field">
                <label>Unit Totalizer</label>
                <input type="text" name="unit_totalizer" placeholder="">
            </div>
        </div>
        <div class="field">
            <label>Remarks</label>
            <input type="text" name="remarks" placeholder="">
        </div>

        <button class="ui button" type="submit" id="table1_userform_submit">Submit</button>
        <button class="ui button red" type="reset" id="table1_userform_cancel">Reset</button>
        <div class="ui error message"></div>
    </form>
    </div>

    <div class="ui horizontal divider header">User List</div>
  <!-- DATA LIST -->

    <div class="ui grid ">
      <div class="eleven wide column">
        <!-- <h2 class="ui header">User List</h2> -->
        <button class="ui circular negative icon button" id="table1_delete" >
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
    var table1= new Tabulation({
            apiUrl: "<?=$app_root?>/api/?function=user_list&pgno=",
            addUrl:"<?=$app_root?>/api/?function=user_add",
            delUrl:"<?=$app_root?>/api/?function=user_delete&del_id=", 
            editUrl:"<?=$app_root?>/api/?function=user_edit&row_id=",
            fetchUrl:"<?=$app_root?>/api/?function=user_fetch&row_id=",
            selectMulti: true,
            });

    $(function() {
        $('.selection.dropdown').dropdown();
        $('.ui.checkbox').checkbox();

        //table1.init();
        table1.loadPage(1, true);

        $('.ui.form').form({
        fields: {
            type: {
                identifier: 'user_type',
                rules: [
                {
                    type   : 'empty',
                    prompt : 'Please select a user type'
                }
                ]
            },
            unit_flow: {
                identifier: 'unit_flow',
                rules: [
                {
                    type   : 'empty',
                    prompt : 'Please select a unit for displaying flow rate'
                }
                ]
            },
            unit_totalizer: {
                identifier: 'unit_totalizer',
                rules: [
                {
                    type   : 'empty',
                    prompt : 'Please select a unit for displaying totalizer value'
                }
                ]
            },
            username: {
                identifier: 'display_name',
                rules: [
                {
                    type   : 'empty',
                    prompt : 'Please enter a user display name'
                }
                ]
            },
            // password: {
            //     identifier: 'user_password',
            //     rules: [
            //     {
            //         type   : 'empty',
            //         prompt : 'Please enter a password'
            //     },
            //     {
            //         type   : 'minLength[6]',
            //         prompt : 'Your password must be at least {ruleValue} characters'
            //     }
            //     ]
            // },
            confirmpass: {
                identifier: 'confirm-password',
                rules: [
                    {
                        type   : 'match[user_password]',
                        prompt : 'Passwords do not match'
                    }
                ]
            },
            email: {
                identifier: 'user_email',
                rules: [
                {
                    type   : 'email',
                    prompt : 'You must enter a valid email'
                }
                ]
            }
          }
        });

    });
  </script>
</body>  
</html>
