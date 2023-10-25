  <link rel="stylesheet" type="text/css" href="css/semantic.min.css">
  <script
  src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
  integrity="sha256-pasqAKBDmFT4eHoN2ndd6lN370kFiGUFyTiUHWhU7k8="
  crossorigin="anonymous"></script>

  <style type="text/css">
    body {
      background-color: #ffffff; /* #DADADA */
      background-image: url('images/bgteal2.jpg');
    }
    body > .grid {
      height: 100%;
    }
    .image {
      margin-top: -100px;
    }
    .column {
      max-width: 450px;
    }
  </style>
  <script>
  $(document)
    .ready(function() {
      $('.ui.form')
        .form({
          fields: {
            email: {
              identifier  : 'username',
              rules: [
                {
                  type   : 'empty',
                  prompt : 'Please enter your username'
                },
                {
                  type   : 'length[5]',
                  prompt : 'Username must be at least 5 characters'
                }
              ]
            },
            password: {
              identifier  : 'password',
              rules: [
                {
                  type   : 'empty',
                  prompt : 'Please enter your password'
                },
                {
                  type   : 'length[5]',
                  prompt : 'Your password must be at least 5 characters'
                }
              ]
            }
          }
        })
      ;
    })
  ;
  </script>
</head>
<body>

<div class="ui middle aligned center aligned grid">
 
  <div class="column">
    <div class="ui inverted segment">
      <b>DATA DIGGER EQUIPMENT</b>
    </div>
    <div class="ui raised segment">
    <p>A user friendly solution for Automation, Safety and Security for Structural,
Environmental and Geo-Technical Instrumentation. The Instruments are based on established Vibrating Wire Technology and MEMS technology...</p>
    </div>

    <h2 class="ui black image header">
      <img src="images/favicon-96x96.png" class="image">
      <div class="content">
        DDE Account Log-In
      </div>
    </h2>
    <form class="ui large form" method="POST">
      <div class="ui stacked segment">
        <div class="field">
          <div class="ui left icon input">
            <i class="user icon"></i>
            <input type="text" name="username" placeholder="Username">
          </div>
        </div>
        <div class="field">
          <div class="ui left icon input">
            <i class="lock icon"></i>
            <input type="password" name="password" placeholder="Password">
          </div>
        </div>
        <input type="hidden" name="action" value="login"> 
        <div class="ui fluid large black submit button">Login</div>
      </div>

      <div class="ui error message"></div>

    </form>

    <div class="ui message">
      Forgot Password? <a href="#">Click here</a>
    </div>
  </div>

</div>

<script src="js/semantic.min.js"></script>
</body>

</html>
