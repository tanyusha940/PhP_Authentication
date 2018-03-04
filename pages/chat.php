<?php

include PATH.'/inc/head.php';

?>
<div align="right">
	<a href="?page=outlogin" >
		<button type="submit"  class="btn btn-primary"><span class="glyphicon glyphicon-log-out" ></span> Выход</button>
	</a>
</div>
<div class="lead" align="center" ><h2>Вы вошли как <?= $_SESSION['auth']['first_name'] ?> <?= $_SESSION['auth']['last_name'] ?>, через <?= $_SESSION['auth']['network'] ?></h2></div><br>


<textarea id="message" rows = "5" class="form-control" placeholder="Type a message"></textarea>
<button type="submit" id="send" class="btn btn-primary"><span class ="glyphicon glyphicon-send"></span>Отправить</button>
<br>

<div id="list"></div>
<br>
<div class="col-sm-3 col-sm-offset-4 frame">
            <ul></ul>
            <div>
                <div class="msj-rta macro">                        
                    <div class="text text-r" style="background:whitesmoke !important">
                        <input class="mytext" placeholder="Type a message"/>
                    </div> 

                </div>
                <div style="padding:10px;">
                    <span class="glyphicon glyphicon-share-alt"></span>
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
						list.append("<div>" + res[key][0] + " " + res[key][1] + " " + res[key][2] + "</div>");
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
