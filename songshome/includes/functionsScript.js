
// Used to add the specified HTML on the page after the specified element.
function addHTML(elementId, html, location) { 
    document.getElementById(elementId).insertAdjacentHTML(location, html);
} 

// Used to remove the specified HTML on the page after the specified element.
function removeHTML(elementId){
    document.getElementById(elementId).remove();
}

function scrollToElement(elementId, behaviorType, blockType, inlineType){
    var element = document.getElementById(elementId);
    element.scrollIntoView({behavior: behaviorType, block: blockType, inline: inlineType});
}

function bytes_to_megabytes(bytes){
    return bytes / 1048576;
}
