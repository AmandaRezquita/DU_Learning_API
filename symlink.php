<?php 

$targetFolder = $_SERVER['DOCUMENT_ROOT'].'/DU_Learning_API/storage/app/public';
$linkFolder = $_SERVER['DOCUMENT_ROOT'].'/DU_Learning_API/public/storage';

if (!file_exists($linkFolder)) {
    if (symlink($targetFolder, $linkFolder)) {
        echo 'Symlink process successfully completed.';
    } else {
        echo 'Failed to create symlink. Check permissions.';
    }
} else {
    echo 'Symlink already exists.';
}

?>
