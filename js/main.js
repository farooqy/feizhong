/* $, jshint bitwise: false*/
var global_ajax_status = false;
var manuscript_set = false;
var cover_set = false;
var site_url = location.protocol+"//"+location.hostname+"/";
var main_dir = "/home/ganacsig/jtoxmolbio.com/";
var message_sent = false;
$(document).ready(function(){

	$('.gen-api').click(function()
	{
	    var url = site_url+'api/';
	    form_data = new FormData();
	    form_data.append('genapi','true');
	    form_data.append('type', 'api');
	    
	    var done = make_ajax(form_data, url);
	});
	
	$('.save-api').click(function(){
	    var url = site_url+'api/';
	    form_data = new FormData();
	    form_data.append('saveapi','true');
	    form_data.append('type', 'api');
	    form_data.append('api', $('.api-text').text());
	    
	    var done = make_ajax(form_data, url);
	});
});
  
//this function escapes the singlue quote problem
//for the database
function singleQuote(text)
{

	var len = text.length;
	var new_text = "";
	for(var i=0; i<len; i++)
		{
			if(text[i] === "'")
				new_text = new_text+"'"+text[i];
			else
				new_text = new_text+text[i];
		}
	//alert(new_text);
	return new_text;

}



function make_ajax(form_data, url, indicate='genapi')
{
	$('.ErrorHolder').css('display','none');
	$.ajax({
		type:"POST",
		async:false,
		url:url,
		data: form_data,
    contentType: false,
    processData: false,
    cache:false,
		success:function(data){
			data = JSON.parse(data);
			if(data[0] === false)
				{
					$('.text-load').text("FAILED: "+data[1]);
					setTimeout(function(){
						$('.loader').css('display','none');
					}, 5000);
					return false;
				}
			else if(data[0] === true)
				{
				    if(indicate === 'genapi')
				        DoGenApi(data[1]);
				    else if (indicate === 'saveapi')
				        DoSaveApi();
					$('.ErrorHolder').text(data[1]);
					$('.ErrorHolder').css('background-color','green');
					$('.ErrorHolder').css('display','block');
					$('.text-load').text('SUCCESSFULL: '+data[1]);
					setTimeout(function(){
						$('.loader').css('display','none');
					}, 5000);
					global_ajax_status = true;
					return true;
				}
			else
				{
					$('.ErrorHolder').text('ERROR: '+data[1]);
					$('.ErrorHolder').css('background-color','red');
					$('.ErrorHolder').css('display','block');
					$('.text-load').text('FAILED:: UNKNOWN ERROR');
					setTimeout(function(){
						$('.loader').css('display','none');
					}, 5000);
					return false;
				}

		},
		error:function(error){
			$('.ErrorHolder').text('Failed to make query, check your internet'+
														' or contact admin');
			$('.ErrorHolder').css('background-color','red');
			$('.ErrorHolder').css('display','block');
			console.log("error: "+JSON.stringify(error));
			$('.text-load').text('Failed,check your interntet'+
													 ', make sure the address'+
													 'is https and not http only '+
													 'then try again.');
			//$('.ErrorHolder').css('display','block');
			setTimeout(function(){
						$('.loader').css('display','none');
					}, 5000);
			return false;
		},
	});
}


function ajax_query(data_to_send, target_url, form_name,file_name,
                     success="success")
{
	$('.loading-page').removeClass('hide');
  $.ajax({
    xhr: function() {
        var xhr = new window.XMLHttpRequest();

        // Upload progress
        xhr.upload.addEventListener("progress",
                                    function(evt){
            if (evt.lengthComputable) {
                var percentComplete = Math.round((evt.
                                                  loaded / evt.total)*100);
                //Do something with upload progress
								$(".loading-page .counter .loader-text").html("LOADING");
								$(".loading-page .counter h1").html(percentComplete+"%");
            }
       }, false);

       // Download progress
       xhr.addEventListener("progress", function(evt){
           if (evt.lengthComputable) {
               var percentComplete = Math.round((
                 evt.loaded / evt.total)*100);
               // Do something with download progress
               
								$(".loading-page .counter .loader-text").html("PROCESSING");
								$(".loading-page .counter h1").html(percentComplete+"%");
             if(percentComplete == 100)
               {
									$(".loading-page .counter .loader-text").html("COMPLETED");
									$(".loading-page .counter h1").html(percentComplete+"%");
               }
           }
       }, false);

       return xhr;
    },
    type:"POST",
    async:false,
    url:target_url,
    data: data_to_send,
    contentType: false,
    processData: false,
    cache:false,
    success:function(data){
      data = JSON.parse(data);
      if(data[0] === true && data[1] === success)
      {
        $('.errorHolder').css('background-color','green');
        $('.errorHolder').text('successfull');
        $('.sh-name').val('');
        $('.sh-sawir').val('');
        $('.sh-preview').html('');
        //alert("true success");
        message_sent = true;
        $(form_name)[0].reset();
				setTimeout(function(){
					$('.errorHolder').css('display','none');
					$('.errorHolder').css('background-color','red');
					$('.errorHolder').text('');
				}, 5000)
				var token = "";
				var type_name ="NONE";
				var random = Math.floor(Math.random() *100);
				var attr_name = "";
				if(form_name === "#man-form")
				{
					type_name = "Manuscript";
					token = "file"+Math.floor(new Date() /1000)+random;
					attr_name = "manuscript";
					update_table(file_name, type_name,attr_name, token);
				}
				else if(form_name === "#cover-form")
				{
					type_name = "Cover";
					token = "cover"+Math.floor(new Date() /1000)+random;
					attr_name = "cover";
					update_table(file_name, type_name,attr_name, token);
				}
				else if(form_name === "#fig-form")
				{
					
					
					type_name = "Figure";
					attr_name = "figure";
					for(var i =0; i<file_name.length; i++)
						{
							token = "figure"+(Math.floor(new Date() /1000)+i);
							update_table(file_name[i], type_name,attr_name, token+1);
						}
				}
				else
				{
					
					type_name = "Other";
					attr_name = "others";
					for(var j =0; j<file_name.length; j++)
						{
							token = "figure"+(Math.floor(new Date() /1000)+j);
							update_table(file_name[j], type_name,attr_name, token);
						}
				}
				
        return true;
      }
      else if(data[0] === false)
        {
          $('.errorHolder').text('Error: '+data[1]);
          $('.errorHolder').css('background-color', 'red');
          $('.errorHolder').css('display','block');
          
          return "error";
        }
    },
    error:function(error){
      error = JSON.stringify(error);
      $('.errorHolder').text('Connection error: '+error);
      return false;
    }
    
    
  });
	
	setTimeout(function(){
		$('.loading-page').addClass('hide');
	}, 5000);
}

