var uploadReady = 1;

//Used to fix a freezing issue on Android by setting accept image type to accept all filetypes.
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

function limitFileDueToSize(file){
    if(bytes_to_megabytes(file.size) > 8){
        return true;
    }
    else {
        return false;
    }
    
}

function checkIfUploadReady(){
   
    //Code will not work without proper html/js added.
    // if (!checkIfEmpty('songTitleInput')){
        
    //     if (bytes_to_megabytes(totalBytes) > 150) {
            
    //         showPopup('fileSizeLimitReached');
    //         event.preventDefault();
    //         return false;
    //     }
    // }

    if (uploadReady == 1) {
        return true;
    }
    else {
        event.preventDefault();
        return false;
    }
}

function checkFileType(file, id, whichList){

    var fileTypeAccepted = false;
    var fileType = file[0].type;

    if (whichList == 0){
        if (fileType.substring(0, 6) == 'image/' ){
            fileTypeAccepted = true;
        }        
        else {
         
            document.getElementById('list' + whichList + '_itemInputList' + id).value = '';
        }
    }
    else if (whichList == 1){

        if (fileType.substring(0, 6) == 'audio/' ){
            if (fileType != 'audio/webm' && fileType != 'audio/ogg'){
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

//Hides remove button if there is no input selected when there is only one item in the array
function checkOneRemoveButton(id, whichList){
    if (whichList == 0) {

            if (document.getElementById('list' + whichList + '_itemInputList' + id).value != ''){
                
                document.getElementById('list' + whichList + '_removeButton' + id).style.visibility = "visible";
            }
            else {
        
                document.getElementById('list' + whichList + '_removeButton' + id).style.visibility = "hidden";
                
            }
        
    }
}

//Removes the input and preview.
function removeInput(id, whichList, defaultPreviewSrc){
    // alert(document.getElementById('list' + whichList + '_itemInputList' + id).value);

    document.getElementById('list' + whichList + '_itemInputList' + id).value = '';
    document.getElementById('list' + whichList + '_uploadPreview' + id).src = defaultPreviewSrc;
    // updateSelectedMb();
        
    checkOneRemoveButton(id, whichList);  
    
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
            
            // document.getElementById('list' + whichList + '_uploadPreview' + id).pause();
            // document.getElementsByClassName('play-pause-btn__icon')[itemsList[whichList].indexOf(id)].attributes.d.value = 'M18 12L0 24V0';
            // document.getElementById('list' + whichList + '_audioFileName' + id).textContent = document.getElementById('list' + whichList + '_itemInputList' + id).files[0].name;

            // const urlObj = URL.createObjectURL(evt.dataTransfer.files[0]);
            // document.getElementById('list' + whichList + '_uploadPreview' + id).src = urlObj;

            // updateSelectedMb();
        }

        
        
        evt.preventDefault();
    };

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
              checkOneRemoveButton(id, whichList);
              uploadReady = 1;
              return;
  
          }
  
          uploadReady = 1;
        //   updateSelectedMb();     
          return;
      }
    
      //This code runs if image compression does not fail.
      document.getElementById('list' + whichList + '_uploadPreview' + id).src = window.URL.createObjectURL(res);
      document.getElementById('list' + whichList + '_itemInputList' + id).files = fileList;
  
      if(limitFileDueToSize(document.getElementById('list' + whichList + '_itemInputList' + id).files[0])){
    
          showPopup('list' + whichList + '_fileTooLargePopup' + id);
          document.getElementById('list' + whichList + '_itemInputList' + id).value = '';
          document.getElementById('list' + whichList + '_uploadPreview' + id).src = defaultPreviewSrc;
          checkOneRemoveButton(id, whichList);
          uploadReady = 1;
          return;
      }
      
    //   updateSelectedMb();
      uploadReady = 1;
    })
  }

