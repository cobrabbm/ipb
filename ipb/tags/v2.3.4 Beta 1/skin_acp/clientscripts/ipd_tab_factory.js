var _tab_factory;function tab_factory(){this.css_tab_off='taboff';this.css_tab_on='tabon';this.string_tab_id='tabtab';this.string_tab_pane='tabpane';this.stored_tabs=new Array();this.stored_panes=new Array();}function tab_factory_init_tabs(){}tab_factory.prototype.init_tabs=function(){var divs=document.getElementsByTagName('DIV');var divcount=0;_tab_factory=this;for(var i=0;i<=divs.length;i++){try{if(!divs[i].id){continue;}}catch(error){continue;}var divid=divs[i].id;var divname=divid.replace( /^(.*)-(\d+)$/,"$1");var divnum=this.get_id_from_text(divs[i].id);if(divname==this.string_tab_id){this.stored_tabs[divnum]=divs[i];divs[i].style.cursor='pointer';divs[i].onclick=this.toggle_tabs;this.stored_panes[divnum]=document.getElementById(this.string_tab_pane+'-'+divnum);if(divcount==0){divs[i].className=this.css_tab_on;divs[i].style.display='block';this.stored_panes[divnum].style.display='block';}else{divs[i].className=this.css_tab_off;divs[i].style.display='block';this.stored_panes[divnum].style.display='none';}divcount++;}}};tab_factory.prototype.toggle_tabs=function(event){var tabid=_tab_factory.get_id_from_text(this.id);for(var i in _tab_factory.stored_tabs){if(i==tabid){_tab_factory.stored_tabs[i].style.display='block';_tab_factory.stored_tabs[i].className=_tab_factory.css_tab_on;_tab_factory.stored_panes[i].style.display='block';}else{_tab_factory.stored_tabs[i].style.display='block';_tab_factory.stored_tabs[i].className=_tab_factory.css_tab_off;_tab_factory.stored_panes[i].style.display='none';}}ipsclass.cancel_bubble(event);return false;};tab_factory.prototype.get_id_from_text=function(id){return id.replace( /.*\-(\d+)/,"$1");};