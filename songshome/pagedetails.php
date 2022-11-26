<?php error_reporting(0); session_start();?>
<!DOCTYPE html>
<html>
<head>
    <title>Music Collab. Environ. - Update Release</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" charset="utf-8">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

    


    <!-- <script src='https://unpkg.com/tesseract.js@v2.1.0/dist/tesseract.min.js'></script> SOURCE -->
    <!-- <script src='/songshome/includes/tesseract_v2.1.0.min.js'></script> -->
    <script src='/songshome/includes/tesseract_v3.0.3.min.js'></script>

    <script src="https://cdn.tiny.cloud/1/edbok2enytbi4yz7vbazy8aqukvbdqgdsf2iu8nqmvloegaw/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

    <script src="/songshome/image-conversion-master/build/conversion.js"></script>

    <link rel="stylesheet" href="/songshome/musicPlayer/dist/css/green-audio-player.css">
    <script src="/songshome/musicPlayer/dist/js/green-audio-player.js"></script>

    <link rel ="stylesheet" type="text/css" href="/songshome/site.css" >
    <link rel="icon" href="/songshome/imageNotFound.jpg" type="image/ico">

    <script>
    // tinymce.init({
    //   selector: 'textarea',
    //   plugins: 'a11ychecker advcode casechange linkchecker autolink lists checklist media mediaembed pageembed permanentpen powerpaste table advtable tinycomments tinymcespellchecker',
    //   toolbar: 'a11ycheck addcomment showcomments casechange checklist code pageembed permanentpen table',
    //   toolbar_mode: 'floating',
    //   tinycomments_mode: 'embedded',
    //   tinycomments_author: 'Author name', branding: false
    // });

    // Set language other that english for editor
    // tinymce.init({selector:'textarea', branding: 'false', language_url : 'tinymce/langs/ru.js',  language: 'ru'});
    
    // Set Menu Options
    // menu: {
    //     format: { title: "Format", items: "forecolor backcolor" }}
    // OR menubar: 'file edit insert view formattools help'

    //Add Color Controls to editor
    // toolbar: "forecolor backcolor"
    
    //Font Sizes
    // toolbar: 'fontsizeselect',
    // fontsize_formats: '11px 12px 14px 16px 18px 24px 36px 48px'
    

    // function getStats(id) {
    //     var body = tinymce.get(id).getBody(), text = tinymce.trim(body.innerText || body.textContent);

    //     return {
    //         chars: text.length,
    //         words: text.split(/[\w\u2019\'-]+/).length
    //     };
    // }
    
    function tinymce_updateCharCounter(el, len) {
    $('#' + el.id).prev().find('.char_count').text(len + '/' + el.settings.max_chars);
    }

    function tinymce_getContentLength() {
        return tinymce.get(tinymce.activeEditor.id).contentDocument.body.innerText.length;
    }

    // function getKeyCode(str) {
    //     return str && str.charCodeAt(0);    
    // }
    // function keyUp(){
    //     var keyCode = getKeyCode("1"); 
    //     return keyCode;
    // }

    tinymce.init({
        selector:'textarea', 
        branding: false, 
        elementpath: false, 
        plugins: "autoresize paste fullscreen print", 
        style_formats_merge: true,
        removed_menuitems: 'code', 
        content_style: "body { font-family: Times New Roman; text-shadow: 1px 1px 1px rgba(0,0,0,0.1); text-align: left; color: #000000; box-sizing: border-box; word-break:break-all;word-break:break-word;}", 
        content_style: "p {font-size: calc(7.15503px + 4.3912vw); margin: 0; font-family: Times New Roman; text-shadow: 1px 1px 1px rgba(0,0,0,0.1); text-align: left; color: #000000;}",
        paste_webkit_styles: "color font-size",
        placeholder:"Lyrics", 
        paste_as_text: true,
        // menu: {
        //     format: { title: "Format", items: "strikethrough superscript subscript" }
        // },
        menubar: 'file edit view',

        // | forecolor backcolor 
        toolbar: 'undo redo paste fullscreen | styleselect textformats aligntext | outdent indent | removeformat  | bold italic underline ',
  
        toolbar_groups: {
            // formatting: {
            //     icon: 'bold',
            //     tooltip: 'formatting',
            //     items: 'bold italic underline | superscript subscript'
            // },

            // textformats: {
            //     icon: 'format',
            //     tooltip: 'Text Customization',
            //     items: 'fontselect fontsizeselect'
            // },

            aligntext: {
                icon: 'align-center',
                tooltip: 'Align Text',
                items: 'alignleft aligncenter alignright alignnone'
            }
        },
        //toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent',
      
//   toolbar2: 'alignleft aligncenter alignright',
        // fontsize: 'Small=8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt',
        style_formats: [
            // { title: 'Headings', items: [
            //     { title: 'Title 1', format: 'h1' },
            //     { title: 'Title 2', format: 'h2' },
            //     { title: 'Heading 3', format: 'h3' },
            //     { title: 'Heading 4', format: 'h4' },
            //     { title: 'Heading 5', format: 'h5' },
            //     { title: 'Heading 6', format: 'h6' }
            // ]},
            { title: 'Inline', items: [
                { title: 'Bold', format: 'bold' },
                { title: 'Italic', format: 'italic' },
                { title: 'Underline', format: 'underline' },
                { title: 'Strikethrough', format: 'strikethrough' },
                { title: 'Superscript', format: 'superscript' },
                { title: 'Subscript', format: 'subscript' },
            
            ]},
            { title: 'Blocks', items: [
                { title: 'Paragraph', format: 'p' },
                { title: 'Blockquote', format: 'blockquote' },
            ]},
            { title: 'Align', items: [
                { title: 'Left', format: 'alignleft' },
                { title: 'Center', format: 'aligncenter' },
                { title: 'Right', format: 'alignright' },
                { title: 'Justify', format: 'alignjustify' }
            ]}
        ],
        
        
    style_formats_merge: false,


    // menubar: 'file edit insert view formattools help',
    contextmenu_never_use_native: false,
        resize: true,
        min_height:150,
        mobile: {
            max_height:350,
            menubar: true,
            menubar: 'file edit view',
            toolbar1: 'undo redo paste fullscreen | styleselect textformats | removeformat  ',
            toolbar2: '  alignleft aligncenter alignright alignnone | outdent indent | bold italic underline '
        },
        // mobile: {
        //     max_height:350,
        //     menubar: true,
        //     menubar: 'file edit view',
        //     toolbar1: 'undo redo paste | fontselect textformats alignleft aligncenter alignright alignnone ',
        //     toolbar2: ' styleselect fontsizeselect | outdent indent',
        //     toolbar3: 'fullscreen | removeformat | bold italic underline | forecolor backcolor'
        // },
        setup: function(editor) {
            
            //Set editor content
            // editor.on('init', function () {
            //     this.setContent('Using the on init stuff!');          
            // });



            // function cutTextLength(){
            //     // console.log(editor.getContent({format : 'text'}).replace('%MCEPASTEBIN%', ''));
            //     editor.setContent(editor.getContent({format : 'text'}).replace('%MCEPASTEBIN%', '').substr(0, 100));
            // }
            
        },
        max_chars: 100000,
        setup: function (ed) {


            //This code limits the number of characters on Android and Windows
            var allowedKeys = [8, 37, 38, 39, 40, 46]; // backspace, delete and cursor keys
            
            ed.on('keydown', function (e) {                       
                if (allowedKeys.indexOf(e.keyCode) != -1) return true;
                if (tinymce_getContentLength() + 1 > this.settings.max_chars) {
                    
                    showPopup('nameLonglyricsInput');
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                return true;
            });
            ed.on('keyup', function (e) {   
                if (tinymce_getContentLength() + 1 > this.settings.max_chars) {
                    showPopup('nameLonglyricsInput');
                    ed.setContent(ed.getContent({format : 'text'}).replace('%MCEPASTEBIN%', '').slice(0, -1));      
                    ed.focus();
                    ed.selection.select(ed.getBody(), true);
                    ed.selection.collapse(false);
                 } 
                tinymce_updateCharCounter(this, tinymce_getContentLength());
                
            });

           

            
           
        },
        init_instance_callback: function () { // initialize counter div
            $('#' + this.id).prev().append('<div class="char_count" style="text-align:right"></div>');
            tinymce_updateCharCounter(this, tinymce_getContentLength());
        },
        paste_preprocess: function (plugin, args) {

            var editor = tinymce.get(tinymce.activeEditor.id);
            var len = editor.contentDocument.body.innerText.length;
            var text = $('<div/>').html(args.content).text();
            if (len + text.length > editor.settings.max_chars) {
                showPopup('pasteLonglyricsInput');
                args.content = '';
            } else {
                tinymce_updateCharCounter(editor, len + text.length);
            }
        }

    });

    
    </script>

    
</head>
<body onresize="adjustElements()" onload="adjustElements()">
<?php


// Returns wheter user is trying to edit an existing song
function editSong() {
    if( isset($_POST['editSongButton']) && isset($_POST['editCheckboxesSong'])  && isset($_POST['editCheckboxesSongKeys']) ) {
        return true;
    }
    else {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    //Check if submitted Group ID was received.
    if(isset($_POST["addSongGroupIdSubmit"]) && isset($_POST["addSongGroupKeySubmit"]) ) { 

        // Security check
        require("userDetailsChangedCheck.php");
        require("retrieveUserAccessLevel.php");
        $access_level = checkAccesstoResource($_POST['addSongGroupIdSubmit'], $db); // Retrieve proper access level for current user.
        if (isset($_SESSION['member_id']) && $access_level >= 5) { 
        
            require('./includes/db.php');

            $accessOk = 0;

            //GROUP KEY CHECK
            $groupIdSubmit = $_POST["addSongGroupIdSubmit"];
            $groupKeySubmit = $_POST["addSongGroupKeySubmit"];

            require("checkKey.php");
            
            // Used to check the supplied group key via POST to ensure security.
            $checkGroupKeyQuery = checkKey("groups", $groupIdSubmit, "groupKey",  $groupKeySubmit, $db);

            if($checkGroupKeyQuery){
                $accessOk = 1;
            }

            
            if (editSong() && $accessOk == 1) {
                $editSongId = $_POST["editCheckboxesSong"][0];
                $editSongKey = $_POST["editCheckboxesSongKeys"][0];

                // Used to check the supplied song key via POST to ensure security.
                $checkSongKeyQuery = checkKey("songs", $editSongId, "songKey",  $editSongKey, $db);
                // Makes sure the song exists and it is located in the target group
                if($checkSongKeyQuery && $checkGroupKeyQuery['id'] == $checkSongKeyQuery['groupId']){
                    $accessOk = 1;
                }
                else {
                    $accessOk = 0;
                }
            }
            

            if ($accessOk) {
                require("navmenustart.php");
                require("functions.php");
                include('./includes/scrollbuttons.php');


                $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :requestedgroup AND `groupKey` = :requestedgroupkey");
                $stmt->bindParam('requestedgroup', $groupIdSubmit);
                $stmt->bindParam('requestedgroupkey', $groupKeySubmit);
                $stmt->execute();
                $groupLookup = $stmt->fetchAll();

                $addSongGroupId = $groupLookup[0]['id'];
                $addSongGroupKey = $groupLookup[0]['groupKey'];

                $locationArray = array();

                $stmt = $db->prepare('SELECT * FROM groups WHERE id = :addsonggroupid');
                $stmt->bindParam('addsonggroupid',  $groupIdSubmit);
                $stmt->execute();
                $locationQuery = $stmt->fetch();
        
                do {
                    $locationArray[] = $locationQuery["groupName"];
                    $locationId[] = $locationQuery["id"];
                    $locationKey[] = $locationQuery["groupKey"];
                    $testGroupId = $locationQuery["relationalGroupId"];
                    $stmt = $db->prepare('SELECT * FROM groups WHERE id = :testgroupid');
                    $stmt->bindParam('testgroupid', $testGroupId);
                    $stmt->execute();
                    $locationQuery = $stmt->fetch();
                } while ($testGroupId != null);


                // Fetch existing images.
                $stmt = $db->prepare("SELECT * FROM `pictures` WHERE `songId` = :requestedsongid ORDER BY `pictureSort` ASC" );
                $stmt->bindParam('requestedsongid', $editSongId);
                $stmt->execute();
                $picturesLookup = $stmt->fetchAll();
            
                // Fetch existing audio files.
                $stmt = $db->prepare("SELECT * FROM `music` WHERE `songId` = :requestedsongid ORDER BY `musicSort` ASC");
                $stmt->bindParam('requestedsongid', $editSongId);
                $stmt->execute();
                $musicLookup = $stmt->fetchAll();

                // Fetch existing links.
                $stmt = $db->prepare("SELECT * FROM `links` WHERE `songId` = :requestedsongid ORDER BY `linkSort` ASC");
                $stmt->bindParam('requestedsongid', $editSongId);
                $stmt->execute();
                $linksLookup = $stmt->fetchAll();

                print("<br>
                
                        <h2 class=\"title\">Add or Update a Release In ");
                
                $size = sizeof($locationArray) - 1;
                for ($o = $size; $o >=0 ; $o--){
                    $location = $locationId[$o];
                    $key = $locationKey[$o];
                    if ($o == $size){
                        print("<span class=\"linkRedirect\"><a href=\"/songshome/group/$location/$key\">".$locationArray[$o]."</a></span>");
                    }
                    else{
                        print(" >> "."<span class=\"linkRedirect\"><a href=\"/songshome/group/$location/$key\">".$locationArray[$o]."</a></span>");
                    }
                }

                print("</h2>");

            
            
            print("
            
                <div style=\"text-align:center;\">

                    <div class=\"popup\" style=\"margin-left: auto; margin-right: auto; display:inline;\" >
                        <span class=\"songPopupText\" style=\"width: 150px; margin-left: -75px; min-width:128%; \" id=\"charErrornumberInput\">Invalid Characters<br>\\ ' < ></span>
                        <span class=\"songPopupText\" style=\"width: 180px;margin-left: -90px;\" id=\"nameLongnumberInput\">Length Limit Reached</span>");

                        if (editSong()  && $accessOk == 1) {
                            $songNumber = $checkSongKeyQuery['songNumber'];
                            print("
                            <input placeholder=\"#\" value=\"$songNumber\" name=\"numberInput\"  id=\"numberInput\" class=\"songNumberUpload\" maxlength=\"12\" style=\"font-size:25px;text-align:center;\" form=\"uploadSongForm\" type=\"text\">");
                        }
                        else {
                            print("
                            <input placeholder=\"#\" name=\"numberInput\"  id=\"numberInput\" class=\"songNumberUpload\" maxlength=\"12\" style=\"font-size:25px;text-align:center;\" form=\"uploadSongForm\" type=\"text\">");
                        }
                        
            print("</div>

                    <div class=\"popup\" style=\"margin-left: auto; margin-right: auto; display:inline;\" >
                        <span class=\"songPopupText\" style=\"width: 150px;margin-left: -75px;\" id=\"charErrorsongTitleInput\">Invalid Characters<br>\\ ' < ></span>
                        <span class=\"songPopupText\" style=\"width: 180px;margin-left: -90px;\" id=\"nameLongsongTitleInput\">Length Limit Reached</span>
                        <span class=\"songPopupText\" style=\"width: 210px;margin-left: -105px;\" id=\"emptyGroupsongTitleInput\">Please Enter a Song Name</span>");

                        if (editSong()  && $accessOk == 1) {
                            $songTitle = $checkSongKeyQuery['title'];
                            print(" 
                            <input placeholder=\"Song Title\" value=\"$songTitle\" name=\"songTitle\"  id=\"songTitleInput\" class=\"songTitleUpload\" maxlength=\"400\" style=\"font-size:25px;text-align:center;\" form=\"uploadSongForm\" type=\"text\">");
                        }
                        else {
                            print("
                            <input placeholder=\"Song Title\" name=\"songTitle\"  id=\"songTitleInput\" class=\"songTitleUpload\" maxlength=\"400\" style=\"font-size:25px;text-align:center;\" form=\"uploadSongForm\" type=\"text\">");
                        }
                            
                print("
                    </div>

            
                    <div class=\"popup\" style=\"margin-left: auto; margin-right: auto; display:block;\" >
                        <div class=\"tinyEditorWrap\">");

                        if (editSong()  && $accessOk == 1) {
                            $songLyrics = $checkSongKeyQuery['lyrics'];
                            print(" 
                            <textarea  name=\"lyricsInput\"  id=\"lyricsInput\" form=\"uploadSongForm\" type=\"text\">$songLyrics</textarea>");
                        }
                        else {
                            print("
                            <textarea  name=\"lyricsInput\"  id=\"lyricsInput\" form=\"uploadSongForm\" type=\"text\"></textarea>");
                        }
                        
                        print("
                        </div>
                        <span class=\"songPopupText\" style=\"width: 180px;margin-left: -90px;bottom:calc(9px + .4vw); z-index:2;\" id=\"nameLonglyricsInput\">Length Limit Reached</span>
                        <span class=\"songPopupText\" style=\"width: 180px;margin-left: -90px;bottom:calc(9px + .4vw); z-index:2;\" id=\"pasteLonglyricsInput\">Paste Is Too Long</span>
                        <span class=\"songPopupText\" style=\"width: 150px;margin-left: -75px;bottom:calc(9px + .4vw); z-index:2;\" id=\"charErrorlyricsInput\">Invalid Characters<br>\\ ' < ></span>
                    </div>

                    <div id=\"progressBarContainer\">                    
    
                    </div>
                    
                    <div style=\"z-index:1;padding-top:calc(14px + .3vh); padding-bottom:2em;max-width: 85%;margin: auto;\">
                        <div class=\"popup\" style=\"margin-left: auto; margin-right: auto; display:block;\" >
                            <span class=\"songPopupText\" style=\"width: 150px; padding-left: 5px; padding-right: 5px;margin-left: -75px;bottom:calc(15px + .4vw); z-index:1000;\" id=\"imageLimitAnalize\">Image Maximum: 4 Images</span>
                            <span class=\"songPopupText\" style=\"width: 130px; padding-left: 5px; padding-right: 5px;margin-left: -75px;bottom:calc(15px + .4vw); z-index:1000;\" id=\"invalidImageAnalize\">File Type Not Supported</span>
                        </div>
                        
                            <label id=\"scanImageButton\" class=\"button\" style=\"display:inline-block;z-index:1;font-size:calc(17px + .2vw); cursor: pointer;\">

                                <input  id=\"scanImage\" type=\"file\" accept=\"image/*\" multiple>
                                
                                <span style=\"z-index:1;\">Read Images In</span>
                                <select class=\"selectMenu\" style=\"-webkit-appearance: none;
                                -moz-appearance: none;z-index:2;min-width:calc(15px + .4vw);border: 1px solid #fff;\" id=\"languageSelection\">
                                ");

                                include('./includes/tesseract_languages.html');

                                print("
                                </select>
                            </label>
                        
                    </div>
                    

                    <div id=\"imagesContainer\" class=\"uploadContainer\" style=\"padding-top:calc(20px + .1vw);\">
                        <h3 class=\"normalHeading\" style=\"text-align:left;width:100%\">Add Images</h3>
                        <div class=\"imagesWrapUpload\" id=\"list0_item1\" onmouseover=\"activateDropArea(1, 0)\">
                            <label class=\"imgDropArea\" id=\"list0_dropArea1\" for=\"list0_itemInputList1\">
                                <label id=\"list0_addUpButton1\" onclick=\"moveItem(1, 'up', 0); event.preventDefault();\" class=\"imageUpButton\"> 
                                    <span>^</span>                   
                                </label>
                                <label id=\"list0_addDownButton1\" onclick=\"moveItem(1, 'down', 0); event.preventDefault();\" class=\"imageDownButton\"> 
                                    <span>^</span>                  
                                </label>
                                <label id=\"list0_removeButton1\" onclick=\"removeInput(1, 0, './imageNotFound.jpg'); event.preventDefault();\" class=\"removeImageButton\"> 
                                    <span>X</span>                  
                                </label>
                                <img id=\"list0_uploadPreview1\" class=\"imagesUpload\" type=\"image\" src=\"/songshome/imageNotFound.jpg\" alt=\"Selected Image\"/>
                                <div class=\"popup\" style=\"margin-left: auto; margin-right: auto; display:block;\">
                                    <span class=\"songPopupText\" style=\"width: 180px;margin-left: -90px;bottom:calc(10px + .4vw); padding-left:.3vw;padding-right:.3vw;\" id=\"list0_invalidFilePopup1\">File Type Not Supported</span>
                                    <span class=\"songPopupText\" style=\"width: 180px;margin-left: -90px;bottom:calc(10px + .4vw); padding-left:.3vw;padding-right:.3vw;\" id=\"list0_fileTooLargePopup1\">File Is Too Large</span>                           
                                    <p id=\"list0_numbers1\" class=\"imageNumbers\">1</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class=\"popup\" style=\"margin-left: auto; margin-right: auto; display:block;\">
                        <span class=\"songPopupText\" style=\"width: 180px;margin-left: -90px;bottom: calc(65px + .4vw);\" id=\"list0_itemLimitReached\">Image Limit Reached</span>                        
                        <label onclick=\"addItem(0)\" class=\"addImageButton\" id=\"addImageButton\"> 
                                <span>+</span>                     
                        </label>
                    </div>
                                    

                    <div id=\"audioContainer\" class=\"uploadContainer\" style=\"padding-top:calc(30px + .1vw);\">
                        <h3 class=\"normalHeading\" style=\"text-align:left;width:100%\">Upload Audio Files</h3>
                        <div id=\"list1_item1\">
                            <div class=\"musicPlayerWrap\" style=\"width:80%;position:relative;\">
                                <div class=\"audioDetailsWrap\">                                             
                                    <p id=\"list1_numbers1\" class=\"audioNumbers\" style=\"position:absolute;left:50%;top:24%;\">1</p>  

                                    <label id=\"list1_removeButton1\" style=\"z-index:1;\" onclick=\"removeInput(1, 1, './defaultMP3.mp3')\" class=\"removeAudioButton\"> 
                                        <span>X</span>                  
                                    </label>      
                                    <div class=\"popup\" style=\"margin-left: auto; margin-right: auto; display:inline;\" >
                                        <span class=\"songPopupText\" style=\"width: 150px;margin-left: -75px;\" id=\"charErrorlist1_audioLabelInput1\">Invalid Characters<br>\\ ' < ></span>
                                        <span class=\"songPopupText\" style=\"width: 180px;margin-left: -90px;\" id=\"nameLonglist1_audioLabelInput1\">Length Limit Reached</span>
                                        <input placeholder=\"Audio Label\" name=\"audioLabelInput[]\"  id=\"list1_audioLabelInput1\" class=\"audioLabelUpload\" maxlength=\"200\" style=\"font-size:25px;text-align:center;position:inline;width:95%;\" form=\"uploadSongForm\" type=\"text\">     
                                    </div>

                                    <label id=\"list1_addUpButton1\" onclick=\"moveItem(1, 'up', 1)\" class=\"audioUpButton\"> 
                                        <span>^</span>                   
                                    </label>
                            
                                    <div id=\"musicWrapClickDetect\" class=\"list1_musicPlayer1\" style=\"width:95%; box-sizing: border-box; margin-left:auto;margin-right:auto;border-width:2px;display:inline-flex;\">                       
                                        <audio id=\"list1_uploadPreview1\" src=\"/songshome/defaultMP3.mp3\" crossorigin>
                                            <source type=\"audio/mpeg\">                                
                                        </audio>    
                                    </div>  
                                    <label id=\"list1_addDownButton1\" onclick=\"moveItem(1, 'down', 1)\" class=\"audioDownButton\"> 
                                        <span>^</span>                  
                                    </label>
                                </div>
                            </div>
                            <div class=\"popup\" style=\"margin-left: auto; margin-right: auto; display:inline;\" >
                                <span class=\"songPopupText\" style=\"width: 200px;margin-left: -250px; display: inline-table; z-index:2;\" id=\"list1_invalidFilePopup1\">File Type Not Supported</span>
                                <div class=\"audioDropArea\" id=\"list1_dropArea1\" for=\"list1_itemInputList1\"  onmouseover=\"activateDropArea(1, 1)\">
                                    <div style=\"display:inline-block;position:relative; width:40%; white-space: nowrap;overflow: hidden;text-overflow: ellipsis; \">
                                        <label class=\"normalText\" id=\"list1_audioFileName1\" style=\"font-size: calc(15px + .25vw);\"></label>
                                    </div>
                                    
                                    <button style=\"display:inline-block;position:relative;margin-left:auto;margin-top: calc(5px + .5vw);\" onclick=\"document.getElementById('list1_itemInputList1').click();\" class=\"button\">Select Audio</button>
                                    <p hidden class=\"normalText\" style=\"display:block;text-align:center;padding-left:calc(14px + 1.1vw);padding-right:calc(14px + 1.1vw);padding-top:calc(10px + .25vw);font-size: calc(12px + .3vw);line-height:25px; visibility:hidden;\">Include audio in the group music player?
                                        <label style=\"display:inline-block;\" class=\"switch\">
                                            <input id=\"list1_itemToggle1\" type=\"checkbox\" checked>        
                                            <span class=\"sliderToggle round\"></span>
                                        </label>
                                        <input id=\"list1_itemToggleValue1\" type=\"hidden\" value=\"1\" name=\"playerToggleValue[]\" form=\"uploadSongForm\">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class=\"popup\" style=\"margin-left: auto; margin-right: auto; display:block;\">
                        <span class=\"songPopupText\" style=\"width: 180px;margin-left: -90px;bottom: calc(65px + .4vw);\" id=\"list1_itemLimitReached\">Audio Limit Reached</span>  
                        <label onclick=\"addItem(1)\" class=\"addAudioButton\" id=\"addAudioButton\" style=\"margin-top:calc(20px + .4vw);\"> 
                                <span>+</span>                     
                        </label>
                    </div>




                    
                    <div id=\"linkContainer\" class=\"uploadContainer\" style=\"padding-top:calc(30px + .1vw);\">
                        <h3 class=\"normalHeading\" style=\"text-align:left;width:100%\">Add Links or YouTube Videos/Playlists</h3>
                        <div id=\"list2_item1\">
                            <div class=\"linkUploadWrap\" style=\"width:80%;position:relative;\">
                                <div class=\"linkDetailsWrap\">                                             
                                    <p id=\"list2_numbers1\" class=\"linkNumbers\" style=\"position:absolute;left:50%;top:24%;\">1</p>  

                                    <label id=\"list2_removeButton1\" style=\"z-index:1;\" onclick=\"removeInput(1, 2, 'empty')\" class=\"removeLinkButton\"> 
                                        <span>X</span>                  
                                    </label>      

                                    <span class=\"popup\" style=\"margin-left: auto; margin-right: auto; display:inline;\" >
                                        <span class=\"songPopupText\" style=\"width: 150px;margin-left: -75px;\" id=\"charErrorlist2_linkLabelInput1\">Invalid Characters<br>\\ ' < ></span>
                                        <span class=\"songPopupText\" style=\"width: 180px;margin-left: -90px;\" id=\"nameLonglist2_linkLabelInput1\">Length Limit Reached</span>
                                        <span class=\"songPopupText\" style=\"width: 150px;margin-left: -75px;\" id=\"charErrorlist2_linkInput1\">Invalid Characters<br>\\ ' < ></span>
                                        <span class=\"songPopupText\" style=\"width: 180px;margin-left: -90px;\" id=\"nameLonglist2_linkInput1\">Length Limit Reached</span>
                                        <input placeholder=\"Link Label\" name=\"linkLabelInput[]\"  id=\"list2_linkLabelInput1\" class=\"linkLabelUpload\" maxlength=\"980\" style=\"font-size:25px;text-align:center;position:inline;width:80%;\" form=\"uploadSongForm\" type=\"text\"> 
                                    </span>

                                    <input placeholder=\"Paste Link Here\" name=\"linkInput[]\" onchange=\"checkRemoveButton(1, 2);\" id=\"list2_linkInput1\" class=\"linkLabelUpload\" maxlength=\"5000\" style=\"font-size:25px;text-align:center;position:inline;width:95%;margin-top: 0;\" form=\"uploadSongForm\" type=\"text\">  
                                    
                                    <label id=\"list2_addUpButton1\" onclick=\"moveItem(1, 'up', 2)\" class=\"linkUpButton\"> 
                                        <span>^</span>                   
                                    </label>
                            
                                    <label id=\"list2_addDownButton1\" onclick=\"moveItem(1, 'down', 2)\" class=\"linkDownButton\"> 
                                        <span>^</span>                  
                                    </label>

                                </div>      
                            </div>
                            <p class=\"normalText\" style=\"display:block;text-align:center;padding-left:calc(14px + 1.1vw);padding-right:calc(14px + 1.1vw);font-size: calc(12px + .3vw);line-height:25px;\">This link is a:
                                <select id=\"list2_linkInput1_linkType\" name=\"youtubeToggleValue[]\" form=\"uploadSongForm\" class=\"selectMenu\" style=\"-webkit-appearance: none;
                                -moz-appearance: none;z-index:2;min-width:calc(15px + .4vw);border: 1px solid #fff;
                                padding-bottom:calc(2px + .1vw);padding-top:calc(2px + .1vw);\"
                                id=\"languageSelection\">
                                    <option value=\"0\" selected>URL</option>
                                    <option value=\"1\" >YouTube Video</option>
                                    <option value=\"2\" >YouTube Playlist</option>
                                </select>
                            </p>
                        </div>
                    </div>

                    <div class=\"popup\" style=\"margin-left: auto; margin-right: auto; display:block;\">
                        <span class=\"songPopupText\" style=\"width: 180px;margin-left: -90px;bottom: calc(65px + .4vw);\" id=\"list2_itemLimitReached\">Link Limit Reached</span>  
                        <label onclick=\"addItem(2)\" class=\"addLinkButton\" id=\"addLinkButton\" style=\"margin-top:calc(20px + .4vw);\"> 
                                <span>+</span>                     
                        </label>
                    </div>

                    <div class=\"uploadContainer\" >
                
                        <div class=\"borderContainer\" style=\"margin-top:calc(50px + .4vw);padding-top:calc(10px + .4vw);padding-bottom:calc(10px + .4vw);\">
                            <div class=\"popup\" style=\"margin-left: auto; margin-right: auto; display:block;\" >
                                <span class=\"songPopupText\" style=\"width: 180px;margin-left: -90px;bottom:calc(19px + .4vw); padding-left:.3vw;padding-right:.3vw;\" id=\"fileSizeLimitReached\">File Size Limit Reached</span>
                            </div>
                            <p class=\"normalText\" id=\"totalMb\" style=\"text-align:left;padding-left:calc(14px + 1.1vw);padding-right:calc(14px + 1.1vw);font-size: calc(15px + .3vw);line-height:25px;\">Selected Files (150MB Limit): 0MB</p>                  
                        </div>
                        
                        <div class=\"borderContainer\" style=\"margin-top:calc(5px + .1vw);padding-top:calc(10px + .4vw);padding-bottom:calc(10px + .4vw);\">
                            <p class=\"normalText\" style=\"display:block;text-align:center;padding-left:calc(14px + 1.1vw);padding-right:calc(14px + 1.1vw);font-size: calc(15px + .3vw);line-height:25px;\">Hide from group (Link Only Acccess)?
                                <label style=\"display:inline-block;\" class=\"switch\">");

                                if (editSong()  && $accessOk == 1) {
                                    $songLinkAccessOnly = $checkSongKeyQuery['linkAccessOnly'];

                                    if ($songLinkAccessOnly == 0) {
                                        print("<input id=\"linkAccessOnlyToggle\" type=\"checkbox\">");
                                    }
                                    else {
                                        print("<input id=\"linkAccessOnlyToggle\" type=\"checkbox\" checked>");
                                    }
                                    
                                }
                                else {
                                    print("
                                    <input id=\"linkAccessOnlyToggle\" type=\"checkbox\">");
                                }
                                
                            print("     
                                    <span class=\"sliderToggle round\"></span>
                                </label>
                                <input id=\"linkAccessOnly\" type=\"hidden\" name=\"linkAccessOnly\" form=\"uploadSongForm\">
                            </p>
                            
                        </div>
                    </div>

                    <form enctype=\"multipart/form-data\" id=\"uploadSongForm\" onsubmit=\"checkIfEmpty('songTitleInput');checkIfUploadReady();setItemToggleValues(1);setTogglesValues();\" autocomplete=\"off\"  target=\"_self\" action=\"/songshome/pageprocess.php\" method=\"post\">
                    
                        <div style=\"padding-top:2em;\">
                    
                                <label style=\"display:none;\" class=\"button\"> 
                                    <span id=\"imageFileContainer\">
                                        <input id=\"list0_itemInputList1\" name=\"songImages[]\"  type=\"file\" accept=\"images/*\" onchange=\" if(!checkFileType(this.files, 1, 0)){showPopup('list0_invalidFilePopup1');return;} compressImage(1, 0, 2000, './imageNotFound.jpg'); document.getElementById('list0_uploadPreview1').src = window.URL.createObjectURL(this.files[this.files.length - 1]); document.getElementById('list0' + '_removeButton1').style.visibility = 'visible'; checkRemoveButton(1, 0);\">
                                    </span>   

                                    <span id=\"audioFileContainer\">
                                        <input id=\"list1_itemInputList1\" name=\"audioFiles[]\"  onchange=\"if(!checkFileType(this.files, 1, 1) ){showPopup('list1_invalidFilePopup1');return;} document.getElementById('list1_uploadPreview1').src = window.URL.createObjectURL(this.files[this.files.length - 1]); document.getElementById('list1_uploadPreview1').pause(); setPauseButton(1, 1); document.getElementById('list1_audioFileName1').textContent = this.files[0].name; document.getElementById('list1_removeButton1').style.visibility = 'visible'; checkRemoveButton(1, 1); updateSelectedMb();\" type=\"file\" accept=\"audio/*\">
                                    </span>   
                                    
                                    <span id=\"linkTextContainer\">
                                        <input id=\"list2_itemInputList1\" type=\"hidden\">
                                    </span>   

                                </label>
                                
                                <input form=\"uploadSongForm\" onclick=\"submitUpdatedData();\" style=\"background-color:#4fbd49; margin-top:0px;\" class=\"button\" type=\"button\" name=\"submitBtn\" value=\"Save Changes\">

                            <br><br><br>
                            
                            <input form=\"uploadSongForm\" type=\"hidden\" name=\"addSongGroupIdSubmit\" value=\"$addSongGroupId\">
                            <input form=\"uploadSongForm\" type=\"hidden\" name=\"addSongGroupKeySubmit\" value=\"$addSongGroupKey\">");
                        
                        if (editSong()) {
                            print("
                            <input form=\"uploadSongForm\" type=\"hidden\" name=\"editCheckboxesSong\" value=\"$editSongId\">
                            <input form=\"uploadSongForm\" type=\"hidden\" name=\"editCheckboxesSongKeys\" value=\"$editSongKey\">
                            ");
                        }

                        print("    
                        </div>
                    </form>
                </div>

                ");

                require("navmenuend.php");
            }
            else
            {
                echo "ERROR 404: Database Error.";
                //header("Location: "."./groups.php");
            }

        }
        else
        {
            echo "Error: You do not have access to this resource.";
        } 

    }
    else {
        print("The target group was not recieved.");
        // header("Location: "."./groups.php");
    }    

}
else {
    // header("Location: "."./groups.php");
}
?>

</body>

<script type="text/javascript" src="/songshome/includes/functionsScript.js"></script>
<script>

//Fixes some issues with the image scanning
document.getElementById("scanImage").addEventListener("click", function(e){

    //Clears input image value so the same image can be scanned twice in a row.
    this.value = null;

    //  Prevents an issue on iOS that occurs when a language is being selected. 
    //  Selecting the language would trigger the file input on iOS which would break the file input button.
    if(document.activeElement ==  document.getElementById("languageSelection")){
      e.preventDefault();
    } 
});


function pasteReadText(text){
    var editor = tinymce.get(tinymce.activeEditor.id);
    var len = editor.contentDocument.body.innerText.length;

    if (len + text.length > editor.settings.max_chars) {
        // alert('Pasting this exceeds the maximum allowed number of ' + editor.settings.max_chars + ' characters.');
        showPopup('pasteLonglyricsInput');
    } else {
        
        //Insert line break if needed
        if(len > 1){
            tinymce.get("lyricsInput").execCommand('mceInsertRawHTML', false, '<br/>');
        }

        tinymce.get("lyricsInput").execCommand('mceInsertRawHTML', false, text.replace(/\n/g, "<br/>"));
        
        tinymce_updateCharCounter(editor, len + text.length);
    }
    
}

//Auto expand textbox for lyrics
var autoExpand = function (field) {

    // Reset field height
    field.style.height = 'inherit';

    // Get the computed styles for the element
    var computed = window.getComputedStyle(field);

    // Calculate the height
    var height = parseInt(computed.getPropertyValue('border-top-width'), 10)
            + parseInt(computed.getPropertyValue('padding-top'), 10)
            + field.scrollHeight
            + parseInt(computed.getPropertyValue('padding-bottom'), 10)
            + parseInt(computed.getPropertyValue('border-bottom-width'), 10);

    field.style.height = height + 'px';
    
};


document.addEventListener('input', function (event) {
if (event.target.tagName.toLowerCase() !== 'textarea') return;
    autoExpand(event.target);
}, false);

// Creates a multi dimensional arrays for the images, music and links items.
//itemsList[0] is for images
//itemsList[1] is for audio files
//itemsList[2] is for links
var itemsList = [ [ 1 ], [ 1 ], [ 1 ]];
var totalBytes = 0;
var uploadReady = 1;

//The next two functions hide/show the scroll buttons and adjust the first Remove Image button so it doesn't overlap the textbox.
function adjustElements(){

    // var toTopScrollButton = document.getElementById("scrollToTopButton");
    // var toBottomScrollButton = document.getElementById("scrollToBottomButton");
    // if (window.innerWidth <= 600)
    // {
    //     toTopScrollButton.style.visibility = "hidden";
    //     toBottomScrollButton.style.visibility = "hidden";
    // } 
    // else
    // {
    //     toTopScrollButton.style.visibility = "visible";
    //     toBottomScrollButton.style.visibility = "visible";
    // }

    // itemsList[0].forEach(setRemoveButtonPosition);
    itemsList[0].forEach((arrayValue, index) => setRemoveButtonPosition(arrayValue, index, 0));
    positionPopupText();
}

function positionPopupText(){
    var width = window.innerWidth;

    //Positions the popup text located at indexes 0 and 1.
    var charErrornumberInput = document.getElementById("charErrornumberInput");
    var nameLongnumberInput = document.getElementById("nameLongnumberInput");
    var emptyGroupsongTitleInput = document.getElementById("emptyGroupsongTitleInput");
    
    if (width <= 600){

        charErrornumberInput.style.marginLeft = "-65%";
        nameLongnumberInput.style.marginLeft = "-52%";
        emptyGroupsongTitleInput.style.marginLeft = "-105px";
        if (width <= 366){
            emptyGroupsongTitleInput.style.marginLeft = "-50%";
        }

    }
    else {     

        charErrornumberInput.style.marginLeft = "-75px";
        nameLongnumberInput.style.marginLeft = "-90px";
        emptyGroupsongTitleInput.style.marginLeft = "-105px";

   }
}

function setRemoveButtonPosition(arrayValue, index, whichList) {
    
    var width = window.innerWidth;

    //Targets image array to set remove button position and image padding.
    if (whichList == 0) {
        if (index == 0){
            
            if (width <= 600){
                document.getElementById("list" + whichList + "_item" + arrayValue).style.paddingTop = "calc(7.5px + .5vw)";
                document.getElementById("list" + whichList + "_removeButton" + arrayValue).style.top = "1%";
                
            }
            else if (width > 600 && width <= 1300) {      
                document.getElementById("list" + whichList + "_item" + arrayValue).style.paddingTop = "calc(7.5px + .5vw)";
                document.getElementById("list" + whichList + "_removeButton" + arrayValue).style.top = "3%";
            }
            else {
                document.getElementById("list" + whichList + "_item" + arrayValue).style.paddingTop = "calc(7.5px + .5vw)";
                document.getElementById("list" + whichList + "_removeButton" + arrayValue).style.top = "1%";
            }

        }
        else 
        {

            if (width <= 600){
                document.getElementById("list" + whichList + "_item" + arrayValue).style.paddingTop = "calc(20px + .5vw)";
                document.getElementById("list" + whichList + "_removeButton" + arrayValue).style.top = "1%";
            }
            else{
                document.getElementById("list" + whichList + "_item" + arrayValue).style.paddingTop = "(7.5px + 1.75vw)";
                document.getElementById("list" + whichList + "_removeButton" + arrayValue).style.top = "1%";
            }    

        }
    }

}


//Hides remove button if there is no input selected when there is only one item in the array
function checkRemoveButton(id, whichList){
    if (whichList == 0 || whichList == 1) {
        if (itemsList[whichList].length == 1){  
            
            firstArrayValue = itemsList[whichList][0];

            if (document.getElementById('list' + whichList + '_itemInputList' + firstArrayValue).value != ''){
                
                document.getElementById('list' + whichList + '_removeButton' + firstArrayValue).style.visibility = "visible";
            }
            else {
        
                document.getElementById('list' + whichList + '_removeButton' + firstArrayValue).style.visibility = "hidden";
                
            }
        }
    }
}

// Toggle for including audio in sitewide music player.
function setItemToggleValues(whichList){
    function loopEachItem(item, whichItem, whichList){
        if (whichList == 1) {
            if (document.getElementById('list' + whichList + '_itemToggle' + item).checked == true){
                document.getElementById('list' + whichList + '_itemToggleValue' + item).value = 1;
            }
            else {
                document.getElementById('list' + whichList + '_itemToggleValue' + item).value = 0;
            }
        }
       
    }
    
    itemsList[whichList].forEach((item, whichItem) => loopEachItem(item, whichItem, whichList));
    
}

//Sets up youtube link detection for the first link.
// document.getElementById("list2_linkInput1").onpaste = function(e) {


var togglesList = ['linkAccessOnly'];
function setTogglesValues(){

    function loopEachItem(item, whichItem){
        if (document.getElementById(item + 'Toggle').checked == true){
            document.getElementById(item).value = 1;
        }
        else {
            document.getElementById(item).value = 0;
        }
    }
    
    togglesList.forEach(loopEachItem);
}


//Hides buttons on the list item when the page loads and sets selected MB.
document.getElementById('list0_addUpButton1').style.visibility = "hidden";
document.getElementById('list0_addDownButton1').style.visibility = "hidden";
document.getElementById('list0_removeButton1').style.visibility = "hidden";

document.getElementById('list1_addUpButton1').style.visibility = "hidden";
document.getElementById('list1_addDownButton1').style.visibility = "hidden";
document.getElementById('list1_removeButton1').style.visibility = "hidden";

document.getElementById('list2_addUpButton1').style.visibility = "hidden";
document.getElementById('list2_addDownButton1').style.visibility = "hidden";
document.getElementById('list2_removeButton1').style.visibility = "hidden";

updateSelectedMb();

//Changes the image input prompt on android from images/* to file/*.
//This is because android has an issue with the images/* input prompt freezing.
setImageInputAcceptType(1, 0);

//Sets include in audio player values onload.
setItemToggleValues(1);

//Load Initial Audio Player
document.addEventListener('DOMContentLoaded', function() {          
    new GreenAudioPlayer('.list1_musicPlayer1', { stopOthersOnPlay: true });               
});

//Updates Pause Button when player is playing and a new input is selected.
function setPauseButton(id, whichList){

    var itemArrayIndex = itemsList[whichList].indexOf(id);
    document.getElementsByClassName('play-pause-btn__icon')[itemArrayIndex].attributes.d.value = 'M18 12L0 24V0';
    
}


function setImageInputAcceptType(id, whichList){
    var ua = navigator.userAgent.toLowerCase();
    var isAndroid = ua.indexOf("android") > -1; //&& ua.indexOf("mobile");
    if(isAndroid) {
        document.getElementById('list' + whichList + '_itemInputList' + id).attributes.accept.value = '';
    }
    else {
        document.getElementById('list' + whichList + '_itemInputList' + id).attributes.accept.value = 'image/*';
    }
    // alert(document.getElementById('list' + whichList + '_itemInputList' + id).attributes.accept.value);
}


// Adds an element to the end of the array and assigns it value that is the largest number in the array.
function arrayIncrement(whichList){
    var newValue = itemsList[whichList].length;    
    itemsList[whichList][newValue] = Math.max.apply(Math, itemsList[whichList]) + 1;
}

var preventScrollOnLoad = 0;
setTimeout(function () {preventScrollOnLoad++;}, 500);
//Adds a new images to the page
function addItem(whichList){
    
    // Limit # of items
    if (whichList == 0){
        if (itemsList[whichList].length >= 80) {
            showPopup('list' + whichList + '_itemLimitReached');
            return;
        }
    }
    else if (whichList == 1){
        if (itemsList[whichList].length >= 60) {
            showPopup('list' + whichList + '_itemLimitReached');
            return;
        }
    }
    else if (whichList == 2){
        if (itemsList[whichList].length >= 500) {
            showPopup('list' + whichList + '_itemLimitReached');
            return;
        }
    }
    

    // Adds an element to the end of the array and assigns it value that is the largest number in the array.
    arrayIncrement(whichList);

    // Gets the first and last value in the array.
    firstArrayValue = itemsList[whichList][0];
    lastArrayValue = itemsList[whichList][itemsList[whichList].length - 1];

    
    // Inserts the required HTML on the page.
    if (whichList == 0){


        
                    
              
    

        addHTML("imagesContainer", '<div class="imagesWrapUpload" id="list'+whichList+'_item'+lastArrayValue+'" onmouseover="activateDropArea('+lastArrayValue+', '+whichList+')">'
                                    + '<label class="imgDropArea" id="list'+whichList+'_dropArea'+lastArrayValue+'" for="list'+whichList+'_itemInputList'+lastArrayValue+'">'
                                        + '<label id="list'+whichList+'_addUpButton'+lastArrayValue+'" onclick="moveItem('+lastArrayValue+','+ ' \'up\', ' +whichList+'); event.preventDefault();" class="imageUpButton">' 
                                            +     '<span>^</span>'                     
                                        + '</label>'
                                        + '<label id="list'+whichList+'_addDownButton'+lastArrayValue+'" onclick="moveItem('+lastArrayValue+','+ ' \'down\', ' +whichList+'); event.preventDefault();" class="imageDownButton">' 
                                            +     '<span>^</span>'                     
                                        + '</label>'
                                        + '<label id="list'+whichList+'_removeButton'+lastArrayValue+'" onclick="removeInput('+lastArrayValue+', '+whichList+', \'./imageNotFound.jpg\'); event.preventDefault();" class="removeImageButton">' 
                                        +       '<span>X</span>'                  
                                        + '</label>'
                                        + '<img id="list'+whichList+'_uploadPreview'+lastArrayValue+'" class="imagesUpload" type="image" src="/songshome/imageNotFound.jpg" alt="Selected Image"/>'
                                        + '<div class="popup" style="margin-left:auto; margin-right:auto; display:block;">'
                                            + '<span class="songPopupText" style="width: 180px;margin-left: -90px;bottom:calc(10px + .4vw); padding-left:.3vw;padding-right:.3vw;" id="list'+whichList+'_invalidFilePopup'+lastArrayValue+'">File Type Not Supported</span>'
                                            + '<span class="songPopupText" style="width: 180px;margin-left: -90px;bottom:calc(10px + .4vw); padding-left:.3vw;padding-right:.3vw;" id="list'+whichList+'_fileTooLargePopup'+lastArrayValue+'">File Is Too Large</span>'
                                            + '<p id="list'+whichList+'_numbers'+lastArrayValue+'" class="imageNumbers">' + itemsList[whichList].length + '</p>'
                                        + '</div>'
                                    + '</label>'
                                 + '</div>', 'beforeend');

                            

                                                       
                        
 
                            
        addHTML("imageFileContainer", '<input id="list'+whichList+'_itemInputList'+lastArrayValue+'" name="songImages[]"  type="file" accept="image/*" onchange=" if(!checkFileType(this.files, '+lastArrayValue+', '+whichList+')){showPopup(\'list'+whichList+'_invalidFilePopup'+lastArrayValue+'\');return;} compressImage('+lastArrayValue+', '+whichList+', 2000, \'./imageNotFound.jpg\'); document.getElementById(\'list'+whichList+'_uploadPreview'+lastArrayValue+'\').src = window.URL.createObjectURL(this.files[this.files.length - 1]); document.getElementById(\'list'+whichList+'_removeButton'+lastArrayValue+'\').style.visibility = \'visible\'; checkRemoveButton('+lastArrayValue+', '+whichList+');">', 'beforeend');
                                        
        setImageInputAcceptType(lastArrayValue, whichList);
    }
                              

    else if(whichList == 1){
      
        addHTML("audioContainer", '<div id="list'+whichList+'_item'+lastArrayValue+'">'
                                    + '<div class="musicPlayerWrap" style="width:80%;position:relative;">'
                                        + '<div class="audioDetailsWrap">'
                                            + '<p id="list'+whichList+'_numbers'+lastArrayValue+'" class="audioNumbers" style="position:absolute;left:50%;top:24%;">' + itemsList[whichList].length + '</p>'
                                            + '<label id="list'+whichList+'_removeButton'+lastArrayValue+'" style="z-index:1;" onclick="removeInput('+lastArrayValue+', '+whichList+', \'./defaultMP3.mp3\')" class="removeAudioButton">'
                                                + '<span>X</span>'
                                            + '</label>'
                                            + '<div class="popup" style="margin-left: auto; margin-right: auto; display:inline;">'
                                                + '<span class="songPopupText" style="width: 150px;margin-left: -75px;" id="charErrorlist'+whichList+'_audioLabelInput'+lastArrayValue+'">Invalid Characters<br>\\ \' < ></span>'
                                                + '<span class="songPopupText" style="width: 180px;margin-left: -90px;" id="nameLonglist'+whichList+'_audioLabelInput'+lastArrayValue+'">Length Limit Reached</span>'
                                                + '<input placeholder="Audio Label" name="audioLabelInput[]"  id="list'+whichList+'_audioLabelInput'+lastArrayValue+'" class="audioLabelUpload" maxlength="400" style="font-size:25px;text-align:center;position:inline;width:95%;" form="uploadSongForm" type="text">'
                                            + '</div>'
                                            + '<label id="list'+whichList+'_addUpButton'+lastArrayValue+'" onclick="moveItem('+lastArrayValue+','+ ' \'up\', ' +whichList+');" class="audioUpButton">'
                                                + '<span>^</span>'
                                            + '</label>'
                                            + '<div id="musicWrapClickDetect" class="list'+whichList+'_musicPlayer'+lastArrayValue+'" style="width:95%; box-sizing: border-box; margin-left:auto;margin-right:auto;border-color: #000000;border-width:2px;display:inline-flex;">'
                                                + '<audio id="list'+whichList+'_uploadPreview'+lastArrayValue+'" src="/songshome/defaultMP3.mp3" crossorigin>'
                                                    + '<source type="audio/mpeg">'
                                                + '</audio>'
                                            + '</div>'
                                            + '<label id="list'+whichList+'_addDownButton'+lastArrayValue+'" onclick="moveItem('+lastArrayValue+','+ ' \'down\', ' +whichList+');" class="audioDownButton">'
                                                + '<span>^</span>'
                                            + '</label>'
                                        + '</div>'
                                    + '</div>'
                                    + '<div class="popup" style="margin-left: auto; margin-right: auto; display:inline;">'
                                        + '<span class="songPopupText" style="width: 200px;margin-left: -90px; display: inline-table; z-index:2;" id="list'+whichList+'_invalidFilePopup'+lastArrayValue+'">File Type Not Supported</span>'
                                        + '<div class="audioDropArea" id="list'+whichList+'_dropArea'+lastArrayValue+'" for="list'+whichList+'_itemInputList'+lastArrayValue+'"  onmouseover="activateDropArea('+lastArrayValue+', '+whichList+')">'
                                            + '<div style="display:inline-block;position:relative; width:40%; white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">'
                                                + '<label class="normalText" id="list'+whichList+'_audioFileName'+lastArrayValue+'" style="font-size: calc(15px + .25vw);"></label>'
                                            + '</div>'
                                            + '<button style="display:inline-block;position:relative;margin-left:auto;margin-top: calc(5px + .5vw);" onclick="document.getElementById(\'list'+whichList+'_itemInputList'+lastArrayValue+'\').click();" class="button">Select Audio</button>'
                                            + '<p class="normalText" style="display:block;text-align:center;padding-left:calc(14px + 1.1vw);padding-right:calc(14px + 1.1vw);padding-top:calc(10px + .25vw);font-size: calc(12px + .3vw);line-height:25px; visibility:hidden;">Include audio in the group music player?'
                                                + '<label style="display:inline-block;" class="switch">'
                                                    + '<input id="list'+whichList+'_itemToggle'+lastArrayValue+'" type="checkbox" checked>'
                                                    + '<span class="sliderToggle round"></span>'
                                                + '</label>'
                                                + '<input id="list'+whichList+'_itemToggleValue'+lastArrayValue+'" type="hidden" value="1" name="playerToggleValue[]" form="uploadSongForm">'
                                            + '</p>'
                                        + '</div>'
                                    + '</div>'
                                + '</div>', 'beforeend'); 

        new GreenAudioPlayer('.list'+whichList+'_musicPlayer'+lastArrayValue, { stopOthersOnPlay: true });

        addHTML("audioFileContainer", '<input id="list'+whichList+'_itemInputList'+lastArrayValue+'" name="audioFiles[]"  onchange="if(!checkFileType(this.files, '+lastArrayValue+', '+whichList+') ){showPopup(\'list'+whichList+'_invalidFilePopup'+lastArrayValue+'\');return;} document.getElementById(\'list'+whichList+'_uploadPreview'+lastArrayValue+'\').src = window.URL.createObjectURL(this.files[this.files.length - 1]); document.getElementById(\'list'+whichList+'_uploadPreview'+lastArrayValue+'\').pause(); setPauseButton('+lastArrayValue+', '+whichList+'); document.getElementById(\'list'+whichList+'_audioFileName'+lastArrayValue+'\').textContent = this.files[0].name; document.getElementById(\'list'+whichList+'_removeButton'+lastArrayValue+'\').style.visibility = \'visible\'; checkRemoveButton('+lastArrayValue+', '+whichList+'); updateSelectedMb();" type="file" accept="audio/*">', 'beforeend');
                              
        addCheckInputFunctions('list'+whichList+'_audioLabelInput'+lastArrayValue, null);                     
    }
    else if(whichList == 2){

        addHTML("linkContainer", '<div id="list'+whichList+'_item'+lastArrayValue+'">'
                                    + '<div class="linkUploadWrap" style="width:80%;position:relative;">'
                                        + '<div class="linkDetailsWrap">'
                                            + '<p id="list'+whichList+'_numbers'+lastArrayValue+'" class="linkNumbers" style="position:absolute;left:50%;top:24%;">' + itemsList[whichList].length + '</p>'
                                            + '<label id="list'+whichList+'_removeButton'+lastArrayValue+'" style="z-index:1;" onclick="removeInput('+lastArrayValue+', '+whichList+', \'empty\')" class="removeLinkButton">'
                                                + '<span>X</span>'
                                            + '</label>'
                                            + '<span class="popup" style="margin-left: auto; margin-right: auto; display:inline;">'
                                                + '<span class="songPopupText" style="width: 150px;margin-left: -75px;" id="charErrorlist'+whichList+'_linkLabelInput'+lastArrayValue+'">Invalid Characters<br>\\ \' < ></span>'
                                                + '<span class="songPopupText" style="width: 180px;margin-left: -90px;" id="nameLonglist'+whichList+'_linkLabelInput'+lastArrayValue+'">Length Limit Reached</span>'
                                                + '<span class="songPopupText" style="width: 150px;margin-left: -75px;" id="charErrorlist'+whichList+'_linkInput'+lastArrayValue+'">Invalid Characters<br>\\ \' < ></span>'
                                                + '<span class="songPopupText" style="width: 180px;margin-left: -90px;" id="nameLonglist'+whichList+'_linkInput'+lastArrayValue+'">Length Limit Reached</span>'
                                                + '<input placeholder="Link Label" name="linkLabelInput[]"  id="list'+whichList+'_linkLabelInput'+lastArrayValue+'" class="linkLabelUpload" maxlength="980" style="font-size:25px;text-align:center;position:inline;width:95%;" form="uploadSongForm" type="text">'
                                            + '</span>'
                                            + '<input placeholder="Paste Link Here" name="linkInput[]" onchange="checkRemoveButton('+lastArrayValue+', '+whichList+');" id="list'+whichList+'_linkInput'+lastArrayValue+'" class="linkLabelUpload" maxlength="5000" style="font-size:25px;text-align:center;position:inline;width:95%;margin-top: 0;" form="uploadSongForm" type="text">'
                                            + '<label id="list'+whichList+'_addUpButton'+lastArrayValue+'" onclick="moveItem('+lastArrayValue+','+ ' \'up\', ' +whichList+')" class="linkUpButton">'
                                                + '<span>^</span>'
                                            + '</label>'
                                            + '<label id="list'+whichList+'_addDownButton'+lastArrayValue+'" onclick="moveItem('+lastArrayValue+','+ ' \'down\', ' +whichList+')" class="linkDownButton">'
                                                + '<span>^</span>'
                                            + '</label>'
                                        + '</div>'
                                    + '</div>'
                                    + '<p class="normalText" style="display:block;text-align:center;padding-left:calc(14px + 1.1vw);padding-right:calc(14px + 1.1vw);font-size: calc(12px + .3vw);line-height:25px;">This link is a:'
                                        + '<select id="list'+whichList+'_linkInput'+lastArrayValue+'_linkType" name="youtubeToggleValue[]" form="uploadSongForm" class="selectMenu" style="-webkit-appearance: none;'
                                        + '-moz-appearance: none;z-index:2;min-width:calc(15px + .4vw);border: 1px solid #fff;'
                                        + 'padding-bottom:calc(2px + .1vw);padding-top:calc(2px + .1vw);"'
                                        + 'id="languageSelection">'
                                            + '<option value="0" selected>URL</option>'
                                            + '<option value="1" >YouTube Video</option>'
                                            + '<option value="2" >YouTube Playlist</option>'
                                        + '</select>'
                                    + '</p>'
                               + '</div>', 'beforeend');
                   
        addHTML("linkTextContainer", '<input id="list'+whichList+'_itemInputList'+lastArrayValue+'" type="hidden">', 'beforeend');

        addCheckInputFunctions('list'+whichList+'_linkLabelInput'+lastArrayValue, null);  
        addCheckInputFunctions('list'+whichList+'_linkInput'+lastArrayValue, null);   
        
    }
    

    // Calls the function to update the item buttons visibility and page numbers of the items.
    setItemAttributes(whichList);

    //Scroll Into View Code
    if (preventScrollOnLoad != 0) {
        if (whichList == 0){
            scrollToElement("addImageButton", "smooth", "center", "nearest");  
        }
        else if (whichList == 1){          
            scrollToElement("addAudioButton", "smooth", "center", "nearest");
        }
        else if (whichList == 2){
            scrollToElement("addLinkButton", "smooth", "center", "nearest");
        }
    }
    
    adjustElements();
}

//Removes the image html, input html, and its array value.
function removeInput(id, whichList, defaultPreviewSrc){
    
    // alert(document.getElementById('list' + whichList + '_itemInputList' + id).value);
    if (itemsList[whichList].length >= 2){

        itemArrayIndex = itemsList[whichList].indexOf(id);
        itemsList[whichList].splice(itemArrayIndex, 1);

        removeHTML('list' + whichList + '_item' + id);
        removeHTML('list' + whichList + '_itemInputList' + id);
        
        // console.log(itemsList[0]);
        setItemAttributes(whichList);
        if (itemsList[whichList].length == 1){
            if (whichList == 0){
                scrollToElement("addImageButton", "smooth", "center", "nearest");  
            }
            else if (whichList == 1){          
                scrollToElement("addAudioButton", "smooth", "center", "nearest");
            }
            else if (whichList == 2){
                scrollToElement("addLinkButton", "smooth", "center", "nearest");
            }
               
        }
        
        if (whichList != 2) {
            updateSelectedMb();
        }
        

        checkRemoveButton(id, whichList); 
    }
    else {
        
        

        if (whichList != 2){
            document.getElementById('list' + whichList + '_itemInputList' + id).value = '';
            document.getElementById('list' + whichList + '_uploadPreview' + id).src = defaultPreviewSrc;
            updateSelectedMb();
        }

        if (whichList == 1) {      
            //Updates file name label for selected audio to default
            document.getElementById('list' + whichList + '_audioFileName' + id).innerHTML = '';
        }
        
        checkRemoveButton(id, whichList);  
    }

    adjustElements();
}

function setItemAttributes(whichList){

    // Gets the last and first value inside the array.
    firstArrayValue = itemsList[whichList][0];
    lastArrayValue = itemsList[whichList][itemsList[whichList].length - 1];
   
    // Checks how many items exist in the array and then updates the visibility of the buttons and page numbers.
    if(itemsList[whichList].length > 2){
        // If there are more than two items a foreach loop updates each 
        // Item button visibility and page numbers based on their position.
        function setImageBtnVisibility(arrayValue, index, whichList) {
            if (index != 0 && index != (itemsList[whichList].length - 1)){
                    document.getElementById('list' + whichList + '_addUpButton' + arrayValue).style.visibility = "visible";
                    document.getElementById('list' + whichList + '_addDownButton' + arrayValue).style.visibility = "visible";

                    document.getElementById('list' + whichList + '_numbers' + arrayValue).innerHTML = index + 1;

                    document.getElementById('list' + whichList + '_removeButton' + arrayValue).style.visibility = "visible";
            }
            else if (index == 0){
                    // Top Image
                    document.getElementById('list' + whichList + '_addUpButton' + firstArrayValue).style.visibility = "hidden";
                    document.getElementById('list' + whichList + '_addDownButton' + firstArrayValue).style.visibility = "visible";

                    document.getElementById('list' + whichList + '_numbers' + firstArrayValue).innerHTML = index + 1;

                    document.getElementById('list' + whichList + '_removeButton' + firstArrayValue).style.visibility = "visible"; 
            }
            else {
                    //Bottom Image
                    document.getElementById('list' + whichList + '_addUpButton' + lastArrayValue).style.visibility = "visible";
                    document.getElementById('list' + whichList + '_addDownButton' + lastArrayValue).style.visibility = "hidden"; 

                    document.getElementById('list' + whichList + '_numbers' +  lastArrayValue).innerHTML = index + 1;

                    document.getElementById('list' + whichList + '_removeButton' + lastArrayValue).style.visibility = "visible";
            }
        }

        itemsList[whichList].forEach((arrayValue, index) => setImageBtnVisibility(arrayValue, index, whichList));

    }
    else if(itemsList[whichList].length == 2){
        //Top Image
        document.getElementById('list' + whichList + '_addUpButton' + firstArrayValue).style.visibility = "hidden";
        document.getElementById('list' + whichList + '_addDownButton' + firstArrayValue).style.visibility = "visible";

        document.getElementById('list' + whichList + '_numbers' +  firstArrayValue).innerHTML = itemsList[whichList].length - 1;

        document.getElementById('list' + whichList + '_removeButton' + firstArrayValue).style.visibility = "visible";

        //Bottom Image
        document.getElementById('list' + whichList + '_addUpButton' + lastArrayValue).style.visibility = "visible";
        document.getElementById('list' + whichList + '_addDownButton' + lastArrayValue).style.visibility = "hidden";

        document.getElementById('list' + whichList + '_numbers' +  lastArrayValue).innerHTML = itemsList[whichList].length;   

        document.getElementById('list' + whichList + '_removeButton' + lastArrayValue).style.visibility = "visible";
    }
    else if(itemsList[whichList].length == 1){
        //Single Image
        document.getElementById('list' + whichList + '_addUpButton' + firstArrayValue).style.visibility = "hidden";
        document.getElementById('list' + whichList + '_addDownButton' + firstArrayValue).style.visibility = "hidden";

        document.getElementById('list' + whichList + '_numbers' +  firstArrayValue).innerHTML = itemsList[whichList].length;

        document.getElementById('list' + whichList + '_removeButton' + firstArrayValue).style.visibility = "hidden";   
    }

}


//Moves the html of two items up or down when called and updates their position in the array.
function moveItem(id, where, whichList){

    //Note: The id variable represents the item value inside the array.

    //This Determines whether the item should move up or down
    if (where == 'up'){
        itemArrayIndex = itemsList[whichList].indexOf(id);
        previousArrayIndex = itemsList[whichList].indexOf(id) - 1;
        itemArrayValue = itemsList[whichList][itemArrayIndex];  
        previousArrayValue = itemsList[whichList][previousArrayIndex];
        
    }
    else if(where == 'down'){
        itemArrayIndex = itemsList[whichList].indexOf(id) + 1;
        previousArrayIndex = itemsList[whichList].indexOf(id);
        itemArrayValue = itemsList[whichList][itemArrayIndex];  
        previousArrayValue = itemsList[whichList][previousArrayIndex];

    }

    // Get the reference node for image placeholder html
    var targetElementButton = document.getElementById('list' + whichList + '_item' + itemArrayValue);
    var referenceNodeButton = document.querySelector('#list' + whichList + '_item' + previousArrayValue);


    // Get the reference node for input html
    var targetElementInput = document.getElementById('list' + whichList + '_itemInputList' + itemArrayValue);  
    var referenceNodeInput = document.querySelector('#list' + whichList + '_itemInputList' + previousArrayValue);

    // Insert the new node before the reference node
    referenceNodeButton.parentNode.insertBefore(targetElementButton, referenceNodeButton);
    referenceNodeInput.parentNode.insertBefore(targetElementInput, referenceNodeInput);

    //Swaps the two item values inside the array
    var b = itemsList[whichList][previousArrayIndex];
    itemsList[whichList][previousArrayIndex] = itemsList[whichList][itemArrayIndex];
    itemsList[whichList][itemArrayIndex] = b;

    // console.log(itemsList[whichList]);

    //Updates the button visibility and page numbers
    setItemAttributes(whichList);
    
    adjustElements();

    if (where == 'up'){
        scrollToElement('list'+whichList+'_item'+itemArrayValue, "smooth", "start", "nearest");  
    }
    else if(where == 'down'){
        scrollToElement('list'+whichList+'_item'+previousArrayValue, "smooth", "end", "nearest");
    }
}

//Creates a filelist to allow Input type file to accept compressed image.
function FileListItem(a) {
   
  a = [].slice.call(Array.isArray(a) ? a : arguments);
  for (var c, b = c = a.length, d = !0; b-- && d;) d = a[b] instanceof File;
  if (!d) throw new TypeError("expected argument to FileList is File or array of File objects");
  for (b = (new ClipboardEvent("")).clipboardData || new DataTransfer; c--;) b.items.add(a[c]);
  return b.files;
}
 
//Compresses the uploaded image before submission. Image Convertion api first compresses it and makes it a blob.
//The blob is then added to a FileList Array. (This is done because Input type file only accepts FileList due to security)
function compressImage(id, whichList, size, defaultPreviewSrc){

  uploadReady = 0;

  const uncompressedFile = document.getElementById('list' + whichList + '_itemInputList' + id).files[0];

  imageConversion.compressAccurately(uncompressedFile, size).then(res=>{
    //The res in the promise is a compressed Blob type (which can be treated as a File type) file;
   
    compressedImage = [];
    resizedFile = new File([res], uncompressedFile.name, uncompressedFile);
  
    compressedImage.push(resizedFile);

    //Updating the input filelist fails on iOS due to DataTransfer in the FileListItem(a) function not being supported.
    //This try/catch disables Compression and just uses the raw image.
    try {
        fileList = new FileListItem(compressedImage);
    }
    catch(error) {

        if(limitFileDueToSize(document.getElementById('list' + whichList + '_itemInputList' + id).files[0])){

            showPopup('list' + whichList + '_fileTooLargePopup' + id);
            document.getElementById('list' + whichList + '_itemInputList' + id).value = '';
            document.getElementById('list' + whichList + '_uploadPreview' + id).src = defaultPreviewSrc;
            checkRemoveButton(id, whichList);
            uploadReady = 1;
            return;

        }

        uploadReady = 1;
        updateSelectedMb();     
        return;
    }
  
    //This code runs if image compression does not fail.
    document.getElementById('list' + whichList + '_uploadPreview' + id).src = window.URL.createObjectURL(res);
    document.getElementById('list' + whichList + '_itemInputList' + id).files = fileList;

    if(limitFileDueToSize(document.getElementById('list' + whichList + '_itemInputList' + id).files[0])){
  
        showPopup('list' + whichList + '_fileTooLargePopup' + id);
        document.getElementById('list' + whichList + '_itemInputList' + id).value = '';
        document.getElementById('list' + whichList + '_uploadPreview' + id).src = defaultPreviewSrc;
        checkRemoveButton(id, whichList);
        uploadReady = 1;
        return;
    }
    
    updateSelectedMb();
    uploadReady = 1;
  })
}

function limitFileDueToSize(file){
    if(bytes_to_megabytes(file.size) > 8){
        return true;
    }
    else {
        return false;
    }
    
}


function checkFileTypeUniversal(file, requiredFileType){

    var fileTypeAccepted = false;
    var fileType = file[0].type;

    if (requiredFileType == 'image/'){

        if (fileType.substring(0, 6) == 'image/' ){
            fileTypeAccepted = true;
        }

    }
    else {
        fileTypeAccepted = false;
    }

    return fileTypeAccepted;
}


function checkFileType(file, id, whichList){

    var fileTypeAccepted = false;
    var fileType = file[0].type;

    if (whichList == 0){
        if (fileType.substring(0, 6) == 'image/' ){
            fileTypeAccepted = true;
        }        
        else{
            document.getElementById('list' + whichList + '_itemInputList' + id).value = '';
        }
    }
    else if (whichList == 1){

        if (fileType.substring(0, 6) == 'audio/' ){
            if (fileType != 'audio/webm'){
                fileTypeAccepted = true;
            }
            else {
                document.getElementById('list' + whichList + '_itemInputList' + id).value = '';
            }
            
        }     
        else{
            document.getElementById('list' + whichList + '_itemInputList' + id).value = '';
        }   
        
    }
    else {
        fileTypeAccepted = false;
        document.getElementById('list' + whichList + '_itemInputList' + id).value = '';
    }

    return fileTypeAccepted;
}

function updateSelectedMb(){
    
    totalBytes = 0;
    
    function loopEachList(listArray, whichList){
        function loopEachItem(item, whichItem, whichList){
        //    console.log('List: ' + whichList);
        //    console.log('Index: ' + whichItem);
        //    console.log('Value: ' + item);
            if (document.getElementById('list' + whichList + '_itemInputList' + item).value != ''){
                totalBytes += document.getElementById('list' + whichList + '_itemInputList' + item).files[0].size;
                // console.log(bytes_to_megabytes(document.getElementById('list' + whichList + '_itemInputList' + item).files[0].size));        
            }
            
        }

        listArray.forEach((item, whichItem) => loopEachItem(item, whichItem, whichList));
    }
    itemsList.forEach(loopEachList);

    if (totalBytes != 0) {
        var total = Math.ceil(bytes_to_megabytes(totalBytes).toString());
        // alert(total);
        document.getElementById('totalMb').innerHTML = 'Selected Files (150MB Limit): ' + total + 'MB';
        // alert(Math.ceil(bytes_to_megabytes(totalBytes)));

    }
    else {
        document.getElementById('totalMb').innerHTML = 'Selected Files (150MB Limit): 0MB';
    
    }
    
}



// Creates an area that allows a droppable Image for upload. 
function activateDropArea(id, whichList){

    let dropArea = document.getElementById('list' + whichList + '_dropArea' + id);

    dropArea.ondragover = dropArea.ondragenter = function(evt) {
        evt.preventDefault();
    };

    dropArea.ondrop = function(evt) {
        
        evt.preventDefault();
        //Checks filetype before accepting on drop input.
        if(!checkFileType(evt.dataTransfer.files, id, whichList)){
            showPopup('list' + whichList + '_invalidFilePopup' + id);
            return;
        }

        document.getElementById('list' + whichList + '_itemInputList' + id).files = evt.dataTransfer.files;

        if(whichList == 0)
        {
            compressImage(id, whichList, 2000, './imageNotFound.jpg');
            // document.getElementById('list' + whichList + '_uploadPreview' + id).src = window.URL.createObjectURL(evt.dataTransfer.files[evt.dataTransfer.files.length - 1]);
        }
        else if (whichList == 1){
             // Create a blob that we can use as an src for our audio element
            
            document.getElementById('list' + whichList + '_uploadPreview' + id).pause();
            document.getElementsByClassName('play-pause-btn__icon')[itemsList[whichList].indexOf(id)].attributes.d.value = 'M18 12L0 24V0';
            document.getElementById('list' + whichList + '_audioFileName' + id).textContent = document.getElementById('list' + whichList + '_itemInputList' + id).files[0].name;

            const urlObj = URL.createObjectURL(evt.dataTransfer.files[0]);
            document.getElementById('list' + whichList + '_uploadPreview' + id).src = urlObj;

            updateSelectedMb();
        }

        
        
        evt.preventDefault();
    };

}

function isiOS() {
        return [
              'iPad Simulator',
              'iPhone Simulator',
              'iPod Simulator',
              'iPad',
              'iPhone',
              'iPod'
            ].includes(navigator.platform)
            // iPad on iOS 13 detection
            || (navigator.userAgent.includes("Mac") && "ontouchend" in document)
      }


function checkIfUploadReady(){
   
    if (!checkIfEmpty('songTitleInput')){
        
        if (bytes_to_megabytes(totalBytes) > 150) {
            
            showPopup('fileSizeLimitReached');
            event.preventDefault();
            return false;
        }
    }

    if (uploadReady == 1) {
        return true;
    }
    else {
        event.preventDefault();
        return false;
    }
}


//Makes sure song name field is not empty at submit
function checkIfEmpty(id) {
    var x = document.getElementById(id).value;
    if (x.length == "") {
        showPopup('emptyGroup' + id);
        scrollToElement('emptyGroupsongTitleInput', "smooth", "start", "nearest");
        event.preventDefault();
        return true;
    } 
    else
    {
        return false;
    }
    
    
}

 
var inputsToCheck = ['numberInput', 'songTitleInput', 'lyricsInput', 'list1_audioLabelInput1', 'list2_linkLabelInput1', 'list2_linkInput1'];
var bannedChar = "\\'<>";
inputsToCheck.forEach(addCheckInputFunctions);

function addCheckInputFunctions(elementId, index) {

    var maximumLength = document.getElementById(elementId).maxLength;
   
    // Check input function for Windows
    document.getElementById(elementId).onkeypress = function(e) {    
        if(document.getElementById(elementId).value.length >= maximumLength){
            showPopup('nameLong' + elementId);
            return false;
        }
        var chr = String.fromCharCode(e.which);
    
        if (bannedChar.indexOf(chr) >= 0){
            showPopup('charError' + elementId);
            return false;
        }       
            
    }


    // Check input function for Android phones
    var input_field = document.getElementById(elementId);

    input_field.addEventListener('textInput', function(e) {

        // e.data will be the 1:1 input you done
        var char = e.data; // In our example = "a"
        // Stop processing if "a" is pressed
        if (bannedChar.indexOf(char) >= 0){
            showPopup('charError' + elementId);
            e.preventDefault();
            return false;
        }       

        if(document.getElementById(elementId).value.length >= maximumLength){
            showPopup('nameLong' + elementId);
            e.preventDefault();
            return false;
        }  
        
    });



    // Show popup if paste is too long
    document.getElementById(elementId).onpaste = function(e) {

        if(document.getElementById(elementId).value.length >= maximumLength){
            showPopup('nameLong' + elementId);
            return false;
        }   
        
        // YOUTUBE link detection for link items 
        if (elementId.includes("linkInput")) {
            clipboardData = e.clipboardData || window.clipboardData;
            pastedData = clipboardData.getData('Text');

            if (pastedData.includes("&list=") || pastedData.includes("?list=")){
                document.getElementById(elementId + '_linkType').value = '2';
            }
            else if (pastedData.includes("&v=") || pastedData.includes("?v=") || pastedData.includes("youtu.be")) {
                document.getElementById(elementId + '_linkType').value = '1';
            }
            else {
                document.getElementById(elementId + '_linkType').value = '0';
            }
        }
        
    }
   
}

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

function alertTooMany(){
    showPopup('imageLimitAnalize');
    scrollToElement('imageLimitAnalize', "smooth", "center", "nearest");
}

var progressBarsArray = [];
var activeImageAnalizers = 0;

document.getElementById("scanImage").addEventListener("change", function(){
    

    if(!checkFileTypeUniversal(this.files, 'image/')){
        showPopup('invalidImageAnalize');
        return;
    }

    scrollToElement('scanImageButton', "smooth", "center", "nearest");

    // var srcArray = document.getElementById('scanImage').src;
    for (i = 0; i < document.getElementById('scanImage').files.length; i++){
        // console.log(document.getElementById('scanImage').files[i]);
        document.getElementById('scanImage').src = window.URL.createObjectURL(document.getElementById('scanImage').files[i]);
        activeImageAnalizers++;
        processImage();
        activeImageAnalizers
    }
    

    function processImage(){

        if (activeImageAnalizers <= 4){

            var src = document.getElementById('scanImage').src;

            var language = document.getElementById('languageSelection').value;
        
            Tesseract.recognize(src, language, { logger: loggerOutput => updateProgressBars(loggerOutput) }).then(({ data: { text } }) => {
 
                    // console.log(text);
                    pasteReadText(text);
                    activeImageAnalizers--;
                });
            }
        else {
            alertTooMany();
            activeImageAnalizers--;
        }

    }

    // document.getElementById('scanImage').src.foreach(processImages);

    
}, false);


function updateProgressBars(loggerOutput){
    // console.log(loggerOutput);
    if  (loggerOutput.workerId != null){
        var workerNumber = loggerOutput.workerId[7];

        if (progressBarsArray.includes(workerNumber) != true) {
            progressBarsArray.push(workerNumber);

            addHTML("progressBarContainer", '<div class="progressBarWrap" id="progressBarWrap'+workerNumber+'">'
                                            +   '<span class="progressPercentageWrap">'                      
                                                +   '<label id="progressPercentage'+workerNumber+'" class="progressPercentage" for="progressBar">0%</label>'
                                            +   '</span>'                       
                                            +   '<progress class="progressBar" id="progressBar'+workerNumber+'" value="0" max="100"></progress>'
                                        +   '</div>', 'beforeend');

           
                    
            

            // console.log(progressBarsArray);
            

        }
        
        if(loggerOutput.jobId != null && loggerOutput.progress == 1)
        {
            index = progressBarsArray.indexOf(workerNumber);
            progressBarsArray.splice(index, 1);
            removeHTML("progressBarWrap" + workerNumber);
            
            // console.log(progressBarsArray);
        }
        else if (loggerOutput.jobId != null)
        {
            document.getElementById("progressBar" + workerNumber).value = Math.floor(loggerOutput.progress * 100);
            document.getElementById("progressPercentage" + workerNumber).innerHTML = Math.floor(loggerOutput.progress * 100) + '%';
        }
    }

    

    
   
}

// Load existing images.
var existingPictures = <?php echo json_encode($picturesLookup); ?>;
if (existingPictures.length >= 1) {
    // Load first images.
    document.getElementById("list0_uploadPreview1").src = "/songshome/pictures/" + existingPictures[0]["pictureFileName"];

    // Load the rest of images.
    for (let i = 0; i < existingPictures.length; i++) {
        if (i != existingPictures.length - 1) { addItem(0) };
        document.getElementById("list0_uploadPreview" + (i+1)).src = "/songshome/pictures/" + existingPictures[i]["pictureFileName"];
    }
}


// Load existing audio files.
var existingAudio = <?php echo json_encode($musicLookup); ?>;
if (existingAudio.length >= 1) {
    // Load first audio files.
    document.getElementById("list1_uploadPreview1").src = "/songshome/music/" + existingAudio[0]["musicFileName"];
    document.getElementById("list1_audioFileName1").innerHTML = existingAudio[0]["musicName"];
    document.getElementById("list1_audioLabelInput1").value = existingAudio[0]["musicName"];

    // Load the rest of audio files.
    for (let i = 0; i < existingAudio.length; i++) {
        if (i != existingPictures.length - 1) { addItem(1) };
        document.getElementById("list1_uploadPreview" + (i+1)).src = "/songshome/music/" + existingAudio[i]["musicFileName"];
        document.getElementById("list1_audioFileName" + (i+1)).innerHTML = existingAudio[i]["musicName"];
        document.getElementById("list1_audioLabelInput" + (i+1)).value = existingAudio[i]["musicName"];
    }
}



// Load existing links.
var existingLinks = <?php echo json_encode($linksLookup); ?>;
if (existingLinks.length >= 1) {
    // Load first link.
    // console.log(existingLinks);
    document.getElementById("list2_linkLabelInput1").value = existingLinks[0]["linkName"];
    document.getElementById("list2_linkInput1").value = existingLinks[0]["linkData"];
    document.getElementById("list2_linkInput1_linkType").value = existingLinks[0]["linkType"];
    // console.log(document.getElementById("list2_linkInput1_linkType").value);
    // Load the rest of links.
    for (let i = 0; i < existingLinks.length; i++) {
        if (i != existingPictures.length - 1) { addItem(2) };
        document.getElementById("list2_linkLabelInput" + (i+1)).value = existingLinks[i]["linkName"];

        if (existingLinks[i]["linkType"] == 0) {  
            document.getElementById("list2_linkInput" + (i+1)).value = existingLinks[i]["linkData"];
        }
        else if (existingLinks[i]["linkType"] == 1) {
            
            document.getElementById("list2_linkInput" + (i+1)).value = "https://www.youtube.com/watch?v=" + existingLinks[i]["linkData"];
        }
        else if (existingLinks[i]["linkType"] == 2) {
            document.getElementById("list2_linkInput" + (i+1)).value = "https://www.youtube.com/embed/videoseries?list=" + existingLinks[i]["linkData"];
        }

        document.getElementById("list2_linkInput" + (i+1) + "_linkType").value = existingLinks[i]["linkType"];
    }
}


// Check if title is not empty on submit.
function checkIfEmpty(id) {
    var x = document.getElementById(id).value;
    if (x.length == "") {
        showPopup('emptyGroupsongTitleInput');
        return true;
    } 
    return false;
}

var oneSubmit = 0;
function submitAll() {
    document.getElementById("uploadSongForm").submit();
}
// Load details of the modified page data and submit form.
function submitUpdatedData(){
    if(checkIfEmpty('songTitleInput')) {
        scrollToElement("songTitleInput", "smooth", "center", "nearest");  
        return false;
    }

    if (oneSubmit == 0) {
        oneSubmit++;

        var imageOrder = itemsList[0];
        for (let i = 0; i < imageOrder.length; i++) {
            var orderIndex = imageOrder[i];
            addHTML("imageFileContainer", '<input name="imagesSrcOrder[]"  type="hidden" accept="text" value="' + document.getElementById("list0_uploadPreview" + orderIndex).src + '">', 'beforeend');
        }

        var audioLabelOrder = itemsList[1];
        for (let i = 0; i < audioLabelOrder.length; i++) {
            var orderIndex = audioLabelOrder[i];
            addHTML("audioFileContainer", '<input name="audioSrcOrder[]"  type="hidden" accept="text" value="' + document.getElementById("list1_uploadPreview" + orderIndex).src + '">', 'beforeend');
            addHTML("audioFileContainer", '<input name="audioLabelOrder[]"  type="hidden" accept="text" value="' + document.getElementById("list1_audioLabelInput" + orderIndex).value + '">', 'beforeend');
        }
        
        const timeout = setTimeout(submitAll, 200);
    }
}

</script>



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script type="text/javascript">
    //Disable Enter
    // var $myForm = $("#uploadSongForm");
    // $myForm.submit(function(){
    //     $myForm.submit(function(){
    //         return false;
    //     });
    // });

    //Scrolls to bottom on load.
    // jQuery(document).ready(function() {
    //     //Scroll to the bottom of the page on load.
    //     var bottomPage =  $(document).height() - $(window).height();
    //     window.scrollTo(0, bottomPage);
    //  });

     
    
</script>
</html>