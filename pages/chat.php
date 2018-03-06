<?php

include PATH.'/inc/head.php';

?>

<div align="right">
	<a href="?page=outlogin" >
		<button id="output_button" type="submit"  class="btn btn-primary"><span class="glyphicon glyphicon-log-out" ></span> Выход</button>
	</a>
</div>
<div class="lead" align="center" ><h2 style ="font-family:Trattatello, cursive ">Вы вошли как <?= $_SESSION['auth']['first_name'] ?> <?= $_SESSION['auth']['last_name'] ?>, через <?= $_SESSION['auth']['network'] ?></h2></div><br>

<div class="col-sm-3 col-sm-offset-4 frame">		<!-- messages  -->
	<ul></ul>
	<div id="chatick" style="overflow-y:  scroll;top: 0px">
		<div class="msj-rta macro" style="margin:auto; background:linen !important">                        
			<div id="list" class="text text-r" style="background:wheat !important" ></div> 
		</div>
	</div>
</div>
<br>

<div class="col-sm-3 col-sm-offset-4 frame second_frame">
	<ul></ul>
	<div>
		<div class="msj-rta macro" style="background:antuqueWhite !important; margin-left: 15px;">                        
			<div class="text text-r" >
				<input id="message" class="mytext" placeholder="Введите текст"/>
			</div> 

		</div>
		<div style="padding:10px;">
			<button type="submit" id="send" class="btn btn-primary"><span class ="glyphicon glyphicon-send"></span></button>
		</div>                
	</div>
</div>       

<script>
	(function(){
		var list = $("#list");
		
		var getListMessages= function(){
			$.ajax({
				url: "http://task3.ru/?page=api-history",
				success: function(res) {
					list.html("");
					for (key in res) {
						//list.append("<div class=\"\">" + res[key][0] + " " + res[key][1] + " " + res[key][2] + "</div>");
						list.append('<li style="width:100%;list-style: none;" class="msg_block">' +
										'<div class="msj macro">' +
											'<div class="text text-l">' +
												'<p><small>'+ res[key][0] +'</small></p>' +
												'<p>'+ res[key][1] +'</p>' +
												'<p><small>'+ res[key][2] +'</small></p>' +
											'</div>' +
										'</div>' +
									'</li>');
					}
				}
			});
		}
		
		var sendMessage = function(text){
			$.ajax({
				method: "POST",
				url: "http://task3.ru/?page=api-send",
				data: {
					text: text
				},
				success: function() {
					getListMessages();
				}
			});
		}
		
		$("#send").click(function(){
			var field = $("#message");
			sendMessage(field.val());
			field.val("");
		});
		
		
		getListMessages();
		
		setInterval(function(){
			getListMessages();
		}, 1000);
	})();
</script>
<?

include PATH.'/inc/foot.php';
