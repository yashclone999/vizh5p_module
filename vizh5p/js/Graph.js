(function($) {
  Drupal.behaviors.myBehavior = {
    attach: function (context, settings) {
      Graph = document.getElementById('graph');
      $('[name="attempt"]').once().on('change', function() {
        var content_id = $('[name="content_id"]').val().trim();
        var username = $('[name="username"]').val().trim();
        var attempt = $('[name="attempt"]').val().trim();
        if(content_id != '' && username != '' && attempt != '') {
          var obj = {};
          obj['content_id'] = content_id;
          obj['username'] = username;
          obj['attempt'] = attempt;
          obj = JSON.stringify(obj);
          $.ajax({
            type: "POST",
            url: "getjson" ,
            data: obj,
            contentType: "application/json",
            success: function(data) {
              var x = ["Time Taken to Interact","Interaction Time","Number of Interactions","Time Taken to Answer"]
              var plotdata = [
                {
                  histfunc: "sum",
                  y: data[0],
                  x: x,
                  type: "histogram",
                  name: "Average of all users"
                },
                {
                  histfunc: "sum",
                  y: data[1],
                  x: x,
                  type: "histogram",
                  name: "User's average"
                },
                {
                  histfunc: "sum",
                  y: data[2],
                  x: x,
                  type: "histogram",
                  name: "Current attempt"
                }
              ]
              Plotly.newPlot('graph', plotdata)  
            }
          });
        }
      });
    }    
  }
})(jQuery);