<?php

$SQL[] = "ALTER TABLE ibf_downloads_files ADD file_new TINYINT( 1 ) DEFAULT '0' NOT NULL ,
			ADD file_placeholder TINYINT( 1 ) DEFAULT '0' NOT NULL ;";

?>