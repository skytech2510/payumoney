"use strict";

$(window).on("load", function () {
  if (addonsNumber > 0) {
    $('#addons-form-container').css("display", "none");
  } else {
    $('#addons-form-container').css("display", "block");
  }
});

let c=1;
document.getElementById('upload-btn').onclick=function(){
    let x = document.getElementById("addons-form-container");
    if(c===1){
        x.style.display = "block";
        c=0;
    }
    else{
        x.style.display = "none";
        c=1;
    }
};

var close = document.getElementsByClassName("addon-alert-closebtn");
var i;

for (i = 0; i < close.length; i++) {
  close[i].onclick = function(){
    var div = this.parentElement;
    div.style.opacity = "0";
    setTimeout(function(){ div.style.display = "none"; }, 600);
  }
}

 