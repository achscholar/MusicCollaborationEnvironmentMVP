<?php
//THIS FUNCTION SHOULD ONLY BE USED AFTER THE CHECKKEY FUNCTION
function checkGroupSiteDataUsage($parentOrSubgroupId, $uploadSizeMB, &$db)
{   
    
    $groupTotalDataUsageMB = 0;

    //Look up the requested group
    $stmt = $db->prepare("SELECT `id`,`groupPictureMBSize`,`dataLimitGB`,`locationIndex` FROM `groups` WHERE `id` = :parentorsubgroupid");
    $stmt->bindParam('parentorsubgroupid', $parentOrSubgroupId);
    $stmt->execute();
    $getParentGroupQuery = $stmt->fetchAll();

    //Check if the group exists.
    if ($getParentGroupQuery) {

        //Look up parent group if the provided group is a subgroup. Continue to determine parent group data limit.
        if ($getParentGroupQuery[0]['locationIndex'] != NULL){

            //Trims off the comma at the end of every location index and converts index to array.
            $getParentGroupQuery[0]['locationIndex'] = substr($getParentGroupQuery[0]['locationIndex'], 0, -1);
            $locationArray = explode(",", $getParentGroupQuery[0]['locationIndex']);
    
            if($locationArray[0] != ""){
                $parentGroupId = $locationArray[0];
                $stmt = $db->prepare("SELECT `id`,`groupPictureMBSize`,`dataLimitGB`,`locationIndex` FROM `groups` WHERE `id` = :parentid");
                $stmt->bindParam('parentid', $parentGroupId);
                $stmt->execute();
                $getParentGroupQuery = $stmt->fetchAll();
     
            }
            else
            {
                //Error in the location index!
                print(" The group location index is corrupt!");
                return false;
            }
        } 

        //Create a list of all sub groups inside the parent group
        $locationIndexSearchValue = $getParentGroupQuery[0]['id'].",%";
        $stmt = $db->prepare("SELECT `id`,`groupPictureMBSize`,`dataLimitGB`,`locationIndex` FROM `groups` WHERE `locationIndex` LIKE :locationindexsearchvalue");
        $stmt->bindParam('locationindexsearchvalue', $locationIndexSearchValue);
        $stmt->execute();
        $getAllSubGroupsQuery = $stmt->fetchAll();

        //Combines the parent group array and sub-group arrays.
        $allGroupsToCheck = array_merge($getParentGroupQuery, $getAllSubGroupsQuery);

        //Add the data usage for the parent group picture, sub-group pictures, song audios inside the groups, and song pictures inside the groups.
        if ($allGroupsToCheck){
            foreach ($allGroupsToCheck as $group) { 

                //Adds the data usage for the group picture.
                $groupTotalDataUsageMB += $group['groupPictureMBSize'];
    
                //Looks up every song inside the group.
                $groupId = $group['id'];
                $stmt = $db->prepare("SELECT `id` FROM `songs` WHERE `groupId` = :groupid");
                $stmt->bindParam('groupid', $groupId);
                $stmt->execute();
                $getGroupSongsQuery = $stmt->fetchAll();
                
                if ($getGroupSongsQuery) {
                    
                    //Looks up the audios and pictures details for each specific song.
                    foreach ($getGroupSongsQuery as $song) {
               
                        $songId = $song["id"];

                        //Looks up song pictures data usage.
                        $stmt = $db->prepare("SELECT `pictureId`, `mbSize` FROM `pictures` WHERE `songId` = :songid");
                        $stmt->bindParam('songid', $songId);
                        $stmt->execute();
                        $getSongPicturesQuery = $stmt->fetchAll();

                        //Adds song pictures data usage.
                        if ($getSongPicturesQuery) {
                            foreach ($getSongPicturesQuery as $picture) {
                                $groupTotalDataUsageMB += $picture["mbSize"];
                                // displayVar($picture["mbSize"]);
                            }
                        }

                        //Looks up song audio data usage.
                        $stmt = $db->prepare("SELECT `musicId`, `mbSize` FROM `music` WHERE `songId` = :songid");
                        $stmt->bindParam('songid', $songId);
                        $stmt->execute();
                        $getSongAudiosQuery = $stmt->fetchAll();

                        //Adds song audios data usage.
                        if ($getSongAudiosQuery) {
                            foreach ($getSongAudiosQuery as $audio) {
                                $groupTotalDataUsageMB += $audio["mbSize"];
                                // displayVar($audio["mbSize"]);
                            }
                        }
                                         
                    }

                }

               
            }

            //Adds the size of the attemped upload.
            $groupTotalDataUsageMB += $uploadSizeMB;

            $groupTotalDataUsageGB = megabytes_to_gigabytes($groupTotalDataUsageMB);
            $parentGroupDataLimitGB = $getParentGroupQuery[0]["dataLimitGB"];

            // var_dump($groupTotalDataUsageGB);

            if ($groupTotalDataUsageGB <= $parentGroupDataLimitGB) {
                return true;
            }
            else 
            {
                print($parentGroupDataLimitGB."GB Site Data Limit Has Been Reached!");
                return false;
            }

        }
        else {
            print(" Data Usage Lookup Error!");
            return false;
        }
        
    }
    else 
    {
        print(" Could not lookup data usage. The group does not exist!");
        return false;
    }
    
    

    

 

}

?>