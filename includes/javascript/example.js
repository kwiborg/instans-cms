  function resizeDivs(){
   OBJ = document.getElementById("contentWrapper");
   H = OBJ.offsetHeight;
   if(H < 500) {
    OBJ.style.height = "500" + "px";
   }
  }