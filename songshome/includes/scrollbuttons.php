<!DOCTYPE html>
<html>

<a id="scrollToTopButton"></a>
<a id="scrollToBottomButton"></a>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script>
var scrlTopBtn = $('#scrollToTopButton');
var scrlBotBtn = $('#scrollToBottomButton');

jQuery(document).ready(function() {

    function displayButtons(){

        var scrollBottom = $(window).scrollTop() + $(window).height();

        if ($(document).scrollTop() < 300) {
            if (scrlTopBtn.hasClass('show') || !scrlBotBtn.hasClass('show')){       

                scrlTopBtn.removeClass('show');

                scrlBotBtn.addClass('show');
                
            }
        }       
        else if ($(document).scrollTop() >=  300 && scrollBottom <= $(document).height() - 300) {
            if (!scrlTopBtn.hasClass('show') || !scrlBotBtn.hasClass('show')){ 

                scrlTopBtn.addClass('show');
                scrlBotBtn.addClass('show');  
                
            };    
        } 
        else {
            if (!scrlTopBtn.hasClass('show') || scrlBotBtn.hasClass('show')){

                scrlTopBtn.addClass('show');

                scrlBotBtn.removeClass('show');          
              
               
            }
           
        }

    };
    
    function getScrollType(){
        var body = document.body, html = document.documentElement;
        var height = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );
        
        var scrollType = 'auto';
        if(height > 7000){
            scrollType = 'auto';
        }
        else {
            scrollType = 'smooth';
        }

        return scrollType;
    }

    // SCROLL PLACEHOLDERS CODE
    // function getNextScrollLocation(){
       
    // }

    // if  (document.getElementById("scrollPlaceholder")) {
    //     alert($(document.getElementById("scrollPlaceholder")).height());
    //     alert($(document).height());
    // }
  

    if ($(window).height() < $(document).height()) {
    
        displayButtons();

        window.setInterval(function(){      
            displayButtons();   
        }, 1500);

        $(document).scroll(function() {    
            displayButtons();
        });
    }
    

    


   

    scrlTopBtn.on('click', function(e) {
        e.preventDefault();
        
        var scrollType = getScrollType();
        // window.scrollTo(0, 0);   
         window.scrollTo({
            top: 0,
            left: 0,
            behavior: scrollType
         });
          
    });

    // scrlTopBtn.on('touchstart', function(e) {
    //     alert();
    // }

    scrlBotBtn.on('click', function(e) {
        var bottomPage =  $(document).height() - $(window).height();
        e.preventDefault();
      
        // window.scrollTo(0, bottomPage);    
        var scrollType = getScrollType();
        window.scrollTo({
            top: bottomPage,
            left: 0,
            behavior: scrollType
        });
    });

});

</script>

</html>

