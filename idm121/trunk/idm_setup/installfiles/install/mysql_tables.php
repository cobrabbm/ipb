<?php

$SQL[] = "CREATE TABLE ibf_downloads_categories (
  cid int(10) NOT NULL auto_increment,
  cparent int(10) NOT NULL default '0',
  cname varchar(255) NOT NULL default '',
  cdesc mediumtext NULL,
  copen tinyint(1) NOT NULL default '0',
  cposition int(10) NOT NULL default '0',
  cperms text NULL,
  coptions text NULL,
  ccfields text NULL,
  cfileinfo text NULL,
  cdisclaimer mediumtext NULL,
  PRIMARY KEY  (cid),
  KEY cparent (cparent),
  KEY cposition (cposition) );";

$SQL[] = "CREATE TABLE ibf_downloads_ccontent (
  file_id mediumint(8) NOT NULL default '0',
  updated int(10) default '0',
  PRIMARY KEY  (file_id) );";

$SQL[] = "CREATE TABLE ibf_downloads_cfields (
  cf_id smallint(5) NOT NULL auto_increment,
  cf_title varchar(250) NOT NULL default '',
  cf_desc varchar(250) NOT NULL default '',
  cf_content text NULL,
  cf_type varchar(250) NOT NULL default '',
  cf_not_null tinyint(1) NOT NULL default '0',
  cf_max_input smallint(6) NOT NULL default '0',
  cf_input_format text NULL,
  cf_file_format mediumtext NULL,
  cf_position smallint(6) NOT NULL default '0',
  cf_topic TINYINT( 1 ) NOT NULL DEFAULT '0',
  cf_search TINYINT( 1 ) NOT NULL DEFAULT '0',
  PRIMARY KEY  (cf_id),
  KEY cf_position (cf_position) );";

$SQL[] = "CREATE TABLE ibf_downloads_comments (
  comment_id int(10) NOT NULL auto_increment,
  comment_fid int(10) NOT NULL default '0',
  comment_mid mediumint(8) NOT NULL default '0',
  comment_date varchar(13) NOT NULL default '0',
  comment_open tinyint(1) NOT NULL default '0',
  comment_text mediumtext NULL,
  comment_append_edit TINYINT( 1 ) NOT NULL DEFAULT '0',
  comment_edit_time VARCHAR( 11 ) NOT NULL DEFAULT '0',
  comment_edit_name VARCHAR( 255 ) NULL ,
  ip_address VARCHAR( 32 ) NULL ,
  use_sig TINYINT( 1 ) NOT NULL DEFAULT '1',
  use_emo TINYINT( 1 ) NOT NULL DEFAULT '1',
  PRIMARY KEY  (comment_id),
  KEY comment_fid (comment_fid) );";

$SQL[] = "CREATE TABLE ibf_downloads_downloads (
  did int(10) NOT NULL auto_increment,
  dfid int(10) NOT NULL default '0',
  dtime varchar(13) NOT NULL default '0',
  dip varchar(55) NOT NULL default '0',
  dmid mediumint(8) NOT NULL default '0',
  dsize int(10) NOT NULL default '0',
  dua varchar(255) default NULL,
  dbrowsers varchar(25) NOT NULL default '',
  dos varchar(25) NOT NULL default '',
  dcountry varchar(7) NOT NULL default '',
  PRIMARY KEY  (did),
  KEY dfid (dfid,dsize) );";


$SQL[] = "CREATE TABLE ibf_downloads_favorites (
  fid int(10) NOT NULL auto_increment,
  fmid mediumint(8) NOT NULL default '0',
  ffid int(10) NOT NULL default '0',
  fupdated varchar(13) NOT NULL default '0',
  PRIMARY KEY  (fid),
  KEY ffid (ffid) );";

$SQL[] = "CREATE TABLE ibf_downloads_files (
  file_id int(10) NOT NULL auto_increment,
  file_name varchar(255) NOT NULL default '0',
  file_cat mediumint(8) NOT NULL default '0',
  file_open tinyint(1) NOT NULL default '0',
  file_broken tinyint(1) NOT NULL default '0',
  file_broken_reason text NULL,
  file_broken_info VARCHAR( 255 ) NULL,
  file_filename varchar(255) NOT NULL default '0',
  file_ssname varchar(255) NOT NULL default '0',
  file_thumb varchar(255) NOT NULL default '0',
  file_views int(10) NOT NULL default '0',
  file_downloads int(10) NOT NULL default '0',
  file_submitted varchar(13) NOT NULL default '0',
  file_updated varchar(13) NOT NULL default '0',
  file_desc text NULL,
  file_size int(10) NOT NULL default '0',
  file_mime mediumint(8) NOT NULL default '0',
  file_ssmime mediumint(8) NOT NULL default '0',
  file_submitter mediumint(8) NOT NULL default '0',
  file_approver mediumint(8) NOT NULL default '0',
  file_approvedon varchar(13) NOT NULL default '0',
  file_topicid int(10) NOT NULL default '0',
  file_pendcomments smallint(4) NOT NULL default '0',
  file_ipaddress varchar(50) NOT NULL default '0',
  file_storagetype enum('web','nonweb','ftp','db') NOT NULL default 'web',
  file_sub_mems mediumtext NULL,
  file_votes text NULL,
  file_rating smallint(5) NOT NULL default '0',
  file_meta text NULL,
  file_new tinyint( 1 )NOT NULL default '0', 
  file_placeholder tinyint(1) NOT NULL default '0',
  file_url TINYTEXT NULL,
  file_ssurl TINYTEXT NULL,
  file_realname VARCHAR( 255 ) NULL,
  PRIMARY KEY  (file_id),
  KEY file_views (file_views),
  KEY file_downloads (file_downloads),
  KEY file_cat (file_cat),
  KEY file_submitter (file_submitter),
  KEY file_broken (file_broken),
  KEY file_open (file_open),
  KEY file_rating (file_rating)
) TYPE=MyISAM";

