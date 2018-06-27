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
              TESTER = document.getElementById('graph');
              var attempted = -1;
              var answered = -1;
              var arr = new Array();
              var ans = new Array();
              for(var i=0;i<data.length;i++) {
                if(data[i].verb=="attempted") {
                  attempted = (new Date(data[i].time).getTime()/1000);
                }
                else if(data[i].verb=="interacted") {
                  arr.push(new Date(data[i].time).getTime()/1000);
                }
                else if(data[i].verb=="answered") {
                  answered = (new Date(data[i].time).getTime()/1000);
                }
              }
    
              var xAxis = new Array();
              var yAxis = new Array();
              var xAxis1 = new Array();
              var yAxis1 = new Array();
              var xAxis2 = new Array();
              var yAxis2 = new Array();
              var xAxis3 = new Array();
              var yAxis3 = new Array();
              if(answered==-1 && arr.length==0){
                alert("Only Attempted");
              }
              else if(answered==-1 && arr.length!=0){
                alert("Attempted and Interacted");
              }
              else if(arr.length==0){
                alert("Attempted and Answered");
              }
              else{
                xAxis.push(0);
                xAxis.push(arr[0]-attempted);
                yAxis.push(1);
                yAxis.push(1);
                xAxis.push(arr[0]-attempted);
                yAxis.push(2);
                var len = xAxis.length;
                for(var i=0;i<arr.length-1;i++)
                {
                  xAxis.push(arr[i+1]-arr[i]+xAxis[len-1]+i);
                  yAxis.push(2);
                }
                len = xAxis.length;
                xAxis.push(answered-arr[arr.length-1]+xAxis[len-1]);
                yAxis.push(3);
                for(var i=0;i<xAxis.length;i++) {
                  if(yAxis[i]==1) {
                      xAxis1.push(xAxis[i]);
                      yAxis1.push(yAxis[i]);
                  }
                  else if(yAxis[i]==2) {
                      xAxis2.push(xAxis[i]);
                      yAxis2.push(yAxis[i]);
                  }
                  else if(yAxis[i]==3) {
                    if(xAxis3.length==0) {
                      xAxis2.push(xAxis[i]);
                      yAxis2.push(2);
                    }
                    xAxis3.push(xAxis[i]);
                    yAxis3.push(yAxis[i]);
                  }
                }
              }
              
              trace1 = {
                x: xAxis1,
                y: yAxis1, 
                line: {
                  color: 'rgba(214,39,40,1)', 
                  shape: 'hv'
                }, 
                mode: 'lines', 
                name: 'attempted', 
                type: 'scatter', 
                xaxis: 'x', 
                yaxis: 'y'
              };
              trace2 = {
                x: xAxis2,
                y: yAxis2, 
                line: {
                  color: 'rgba(0,0,128,1)', 
                  shape: 'hv'
                }, 
                mode: 'lines', 
                name: 'interacted', 
                type: 'scatter', 
                xaxis: 'x', 
                yaxis: 'y'
              };
              trace3 = {
                x: xAxis3,
                y: yAxis3, 
                line: {
                  color: 'rgba(0,128,0,1)', 
                  shape: 'hv'
                }, 
                mode: 'markers', 
                name: 'answered', 
                type: 'scatter', 
                xaxis: 'x', 
                yaxis: 'y',
                marker: { size: 12 }
              };

              data = [trace1,trace2,trace3];
              layout = {
                dragmode: 'zoom', 
                margin: {
                  r: 10, 
                  t: 25, 
                  b: 40, 
                  l: 60
                }, 
                showlegend: true, 
                xaxis: {
                  domain: [0, 1], 
                  title: 'x'
                }, 
                yaxis: {
                  domain: [0, 1], 
                  title: 'y'
                }
              };
              Plotly.newPlot(TESTER, {
                data: data,
                layout: layout
              });
            }
          });
        }
      });
    }    
  }
})(jQuery);