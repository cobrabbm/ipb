<?php

$SQL[] = "CREATE TABLE ibf_downloads_categories (
  cid serial NOT NULL,
  cparent integer NOT NULL default 0,
  cname varchar(255) NOT NULL default '',
  cdesc text NULL,
  copen smallint NOT NULL default 0,
  cposition integer NOT NULL default 0,
  cperms text NULL,
  coptions text NULL,
  ccfields text NULL,
  cfileinfo text NULL,
  cdisclaimer text NULL,
PRIMARY KEY  (cid)
);";
$SQL[] = "CREATE INDEX ibf_downloads_categories_cparent ON ibf_downloads_categories(cparent);";
$SQL[] = "CREATE INDEX ibf_downloads_categories_cposition ON ibf_downloads_categories(cposition);";


$SQL[] = "CREATE TABLE ibf_downloads_ccontent (
  file_id integer NOT NULL default 0,
  updated integer default 0,
PRIMARY KEY  (file_id)
);";


$SQL[] = "CREATE TABLE ibf_downloads_cfields (
  cf_id serial NOT NULL,
  cf_title varchar(250) NOT NULL default '',
  cf_desc varchar(250) NOT NULL default '',
  cf_content text NULL,
  cf_type varchar(250) NOT NULL default '',
  cf_not_null smallint NOT NULL default 0,
  cf_max_input smallint NOT NULL default 0,
  cf_input_format text NULL,
  cf_file_format text NULL,
  cf_position smallint NOT NULL default 0,
PRIMARY KEY  (cf_id)
;";
$SQL[] = "CREATE INDEX ibf_downloads_cfields_cf_position ON ibf_downloads_cfields(cf_position);";


$SQL[] = "CREATE TABLE ibf_downloads_comments (
  comment_id serial NOT NULL,
  comment_fid integer NOT NULL default 0,
  comment_mid integer NOT NULL default 0,
  comment_date varchar(13) NOT NULL default '0',
  comment_open smallint NOT NULL default 0,
  comment_text text NULL,
PRIMARY KEY  (comment_id)
);";
$SQL[] = "CREATE INDEX ibf_downloads_comments_comment_fid ON ibf_downloads_comments(comment_fid);";


$SQL[] = "CREATE TABLE ibf_downloads_downloads (
  did serial NOT NULL,
  dfid integer NOT NULL default 0,
  dtime varchar(13) NOT NULL default '0',
  dip varchar(55) NOT NULL default '0',
  dmid integer NOT NULL default 0,
  dsize integer NOT NULL default 0,
  dua varchar(255) default NULL,
  dbrowsers varchar(25) NOT NULL default '',
  dos varchar(25) NOT NULL default '',
  dcountry varchar(7) NOT NULL default '',
PRIMARY KEY  (did)
);";
$SQL[] = "CREATE INDEX ibf_downloads_downloads_dfid ON ibf_downloads_downloads(dfid,dsize);";


$SQL[] = "CREATE TABLE ibf_downloads_favorites (
  fid serial NOT NULL,
  fmid integer NOT NULL default 0,
  ffid integer NOT NULL default 0,
  fupdated varchar(13) NOT NULL default '0',
  PRIMARY KEY  (fid)
);";
$SQL[] = "CREATE INDEX ibf_downloads_favorites_ffid ON ibf_downloads_favorites(ffid);";


$SQL[] = "CREATE TABLE ibf_downloads_files (
  file_id serial NOT NULL,
  file_name varchar(255) NOT NULL default '0',
  file_cat integer NOT NULL default 0,
  file_open smallint NOT NULL default 0,
  file_broken smallint NOT NULL default 0,
  file_broken_reason text NULL,
  file_filename varchar(255) NOT NULL default '0',
  file_ssname varchar(255) NOT NULL default '0',
  file_thumb varchar(255) NOT NULL default '0',
  file_views integer NOT NULL default 0,
  file_downloads integer NOT NULL default 0,
  file_submitted varchar(13) NOT NULL default '0',
  file_updated varchar(13) NOT NULL default '0',
  file_desc text NULL,
  file_size integer NOT NULL default 0,
  file_mime integer NOT NULL default 0,
  file_ssmime integer NOT NULL default 0,
  file_submitter integer NOT NULL default 0,
  file_approver integer NOT NULL default 0,
  file_approvedon varchar(13) NOT NULL default '0',
  file_topicid integer NOT NULL default 0,
  file_pendcomments smallint NOT NULL default 0,
  file_ipaddress varchar(50) NOT NULL default '0',
  file_storagetype varchar(6) NOT NULL default 'web',
  file_sub_mems text NULL,
  file_votes text NULL,
  file_rating smallint NOT NULL default 0,
  file_meta text NULL,
  file_new tinyint NOT NULL default 0,
  file_placeholder smallint NOT NULL default 0,
  file_url TEXT NULL,
  file_ssurl TEXT NULL,
  file_realname VARCHAR( 255 ) NULL,
