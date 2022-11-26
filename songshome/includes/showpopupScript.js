var popupState = 0;
function showPopup(id) {

    var popup = document.getElementById(id);
    
    if (popupState === 0){
        popup.classList.toggle("show");
    }

    if (popupState === 0){
    popupState = 1;
    setTimeout(function(e){     
        popup.classList.remove('show');
        popupState = 0; 
        popup.classList.toggle("hide"); 
        }, 2000); 
    }
  
}