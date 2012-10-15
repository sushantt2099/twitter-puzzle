 function genrateResult(frm)
			    {
				     if (frm.handler.value == "")
				     	alert("Hey! Please Enter Twitter Handler!")
				    else{
					    window.location="createcloud.php?handler="+frm.handler.value+"&sample_size=200&retweet=false&exclude_replies=false";

				    }
				    }
				    
function customizeResult(frm)
				{
					var loc;
					if (frm.oldhandler.value == "")
				     	alert("Hey! Please Enter Twitter Handler!")
				    else{

					if(frm.retweet.checked){
						 loc = "createcloud.php?handler="+frm.oldhandler.value+"&retweet=true";
					}else{
						 loc = "createcloud.php?handler="+frm.oldhandler.value+"&retweet=false";
					}
					
					if(frm.exclude_replies.checked){
						loc = loc+"&exclude_replies=true";
					}else{
						loc = loc+"&exclude_replies=false";

					}
					loc = loc+"&sample_size="+frm.sample_size.value;
					
					window.location=loc;
					}
			}