PRIMARY KEY  (file_id)
);";
$SQL[] = "CREATE INDEX ibf_downloads_files_file_views ON ibf_downloads_files(file_views);";
$SQL[] = "CREATE INDEX ibf_downloads_files_file_downloads ON ibf_downloads_files(file_downloads);";
$SQL[] = "CREATE INDEX ibf_downloads_files_file_cat ON ibf_downloads_files(file_cat);";
$SQL[] = "CREATE INDEX ibf_downloads_files_file_submitter ON ibf_downloads_files(file_submitter);";
$SQL[] = "CREATE INDEX ibf_downloads_files_file_broken ON ibf_downloads_files(file_broken);";
$SQL[] = "CREATE INDEX ibf_downloads_files_file_open ON ibf_downloads_files(file_open);";
$SQL[] = "CREATE INDEX ibf_downloads_files_file_rating ON ibf_downloads_files(file_rating);";
$SQL[] = "CREATE INDEX ibf_downloads_files_file_desc ON ibf_downloads_files(file_desc text_pattern_ops);";
$SQL[] = "CREATE INDEX ibf_downloads_files_file_meta ON ibf_downloads_files(file_meta text_pattern_ops);";


$SQL[] = "CREATE TABLE ibf_downloads_filestorage (
  storage_id integer NOT NULL default 0,
  storage_file text NULL,
  storage_ss text NULL,
  storage_thumb text NULL,
PRIMARY KEY (storage_id)
);";


$SQL[] = "CREATE TABLE ibf_downloads_fileviews (
  view_id serial NOT NULL,
  view_fid integer NOT NULL default 0,
PRIMARY KEY  (view_id)
);";


$SQL[] = "CREATE TABLE ibf_downloads_ip2ext (
  ip bigint NOT NULL default 0,
  country char(2) NOT NULL default ''
);";
$SQL[] = "CREATE INDEX ibf_downloads_ip2ext_ip ON ibf_downloads_ip2ext(ip);";


$SQL[] = "CREATE TABLE ibf_downloads_mime (
  mime_id serial NOT NULL,
  mime_extension varchar(18) NOT NULL default '',
  mime_mimetype varchar(255) NOT NULL default '',
  mime_file text NULL,
  mime_screenshot text NULL,
  mime_inline text NULL,
  mime_img text NULL,
PRIMARY KEY  (mime_id)
);";


$SQL[] = "CREATE TABLE ibf_downloads_mimemask (
  mime_maskid serial NOT NULL,
  mime_masktitle varchar(255) NOT NULL default '0',
PRIMARY KEY  (mime_maskid)
);";


$SQL[] = "CREATE TABLE ibf_downloads_mods (
  modid serial NOT NULL,
  modtype smallint NOT NULL default 0,
  modgmid varchar(255) NOT NULL default '0',
  modcanedit smallint NOT NULL default 0,
  modcandel smallint NOT NULL default 0,
  modcanapp smallint NOT NULL default 0,
  modcanbrok smallint NOT NULL default 0,
  modcancomments smallint NOT NULL default 0,
  modcats text NULL,
PRIMARY KEY  (modid)
);";


$SQL[] = "CREATE TABLE ibf_downloads_upgrade_history (
  idm_upgrade_id serial NOT NULL,
  idm_version_id integer NOT NULL default 0,
  idm_version_human varchar(200) NOT NULL default '',
  idm_upgrade_date integer NOT NULL default 0,
  idm_upgrade_mid integer NOT NULL default 0,
  idm_upgrade_notes text NULL,
PRIMARY KEY  (idm_upgrade_id)
);";


$SQL[] = "CREATE TABLE ibf_downloads_filebackup (
  b_id serial NOT NULL,
  b_fileid integer NOT NULL DEFAULT 0,
  b_filetitle VARCHAR( 255 ) NOT NULL DEFAULT '0',
  b_filedesc TEXT NULL ,
  b_filename VARCHAR( 255 ) NOT NULL DEFAULT '0',
  b_ssname VARCHAR( 255 ) NOT NULL DEFAULT '0',
  b_thumbname VARCHAR( 255 ) NOT NULL DEFAULT '0',
  b_filemime integer NOT NULL DEFAULT 0,
  b_ssmime integer NOT NULL DEFAULT 0,
  b_filemeta TEXT NULL ,
  b_storage VARCHAR( 10 ) NOT NULL DEFAULT 'web',
  b_hidden smallint NOT NULL DEFAULT 0,
  b_backup VARCHAR( 13 ) NOT NULL DEFAULT '0',
  b_updated VARCHAR( 13 ) NOT NULL DEFAULT '0',
  b_fileurl varchar(255) NULL ,
  b_ssurl varchar(255) NULL ,
  b_filereal VARCHAR( 255 ) NOT NULL DEFAULT '0',
PRIMARY KEY (b_id )
);";
$SQL[] = "CREATE INDEX ibf_downloads_filebackup_fileid ON ibf_downloads_filebackup( b_fileid );";


$SQL[] = "ALTER TABLE ibf_groups ADD idm_restrictions TEXT NULL;";

?>