<!DOCTYPE html>


</div>

<script>
   
var open = 0;
var timeout = false;
function openNav() {
    document.getElementById("mySidenav").style.height = "calc(170px + 7vw)";
    document.getElementById("navMenuDetectContent").style.marginTop = "calc(170px + 7vw)";

    if (open){
     
        closeNav();
        
        open = 0;
    }
    else {
        setTimeout(function(){ open = 1; document.getElementById("hamburgerIcon").classList.add("is-active"); }, 10);
    }
}

function hoverNav() {
   
    event.preventDefault();
    if (!open){
        document.getElementById("hamburgerIcon").classList.add("is-active"); 
        document.getElementById("mySidenav").style.height = "calc(170px + 7vw)";
        document.getElementById("navMenuDetectContent").style.marginTop = "calc(170px + 7vw)";

        if (!timeout){
            timeout = true;
            setTimeout(function(){  document.getElementById("hamburgerIcon").classList.add("is-active"); 
            open = 1; timeout = false;
            }, 500);
        }
        
    }

}


function closeNav() {

        document.getElementById("mySidenav").style.height = "0";
        document.getElementById("navMenuDetectContent").style.marginTop = "0";
        document.getElementById("hamburgerIcon").classList.remove("is-active");  
        open = 0;
    
}

var specifiedElement = document.getElementById('mySidenav');
var specifiedElementBar = document.getElementById('navMenuBarCont');
var specifiedElementIcon = document.getElementById('hamburgerIcon');

//Close the navBar when clicked outside of it.
document.addEventListener('click', function(event) {
   
    var isClickInside = (specifiedElement.contains(event.target) || specifiedElementBar.contains(event.target));

    if (!isClickInside) { 
        if(open){

            closeNav();
        }

    }
});


//Add navBar hover controls if not on mobile.
var isMobile = /Android|iPhone|iPad|iPod|BlackBerry|Opera Mini|WPDesktop|IEMobile/i.test(navigator.userAgent);
if (!isMobile) {
    document.getElementById("hamburgerIcon").onmouseover = function() {hoverNav();};

    document.addEventListener('mouseover', function(event) {

        if(open){
        
            var isClickInside = (specifiedElement.contains(event.target) || specifiedElementBar.contains(event.target));
            if (!isClickInside ) { 
                    
                    closeNav();
            
            //the click was outside the specifiedElement, do something
            }
            else if (specifiedElementIcon.contains(event.target)){
                document.getElementById("mySidenav").style.height = "0";
                document.getElementById("navMenuDetectContent").style.marginTop = "0";
                document.getElementById("hamburgerIcon").classList.remove("is-active");  

                setTimeout(function(){  document.getElementById("hamburgerIcon").classList.remove("is-active"); 
                    open = 0; 
                }, 500);
        
            }

        }
    });

}
</script>