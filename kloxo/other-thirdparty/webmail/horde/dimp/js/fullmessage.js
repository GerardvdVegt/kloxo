var DimpFullmessage={quickreply:function(a){var b;$("msgData").hide();$("qreply").show();switch(a){case"reply":case"reply_all":case"reply_list":b="GetReplyData";break;case"forward_all":case"forward_body":case"forward_attachments":b="GetForwardData";break}DimpCore.doAction(b,{imp_compose:$F("messageCache"),type:a},[DimpCore.toUIDString($F("index"),$F("folder"))],this.msgTextCallback.bind(this))},msgTextCallback:function(b){if(!b.response){return}var d=b.response,a=((d.format=="html")&&!DimpCompose.editor_on),e=(d.identity===null)?$F("identity"):d.identity,c=DimpCompose.get_identity(e,a);$("identity","last_identity").invoke("setValue",e);DimpCompose.fillForm((c.id[2])?("\n"+c.sig+d.body):(d.body+"\n"+c.sig),d.header);if(d.fwd_list&&d.fwd_list.length){d.fwd_list.each(function(f){DimpCompose.addAttach(f.number,f.name,f.type,f.size)})}if(a){DimpCompose.toggleHtmlEditor(true)}if(d.imp_compose){$("messageCache").setValue(d.imp_compose)}}};document.observe("dom:loaded",function(){window.focus();DimpCore.buildAddressLinks("msgHeaders");DimpCore.messageOnLoad();DimpCore.addPopdown("reply_link","replypopdown");DimpCore.addPopdown("forward_link","fwdpopdown");var a=DimpCore.clickObserveHandler;a({d:$("windowclose"),f:function(){window.close()}});a({d:$("reply_link"),f:DimpFullmessage.quickreply.bind(DimpFullmessage,"reply")});a({d:$("forward_link"),f:DimpFullmessage.quickreply.bind(DimpFullmessage,DIMP.conf.forward_default)});["spam","ham","deleted"].each(function(b){var c=$("button_"+b);if(c){a({d:c,f:function(d){DIMP.baseWindow.DimpBase.flag(d,DIMP.conf.msg_index,DIMP.conf.msg_folder);window.close()}.curry(b)})}});a({d:$("qreply").select("div.headercloseimg img").first(),f:DimpCompose.confirmCancel.bind(DimpCompose)});["reply","reply_all","reply_list"].each(function(b){var c=$("ctx_replypopdown_"+b);if(c){a({d:c,f:DimpFullmessage.quickreply.bind(DimpFullmessage,b),ns:true})}});["forward_all","forward_body","forward_attachments"].each(function(b){a({d:$("ctx_fwdpopdown_"+b),f:DimpFullmessage.quickreply.bind(DimpFullmessage,b),ns:true})})});