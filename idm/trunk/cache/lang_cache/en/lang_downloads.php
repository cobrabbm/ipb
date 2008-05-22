<?php

// Language file for IDM

$lang = array(

// 1.2.0
'dl_restrict_sim'			=> 'You may not download any more files until your other downloads are complete',
'dl_estimate_time'			=> 'Estimated Download Times',
'dl_estimate_56k'			=> 'Dialup (56Kbps)',
'dl_estimate_dsl'			=> 'DSL (768Kbps)',
'dl_estimate_t1'			=> 'T1 (1.5Mbps)',
'dl_estimate_cable'			=> 'Cable (3Mbps)',
'dl_estimate_fios'			=> 'Fios (10Mbps)',

'dl_seconds'				=> ' seconds ',
'dl_minutes'				=> ' minutes ',
'dl_hours'					=> ' hours ',
'no_downloads_cats_created'	=> 'There have been no categories created yet',
'idm_alreadyuploaded'		=> 'Existing file present.',
'idm_reuploadfile'			=> 'Upload a new file to replace existing one?',
'idm_upload_progress'		=> 'Upload Progress...',
'idm_uploaded_st'			=> 'Uploaded',
'idm_uploaded_of'			=> 'of',
'idm_uploaded_bytes'		=> 'bytes',
'idm_uploaded_bps'			=> 'kb/sec',
'idm_uploaded_est'			=> 'Estimated seconds remaining: ',

'idm_portal_random'			=> 'Random Files',

'searchin_idmboth'			=> 'Both titles and descriptions',
'searchin_idmtitles'		=> 'Titles only',
'searchin_idmdesc'			=> 'Descriptions only',
'idm_email_file'			=> 'Email link to this file',
'email_email'				=> 'Email Address',
'email_content'				=> 'Custom Message',
'email_default'				=> "Hey, check out this great file I found!",
'email_send'				=> 'Send Email',
'idm_emails_onlymembers'	=> 'Only members are allowed to email file links',
'idm_emails_noid'			=> 'We could not find the file you wished to email a link to',
'idm_emails_noemail'		=> 'You must enter a valid email address to send to',
'email_link_sub'			=> '%s wants to share a link with you!',
'thanks_for_emailing'		=> 'Your email has been sent successfully',

'mod_button'				=> 'With selected',
'click_for_mod' 			=> 'Click to select all images for bulk moderation',
'modact_unapprove'			=> 'Unapprove',
'modact_move'				=> 'Move',
'cannot_find_comment'		=> 'We could not find the comment to edit',
'ipis_private'	   			=> 'Private',
'idm_lang_prompt'			=> 'Link to Comment: ',
'bulk_mod_title'			=> 'Click to select this comment for bulk moderation',
'opt_dd_delete_comm'		=> 'Delete Comments',
'opt_dd_app_comm'			=> 'Approve Comments',
'opt_dd_unapp_comm'			=> 'Unapprove Comments',
'idm_leave_comment'			=> 'Leave a comment',
'add_comments_pt'			=> "Adding comment for: %s",
'qr_title'					=> 'Add Your Comment',
'report_comment_page'  		=> 'Report this comment to a moderator',
'report_submit'    			=> 'Send your report',
'report_comment_msg'   		=> "<b>Enter your report</b><br /><br />Please note: The moderator will be made aware of the link to the comment.<br /><br />

This form is to be used ONLY for reporting objectional content, etc and is not to be used as a method of communicating with moderators for other reasons.",                    
	 
'report_comment_page'		=> 'Report a Comment',

'report_comment_email' 		=> '<#MODNAME#>,

You have been sent this communication from <#SENDERNAME#> via the "Report this comment to a moderator" link.

------------------------------------------------
<#LINK#>
------------------------------------------------
Report:

<#REPORT#>

------------------------------------------------ ',
'link_to_comment'			=> 'Link to Comment',                         
'comment_reported'			=> 'A comment has been reported on a file at',
'report_redirect_comment'	=> 'The report has been sent successfully',
'comment_mmod_success'		=> 'The multi-moderation has been successfully applied to the comments',
'file_broken_info'			=> "Reported by <a href='%s'>%s</a> on %s",
'imported_desc'				=> 'File imported by an administrator',
'top_downloads'				=> 'Top Downloads',
'file_mmod_success'			=> 'The selected action has been taken on the files',
'multimod_moveto'			=> 'Select category to move the files to',
'multimod_submit'			=> 'Move!',
'multimod_cat'				=> 'Select Category',




// 1.1.2
'import_note'				=> "<b>Note</b>: File must be uploaded to your configured downloads directory",
'ss_import_note'			=> "<b>Note</b>: File must be uploaded to your configured screenshots directory",

// 1.1.1
'sortby_downloads'			=> "downloads",
'sortby_submitted'			=> "submission date",
'sortby_views'				=> "views",
'sortby_rating'				=> "rating",
'sortby_updated'			=> "date updated",
'sortby_title'				=> "title",

// RC 2
'qr_title_edit'				=> "Editing Comment",
'qr_edit_save_button'		=> "Save Changes",

//1.1.0
'latest_downloads'			=> "Latest Files",
'sform_fileurl'				=> "Link to File",
'sform_filessurl'			=> "Link to Screenshot",
'sform_filepath'			=> "Path to File",
'sform_filesspath'			=> "Path to Screenshot",
'restorefile'				=> "Restore Previous Version",
'older_versions'			=> "Saved Versions",
'bversion_name'				=> "Name",
'bversion_filename'			=> "Filename",
'bversion_updated'			=> "Updated",
'bversion_alt_download'		=> "Download",
'bversion_ops'				=> "Operations",
'bversion_alt_restore'		=> "Restore",
'bversion_alt_delete'		=> "Delete",
'bversion_alt_hide'			=> "Hide From Members",
'bversion_alt_unhide'		=> "Show To Members",

'version_restore_succesful'	=> "The version was successfully restored as the most current",
'version_hide_succesful'	=> "The version was successfully hidden",
'version_unhide_succesful'	=> "The version was successfully unhidden",
'version_remove_succesful'	=> "The version was successfully removed",

// Versioning Errors
'FILE_NOT_FOUND'			=> "We could not find the requested file",
'ONLY_LOCAL_BACKUP'			=> "Versioning control is only supported when locally storing the files on the server",
'NO_ID_PASSED'				=> "No ID was passed for versioning control",

'broken_reason'				=> "Please enter the reason for your report",
'broken_confirm'			=> "Confirm Report",
'file_not_approved'			=> "This file has not yet been approved.  Only moderators, and the person who submitted the file can see it.",
'file_report_name'			=> "Reporting file",

'dl_restrict_min_posts'		=> "You do not have enough posts to download files yet.",
'dl_restrict_posts_p_dl'	=> "You have not posted enough times since the last file you downloaded.",
'dl_restrict_daily_bw'		=> "You have exceeded the maximum amount of bandwidth allotted to you by the administrator for the day.",
'dl_restrict_weekly_bw'		=> "You have exceeded the maximum amount of bandwidth allotted to you by the administrator for the week.",
'dl_restrict_monthly_bw'	=> "You have exceeded the maximum amount of bandwidth allotted to you by the administrator for the month.",
'dl_restrict_daily_dl'		=> "You have exceeded the maximum amount of downloads allotted to you by the administrator for the day.",
'dl_restrict_weekly_dl'		=> "You have exceeded the maximum amount of downloads allotted to you by the administrator for the week.",
'dl_restrict_monthly_dl'	=> "You have exceeded the maximum amount of downloads allotted to you by the administrator for the month.",




//1.0.0 RC 2

'idm_cat_selectcat'			=> "Select Category...",

//1.0.0 Beta 2
'idm_guestlang'				=> "Guest",
'idm_suredelete'			=> "Are you sure you wish to delete the item(s)?",
'idm_notdeleted'			=> "The file will not be deleted",

// Global
'idm_navbar'				=> "Downloads",
'idm_pagetitle'				=> "Download Manager",
'stats_main_header'			=> "Download Manager Statistics",
'mini_stats_downloads'		=> "downloads",
'mini_stats_files'			=> "files",
'mini_stats_members'		=> "active users",
'active_users_header'		=> "user(s) active in the past %s minutes",
'active_users_guests'		=> "guests",
'active_users_anon'			=> "anonymous members",
'dactive_users_members'		=> "members, ",
'pagelinks_single'			=> "Single Page",
'stats_totalfiles'			=> "We have a total of <b>%s</b> files in <b>%s</b> categories",
'stats_totalauthors'		=> "A total of <b>%s</b> unique authors have submitted to our site",
'stats_totaldls'			=> "There have been <b>%s</b> logged downloads to date",
'stats_latestfile'			=> "The latest file submitted was <b>%s</b> by <b>%s</b> (submitted <b>%s</b>)",
'stats_noneyet'				=> "<i>N/A</i>",
'randomfile_header'			=> "Random Files",
'idm_offline_msg'			=> "Download Manager Offline",
'idm_rss_title'				=> "Last 10 Submissions RSS Feed",
'idm_rss_desc'				=> "This is the RSS feed of the last ten file submissions accepted into our database.  This RSS feed is always up to date as it is dynamically updated.",


// Category Display
'category_main_header'		=> "Categories",
'subcategory_main_header'	=> "Subcategories",
'cat_no_info'				=> "<i>No Information</i>",
'catfile_date'				=> "Submitted",
'catfile_date1'				=> "Updated",
'catfile_author'			=> "Submitted by",
'catinfo_description'		=> "Description",
'catinfo_totalfiles'		=> "Files",
'catinfo_totalviews'		=> "Views",
'catinfo_totaldownloads'	=> "Downloads",
'catinfo_latestinfo'		=> "Latest Submission",
'catinfo_subcats'			=> "Subcategories: ",
'catdis_updated'			=> "Updated: ",
'catdis_ssnone'				=> "No Screenshot Available",
'catdis_ssalt'				=> "Screenshot",
'catdis_filename'			=> "File Name: ",
'catdis_fileauthor'			=> "Submitter: ",
'catdis_filesubmitted'		=> "Submitted: ",
'catdis_filedls'			=> "Downloads: ",
'catdis_fileviews'			=> "Views: ",
'catdis_filerating'			=> "Rating: ",
'cat_qued'					=> "Queued files:",
'com_qued'					=> "Queued comments:",


// Category File Display
'fileinfo_name'				=> "File Name",
'fileinfo_views'			=> "Views",
'fileinfo_downloads'		=> "Downloads",
'fileinfo_rating'			=> "Rating",
'fileinfo_submitted'		=> "Submission Information",
'no_files_in_category'		=> "There are no files to display",
'fileinfo_sby'				=> "Submitted by",
'fileinfo_son'				=> "on",


// File Display
'file_infoheader'			=> "File Information",
'file_statsheader'			=> "Statistics",
'file_opsheader'			=> "Operations",
'file_custom'				=> "Other Information",

'file_broken_text'			=> "This file has been reported as broken because",

'filescreenshot'			=> "Screenshot",
'ss_clickhere'				=> "Click here for full screenshot",
'ss_notavail'				=> "Not Available",
'ss_notavail2'				=> "Screenshot Not Available",
'filename'					=> "File Name",
'filecategory'				=> "In Category",
'submittedon'				=> "Submitted",
'lastupdated'				=> "Last Updated",
'filedownloads'				=> "Downloads",
'fileviews'					=> "Views",
'filerating'				=> "Rating",
'fileaddrating'				=> "Add Rating",
'filetype'					=> "File Type",
'filesize_mb'				=> "MB",
'filesize_kb'				=> "KB",
'filesize_bytes'			=> "bytes",
'filesize'					=> "File Size",
'ratingoutof'				=> "out of 5",
'file_showtopic'			=> "Click here to visit support topic",
'file_topiclang'			=> "Support Topic",
'file_popuplang'			=> "Comments",
'file_compop'				=> "View Comments (%s)",
'already_voted'				=> "You rated this file %s out of 5",
'rate_this_file1'			=> "Rate this file 1 out of 5",
'rate_this_file2'			=> "Rate this file 2 out of 5",
'rate_this_file3'			=> "Rate this file 3 out of 5",
'rate_this_file4'			=> "Rate this file 4 out of 5",
'rate_this_file5'			=> "Rate this file 5 out of 5",
'click_a_pip'				=> "Click an image to rate this file",
'fileby'					=> "by",
'file_votesnum'				=> "votes",
'file_votesnump'			=> "with a total of",
'file_approvedby'			=> "Approved by",

// Operations
'findallbymem'				=> "Find all files by this member",
'addtofavs'					=> "Add this file to your favorites",
'removefavs'				=> "Remove this file from your favorites",
'subtofile'					=> "Notify me when this file is updated",
'already_subscribed'		=> "Stop notifying me when this file is updated",
'editfile'					=> "Edit this file",
'deletefile'				=> "Delete this file",
'unnapprovefile'			=> "Unapprove this file",
'approvefile'				=> "Approve this file",
'reportbroken'				=> "Report this file as broken",
'unreportbroken'			=> "Remove the broken file flag",


// Comments
'comments_header'			=> "Comments",
'no_comments'				=> "There were no comments found for this file",
'edit_full_link'			=> "Full Edit",
'edit_il_link'				=> "Quick Edit",
'comment_ignore'			=> "You have chosen to ignore posts from ",
'comment_viewignored'		=> "View this comment",
'comment_unignore'			=> "Stop ignoring ",
'comments_titlebar'			=> "File Comments",
'comments_pt'				=> "Edit your Comment",
'qe_complete_edit'			=> "Save Comment",
'qe_cancel_edit'			=> "Cancel",


// File Submission
'file_submit_start_form'	=> "Please select a category",
'file_submit_nav_header'	=> "Select a Category",
'continue_button'			=> "Continue...",
'edit_button'				=> "Update Submission",
'add_button'				=> "Add Submission",
'sform_screenshot'			=> "Screenshot",
'sform_allowed'				=> "Allowed:",
'sform_removess'			=> "Remove existing screenshot?",
'sform_requiress'			=> "Screenshot Required",
'sform_mb'					=> " MB",
'sform_kb'					=> " KB",
'sform_maxsize'				=> "Maximum Size: ",
'cfield_required'			=> "This field is required",
'sform_filelang'			=> " file",
'sform_addfile_header'		=> "Add a",
'sform_editfile_header'		=> "Update",
'sform_filename'			=> "File Name",
'sform_filedesc'			=> "Description",
'sform_filefile'			=> "Upload File",
'sform_smilie_header'		=> "Clickable Smilies",
'all_emoticons'				=> "View All Smilies",
'sform_errors_found'		=> "The following errors were found",
'submit_bbcode_legend'		=> "BBCODE is%s allowed",
'submit_html_legend'		=> "HTML is%s allowed",
'submit_not_legend'			=> " not",
'sform_editcat'				=> "Click here to edit this file's category (Current category: %s)",
'sub_notice_subject'		=> "%s has been updated",
'thanks_for_voting'			=> "Thank you for submitting your rating of this file!",


// Search
'search_nolimit'			=> "No Limit",
'search_results'			=> "Search Results",
'no_search_results'			=> "There were no search results that matched your search criteria.  Please broaden your search criteria and try again",
'search_formheader'			=> "Search for Files",
'search_keywords'			=> "Search for keywords",
'search_date_a'				=> "Files submitted or updated after",
'search_date_b'				=> "and before",
'search_category'			=> "In categories",
'search_author'				=> "Files submitted by",
'search_cat_extra'			=> "You may select more than one category by holding down the CTRL key and clicking on category names",
'search_andor'				=> "Type of matching",
'search_matchall'			=> "Match all criteria",
'search_matchany'			=> "Match any criteria",
'search_sortby'				=> "and sort results by",
'search_sortby2'			=> "Sort files by",
'resort_button'				=> "Sort Files",
'search_sortorder'			=> "in",
'search_sortorder2'			=> "order",
'search_limitresults'		=> "limited to",
'search_limitresults2'		=> "results",
'search_dosearch'			=> "Do Search!",
'search_allcats'			=> "All categories",
'searchpage_nav'			=> "Search",
'search_last_ten'			=> "Last ten submissions accepted",
'search_last_visit'			=> "View all submissions since your last visit",


// Topic stuff
't_filename'				=> "File Name",
't_fileauthor'				=> "File Submitter",
't_submitted'				=> "File Submitted",
't_category'				=> "File Category",
't_clickhere'				=> "Click here to download this file",
't_updated'					=> "File Updated",


// Download page
'dpage_header'				=> "Downloading file",
'dpage_starting'			=> "You are about to download the file '%s'. Please take a moment to review the following disclaimer. Afterwards click the 'Download File' button to download the file.",
'dpage_button'				=> "Download File",


// Moderation
'moderate_nav'				=> "Moderation Panel",
'moderate_filedeleted'		=> "File Successfully Deleted",
'moderate_approve'			=> "The file is now visible to the public",
'moderate_unapprove'		=> "The file is now hidden from public view",
'moderate_broken'			=> "The file has been reported as broken",
'moderate_unbroken'			=> "The broken file flag has been removed",
'moderate_pend_top'			=> "Files Pending Approval",
'moderate_broke_top'		=> "Files Reported Broken",
'modact_select'				=> "With selected...",
'modact_delete'				=> "Delete",
'modact_approve'			=> "Approve",
'modact_submit'				=> "Submit",
'modact_rembroke'			=> "Remove Broken File Flag",
'modact_message_del'		=> "%s files deleted",
'modact_message_app'		=> "%s files approved",
'modact_message_br'			=> "%s files removed from broken file status",
'modact_message_huh'		=> "You must choose an action to perform on the selected files!",
'moderate_appnotify'		=> "Hello %s,\n\nThis message is to notify you that your recently submitted file %s has been approved.\nThis message was automatically generated.  Please contact an admin with any questions.  Thanks!",
'moderate_dennotify'		=> "Hello %s,\n\nThis message is to notify you that your file %s was deleted.\nThis message was automatically generated.  Please contact an admin with any questions.  Thanks!",
'moderate_subject'			=> "Your submitted file %s was %s",
'moderate_app_lang'			=> "approved",
'moderate_del_lang'			=> "deleted",



// UCP
'ucp_nav'					=> "Downloads Control Panel",
'ucp_addedtofavs'			=> "The file has been added to your favorites",
'ucp_removedtofavs'			=> "The file has been removed from your favorites",
'ucp_removedtofavss'		=> "%s file(s) removed from your favorites list",
'ucp_addedtosubs'			=> "You are now subscribed to receive notifications when this file is updated.",
'ucp_rmsubs'				=> "You will not receive any further notifications about updates to this file.",
'ucp_rmsubss'				=> "Subscription notifications stopped for %s file(s)",
'ucp_menu_cat'				=> "Download Manager",
'ucp_manage_files'			=> "Manage Your Files",
'ucp_manage_subs'			=> "Manage Subscribed Files",
'ucp_manage_favs'			=> "Manage Your Favorites",
'ucp_notupdated'			=> "<i>Never</i>",
'ucp_filename'				=> "File Name",
'ucp_submitted'				=> "Submitted",
'ucp_updated'				=> "Last Updated",
'ucp_downloads'				=> "Downloads",
'ucp_views'					=> "Views",
'ucp_rating'				=> "Rating",
'ucp_blank_row'				=> "We're sorry, but we did not find any files that matched your criteria.",
'ucp_rm_sel'				=> "Remove Selected Files",


// Errors
'main_error_header'			=> "ERROR",
'main_error_subtitle'		=> "We're sorry, but your last request produced the following error:",
'main_error_goback'			=> "Back",
'error_generic'				=> "An internal error occurred.  Please contact an administrator",
'no_downloads_permissions'	=> "Sorry, but you do not have access to any of the functions in our Download Manager",
'no_downloads_categories'	=> "There do not appear to be any categories created yet.",
'no_permitted_categories'	=> "You do not have access to this section of the site.",
'no_addfile_permissions'	=> "We could not find any categories that you have permission to submit to",
'no_addthiscat_permissions' => "You do not have permission to add files to this category",
'addfile_error_filename'	=> "A filename is required",
'addfile_error_filedesc'	=> "A file description is required",
'addfile_error_screenshot'	=> "Screenshots are required in this category",
'addfile_error_file'		=> "You did not choose any file to upload",
'addfile_error_cfield'		=> "You left a required field blank",
'addfile_error_cfield1'		=> "One of the fields you completed had more data than is allowed.",
'addfile_upload_error1'		=> "There was an error attempting to upload the file.  Please contact an administrator",
'addfile_upload_error2'		=> "The filetype you tried to upload is not in the list of approved file types",
'addfile_upload_error3'		=> "The file you uploaded was too big.  Please review the size of the file you attempted to upload and the allowed maximum file size",
'addfile_upload_error4'		=> "There was an error attempting to move the file to it's storage directory.  Please contact an administrator",
'addfile_upload_error5'		=> "We could not validate the image you attempted to upload.  Please contact an administrator or try a different image",
'addfile_upload_error6'		=> "Screenshots are required, however you did not submit one",
'addfile_upload_error7'		=> "You submitted an invalid file type for your screenshot",
'addfile_upload_error8'		=> "The screenshot you attempted to upload was too large",
'addfile_ftp_error1'		=> "The uploaded files cannot be stored in the location specified by the administrator",
'addfile_upload_mainerror'	=> "We could not upload the file.  Please contact an administrator",
'file_not_found'			=> "We could not find the file specified",
'search_bad_time'			=> "The date or time you entered was invalid.  Please try again",
'search_no_terms'			=> "You did not supply any criteria to search by or we could not find any results matching the criteria you supplied",
'search_bad_cat'			=> "One of the categories you tried to search in you do not have permission to access",
'cannot_do_download'		=> "You do not have permission to download this file",
'cannot_find_to_del'		=> "We could not find the specified file to delete",
'not_your_file'				=> "This is not your file, thus you cannot edit it",
'cannot_delete'				=> "You do not have permission to delete this file",
'cannot_find_to_addfavs'	=> "We could not find the specified file to add to your favorites list",
'already_addfavs'			=> "This file is already in your favorites",
'cannot_find_to_remove'		=> "Could not find the file to remove from your favorites",
'cannot_find_to_addsub'		=> "We could not find the specified file to subscribe you to it",
'cannot_find_to_rmsub'		=> "Could not find the file to unsubscribe you from",
'cannot_rate_file'			=> "You do not have permission to rate this file.",
'cannot_find_to_toggle'		=> "Could not find the file to approve or unapprove.",
'cannot_find_to_report'		=> "We could not find the specified file to report as broken",
'cannot_find_to_unreport'	=> "We could not find the specified file to remove the broken file flag",
'ftp_couldnot_del'			=> " could not be deleted from the FTP server configured in the ACP.  Please login to your fileserver and manually delete this file.",
'cannot_comment_file'		=> "You do not have permission to comment on this file.",
'no_comments_perms'			=> "You do not have permission to perform the requested action",
'no_comment_foredit'		=> "No comment id was passed for editing",

// Redirect
'submission_approve'		=> "Your submission was successful!  An administrator must review your submission before it will be available publicly.",
'submission_live'			=> "Your submission was successful!  We are taking you there now.",
'comment_approved'			=> "Your comment has been posted successfully",
'comment_pending'			=> "Your comment has been posted.  An administrator will have to approve it before it will be displayed to the public.",
'comment_deleted'			=> "The comment has been deleted.",
'comment_manapproved'		=> "The comment has been approved for public viewing",
'comment_edit_approved'		=> "The changes to your comment have been approved.",
'comment_edit_pending'		=> "Your changes have been saved and will become publicly viewable upon administrator approval",


// Location - online list entry pulls
'idm_loc_idx'				=> 'Viewing Download Manager',
'idm_loc_cat'				=> 'Viewing Download Category:',
'idm_loc_file'				=> 'Viewing Download:',
'idm_loc_ucp'				=> 'Managing Download Settings',
'idm_loc_post'				=> 'Submitting a Download',
'idm_loc_search'			=> 'Searching Download Manager',
'idm_loc_mod'				=> 'Moderating Download Manager',
'idm_loc_comments'			=> 'Viewing Download File Comments',


);

// Email Template(s)

$lang['subsription_notifications'] = <<<EOF
<#NAME#>,

<#AUTHOR#> has just updated the file "<#TITLE#>" to which you are currently subscribed.

You may wish to login to <#BOARD_NAME#> and download the update(s) to this file.

The file download can be found here:
<#BOARD_ADDRESS#>?autocom=downloads&showfile=<#FILE_ID#>

Unsubscribing:
--------------

You can unsubscribe at any time by logging into your control panel and clicking on the "View File Subscriptions" link, or
by clicking the link on the file's download page that states 'Stop notifying me when this file is updated'.

EOF;

$lang['idm_emails_template'] = <<<EOF
A member of our site wished to share a link on our site with you:

<#CONTENT#>

<#FILENAME#> (<#LINK#>)
EOF;
?>