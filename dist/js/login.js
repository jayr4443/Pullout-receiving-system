$(document).ready(function () {
  // Handle form submission
  $('#loginForm').on('submit', function(e){
    e.preventDefault();
    $('#loginError').hide();

    const username = $('#username').val().trim();
    const password = $('#password').val().trim();

    $.ajax({
      type: 'POST',
      url: './includes/query.php',
      data: {
        action:   'login',
        username: username,
        password: password
      },
      success: function(res){
        const json = JSON.parse(res);

        if (json.status_code === '200' && json.data.length) {
          // grab the single user object
          const user = json.data[0];

          // save to localStorage
          localStorage.setItem('userId',       user.id);
          localStorage.setItem('userFname',    user.fname);
          localStorage.setItem('userUsername', user.username);
          localStorage.setItem('userLevel',    user.userlevel);

          // redirect to your app
          window.location.href = 'structure.php?page=Transaction';
        } else {
          // show the server-sent message
          $('#loginError').text(json.status_msg).show();
          $('#username').val("");
          $('#password').val("");
        }
      },
      error: function(){
        $('#loginError').text('Server error, please try again later.').show();
      }
    });
  });
});