$SQL[] = "CREATE TABLE ibf_downloads_filestorage (
  storage_id int(10) NOT NULL default '0',
  storage_file blob,
  storage_ss blob,
  storage_thumb blob,
  UNIQUE KEY storage_id (storage_id) );";

$SQL[] = "CREATE TABLE ibf_downloads_fileviews (
  view_id mediumint(10) NOT NULL auto_increment,
  view_fid int(10) NOT NULL default '0',
  PRIMARY KEY  (view_id) );";

$SQL[] = "CREATE TABLE ibf_downloads_ip2ext (
  ip int(11) unsigned NOT NULL default '0',
  country char(2) NOT NULL default '',
  KEY ip (ip) );";

$SQL[] = "CREATE TABLE ibf_downloads_mime (
  mime_id int(10) NOT NULL auto_increment,
  mime_extension varchar(18) NOT NULL default '',
  mime_mimetype varchar(255) NOT NULL default '',
  mime_file text NULL,
  mime_screenshot text NULL,
  mime_inline text NULL,
  mime_img text NULL,
  PRIMARY KEY  (mime_id) );";

$SQL[] = "CREATE TABLE ibf_downloads_mimemask (
  mime_maskid int(10) NOT NULL auto_increment,
  mime_masktitle varchar(255) NOT NULL default '0',
  PRIMARY KEY  (mime_maskid) );";

$SQL[] = "CREATE TABLE ibf_downloads_mods (
  modid mediumint(8) NOT NULL auto_increment,
  modtype tinyint(1) NOT NULL default '0',
  modgmid varchar(255) NOT NULL default '0',
  modcanedit tinyint(1) NOT NULL default '0',
  modcandel tinyint(1) NOT NULL default '0',
  modcanapp tinyint(1) NOT NULL default '0',
  modcanbrok tinyint(1) NOT NULL default '0',
  modcancomments tinyint(1) NOT NULL default '0',
  modcats mediumtext NULL,
  PRIMARY KEY  (modid) );";

$SQL[] = "CREATE TABLE ibf_downloads_upgrade_history (
  idm_upgrade_id int(10) NOT NULL auto_increment,
  idm_version_id int(10) NOT NULL default '0',
  idm_version_human varchar(200) NOT NULL default '',
  idm_upgrade_date int(10) NOT NULL default '0',
  idm_upgrade_mid int(10) NOT NULL default '0',
  idm_upgrade_notes text NULL,
  PRIMARY KEY  (idm_upgrade_id) );";


$SQL[] = "CREATE TABLE ibf_downloads_filebackup (
b_id INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
b_fileid INT( 10 ) NOT NULL DEFAULT '0',
b_filetitle VARCHAR( 255 ) NOT NULL DEFAULT '0',
b_filedesc TEXT NULL ,
b_filename VARCHAR( 255 ) NOT NULL DEFAULT '0',
b_ssname VARCHAR( 255 ) NOT NULL DEFAULT '0',
b_thumbname VARCHAR( 255 ) NOT NULL DEFAULT '0',
b_filemime MEDIUMINT( 8 ) NOT NULL DEFAULT '0',
b_ssmime MEDIUMINT( 8 ) NOT NULL DEFAULT '0',
b_filemeta TEXT NULL ,
b_storage VARCHAR( 10 ) NOT NULL DEFAULT 'web',
b_hidden TINYINT( 1 ) NOT NULL DEFAULT '0',
b_backup VARCHAR( 13 ) NOT NULL DEFAULT '0',
b_updated VARCHAR( 13 ) NOT NULL DEFAULT '0',
b_fileurl TINYTEXT NULL ,
b_ssurl TINYTEXT NULL ,
b_filereal VARCHAR( 255 ) NOT NULL DEFAULT '0',
INDEX ( b_fileid ) );";

$SQL[] = "ALTER TABLE ibf_groups ADD idm_restrictions TEXT NULL;";

$SQL[] = "CREATE TABLE ibf_downloads_sessions (
dsess_id VARCHAR( 32 ) NOT NULL ,
dsess_mid INT( 10 ) NOT NULL DEFAULT '0',
dsess_ip VARCHAR( 32 ) NULL ,
dsess_file INT( 10 ) NOT NULL DEFAULT '0',
dsess_start VARCHAR( 13 ) NOT NULL DEFAULT '0',
dsess_end VARCHAR( 13 ) NOT NULL DEFAULT '0',
PRIMARY KEY ( dsess_id ) ,
INDEX ( dsess_mid , dsess_ip )
) ENGINE = MYISAM ;";

?>