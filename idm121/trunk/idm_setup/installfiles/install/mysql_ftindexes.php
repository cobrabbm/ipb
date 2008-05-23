<?php

$SQL[] = "ALTER TABLE ibf_downloads_files ADD FULLTEXT(file_desc)";
$SQL[] = "ALTER TABLE ibf_downloads_files ADD FULLTEXT(file_meta)";

?>