function DoGenApi(apikey)
{
    $('.api-text').text(apikey);
}
function DosaveApi()
{
    $('.api-text').text('');
}
function update_table(name, type_name, attr_name, token)
{
	var data ="";
	if(attr_name === "manuscript")
			data = main_dir+"docs/"+name;
	else if(attr_name === "cover")
		data = main_dir+"docs/covers/"+name;
	else if(attr_name === "figure" || attr_name === "others") 
		data = main_dir+"photos/journalfigs/"+name;
	var nrows = parseInt($('.table-uploads').attr('target'));
	nrows +=1;
	var srcRemove = site_url+"photos/icons/close_red.png";
	var tr = "<tr class=\" trow "+token+"\" name=\""+attr_name+"\" id=\""+attr_name+"\"> <td>"+nrows+"</td> ";
	tr = tr+"<td>"+name+"</td>";
	tr = tr+"<td> "+type_name+" </td>"
	tr = tr+"<td> Uploaded </td>";
	tr = tr+"<td class=\"text-center\"> <img class=\"remove-icon\" src=\""+srcRemove+"\" target=\""+token+"\"";
	tr = tr+" onclick=\"removeFile(this)\" data=\""+data+"\"> </td> </tr>";
	var current_body = $('.table-uploads').html()+tr;
	$('.table-uploads').html(current_body);
	$('.table-uploads').attr('target',nrows);
}

function checkFields(fields)
{
  console.log("leng: "+fields.length)
  for(var i=0; i<fields.length; i++)
  {
    if(fields[i][0].type == "string")
    {
      console.log("is "+fields[i][0].name+" empty? "+fields[i][0].val.length);
      if(fields[i][0].val.length === 0 || fields[i][0].val === undefined || fields[i][0].val === "dft")
        {
          return fields[i][0].name;
        }
    }
    else
    {
      if(fields[i][0].val === 0 || fields[i][0].val === undefined )
        return fields[i][0].name;
    }
  }
  return false;
}
  
function correspond(obj)
{
	var check_status = $(obj).attr('checked');
	if(check_status == "checked")
		{
			$(obj).attr("checked",false);
			$(obj).val('');
		}
	else
		{
			var email = $(obj).attr('target');
			$(obj).val(email);
			$(obj).attr("checked", true);
		}
}
function deleteAuthor(obj)
{
	var target = $(obj).attr('target');
	alert('to delete: '+target);
			$('.'+target).remove();
			$('#email'+target).remove();
			$('#sal'+target).remove();
			$('#fname'+target).remove();
			$('#sname'+target).remove();
			$('#country'+target).remove();
			$('#insti'+target).remove();
			var divsT = document.getElementById(target);
	

	$('.authorstable').attr('target',parseInt($('.authorstable').attr('target'))-1);
}

function removeFile(obj)
{
	//alert("called again");
	var dir = $(obj).attr('data');
	var ajax_url = site_url+"submit/remove.php";
	var form_data = new FormData();
	form_data.append('remove',dir);
	var status_ajax = false;
	var ajax_status = make_ajax(form_data, ajax_url);
	var target = $(obj).attr('target');
	var type =$('.'+target).attr('name');
	$('.'+type).val('');
	$('.'+target).remove();
	var no = parseInt($('.table-uploads').attr('target'));
	if(no > 0)
	$('.table-uploads').attr('target',no-1);
	if(type === "manuscript")
	 manuscript_set = false;
	else if(type ==="cover")
	{
		cover_set = false;
	}
}

function ExisitingRandom(previousrandom, random)
{	
	$.each(previousrandom, function(i, val){
		if(val === random)
						return true;
				});
	return false;
}

String.prototype.hashCode = function() {
  var hash = 0, i, chr;
  if (this.length === 0) return hash;
  for (i = 0; i < this.length; i++) {
    chr   = this.charCodeAt(i);
    hash  = ((hash * 31) + chr);
    hash = hash || 0; // Convert to 32bit integer
  }
  return hash;
};

