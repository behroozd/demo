<html>

<head>
	<title>Test Json</title>
	<script src="http://code.jquery.com/jquery-1.9.0.js"></script>
	<style>
		button{ width:100px;height:26px;cursor:pointer; }
	</style>
</head>

<body>
	<div>
		<label style="display:table-cell">Macaddress</label>
		<input value="f8:a9:d0:8c:08:a8" id="macAdd" />
		<br>
		<label style="display:table-cell">UUID</label>
		<input value="" id="uuid" />
		<br>
		<label style="display:table-cell">Major</label>
		<input value="" id="major" />
		<br>
		<label style="display:table-cell">Minor</label>
		<input value="" id="minor" />
		<br>
		<label style="display:table-cell">Presence</label>
		<input value="" id="presence" />
		<br>
		<label style="display:table-cell">location ID</label>
		<input value="" id="locationid" />
		<br>
		<label style="display:table-cell">Type</label>
		<input value="list" id="type" />
	</div>
	<br />
	<div>
		<button id="auth">Call auth</button>
		&nbsp;
		<button id="inout">Call inout</button>
		&nbsp;
		<button id="list">Call list</button>
		&nbsp;
		<button id="listall">Call list all</button>
		&nbsp;
		<button id="referral">Referral</button>
		
	</div>
	<!--
	<div>
		<button id="inout1">Call inout 1</button>
	</div>
	-->
	<br />
	<div id="response">
	</div>
</body>

<script>
//----------------------------------------------------------------
$("#auth").click(function(){
	$("#response").html('wait . . .');
    $.post(
		"../auth.php", 
		{ 
			macaddress  : $("#macAdd").val()
		},
		function(data, status){
			if(status=='success'){
				var tmp="";
				for( var obj in data){
					tmp+=obj+":"+data[obj]+"<br/>";
				}
				$("#response").html(tmp);
			}else{
				$("#response").html('');
		        alert("\nStatus: " + status);
			}
    	}
	);
});
//----------------------------------------------------------------
$("#inout").click(function(){
	$("#response").html('wait . . .');
    $.post(
		"../index.php", 
		{ 
			activity   : 'inout',
			macaddress : $("#macAdd").val(),
			presence   : $("#presence").val(),
			major      : $("#major").val(),
			minor      : $("#minor").val(),
			uuid       : $("#uuid").val()
		},
		function(data, status){
			if(status=='success'){
				var tmp="";
				for( var obj in data){
					if(obj=='users'){
						tmp+=obj+":<br/>";
						for( var i=0; i<data.users.length ; i++){
							tmp+=" - "+i+"<br>";
							var tmpU=data.users[i];
							for( var objU in tmpU){
								tmp+="&nbsp;&nbsp;&nbsp;"+objU+":"+tmpU[objU]+"<br/>";
							}
						}
					}else{
						tmp+=obj+":"+data[obj]+"<br/>";
					}
				}
				$("#response").html(tmp);
			}else{
				$("#response").html('');
		        alert("\nStatus: " + status);
			}
    	}
	);
});
$("#inout1").click(function(){
	$("#response").html('wait . . .');
    $.post(
		"../test.php", 
		{ 
			activity   : 'inout',
			macaddress : $("#macAdd").val(),
			presence   : $("#presence").val(),
			uuid       : $("#uuid"  ).val()
		},
		function(data, status){
			if(status=='success'){
				var tmp="";
				for( var obj in data){
					if(obj=='users'){
						tmp+=obj+":<br/>";
						for( var i=0; i<data.users.length ; i++){
							tmp+=" - "+i+"<br>";
							var tmpU=data.users[i];
							for( var objU in tmpU){
								tmp+="&nbsp;&nbsp;&nbsp;"+objU+":"+tmpU[objU]+"<br/>";
							}
						}
					}else{
						tmp+=obj+":"+data[obj]+"<br/>";
					}
				}
				$("#response").html(tmp);
			}else{
				$("#response").html('');
		        alert("\nStatus: " + status);
			}
    	}
	);
});
//----------------------------------------------------------------
$("#list").click(function(){
	$("#response").html('wait . . .');
    $.post(
		"../index.php", 
		{ 
			activity   : 'list',
			macaddress : $("#macAdd").val(),
			locationid : $("#locationid").val(),
			presence   : $("#presence").val(),
			uuid       : $("#uuid"  ).val(),
			major      : $("#major").val(),
			minor      : $("#minor").val(),
			type       : $("#type").val()
		},
		function(data, status){
			if(status=='success'){
				var tmp="";
				for( var obj in data){
					if(obj=='items'){
						tmp+=obj+":<br/>";
						for( var i=0; i<data.items.length ; i++){
							tmp+=" - "+i+"<br>";
							var tmpU=data.items[i];
							for( var objU in tmpU){
								tmp+="&nbsp;&nbsp;&nbsp;"+objU+":"+tmpU[objU]+"<br/>";
							}
						}
					}else{
						tmp+=obj+":"+data[obj]+"<br/>";
					}
				}
				$("#response").html(tmp);
			}else{
				$("#response").html('');
		        alert("\nStatus: " + status);
			}
    	}
	);
});
//----------------------------------------------------------------
$("#listall").click(function(){
	$("#response").html('wait . . .');
    $.post(
		"../index.php", 
		{ 
			activity   : 'listall',
			macaddress : $("#macAdd").val(),
			major      : $("#major").val(),
			minor      : $("#minor").val(),
			uuid       : $("#uuid"  ).val()
		},
		function(data, status){
			if(status=='success'){
				var tmp="";
				for( var obj in data){
					if(obj=='items'){
						tmp+=obj+":<br/>";
						for( var i=0; i<data.items.length ; i++){
							tmp+=" - "+i+"<br>";
							var tmpU=data.items[i];
							for( var objU in tmpU){
								tmp+="&nbsp;&nbsp;&nbsp;"+objU+":"+tmpU[objU]+"<br/>";
							}
						}
					}else{
						tmp+=obj+":"+data[obj]+"<br/>";
					}
				}
				$("#response").html(tmp);
			}else{
				$("#response").html('');
		        alert("\nStatus: " + status);
			}
    	}
	);
});
//----------------------------------------------------------------
$("#referral").click(function(){
	$("#response").html('wait . . .');
    $.post(
		"../index.php", 
		{ 
			activity   : 'referral',
			macaddress : $("#macAdd").val()
		},
		function(data, status){
			if(status=='success'){
				var tmp="";
				for( var obj in data){
					if(obj=='users'){
						tmp+=obj+":<br/>";
						for( var i=0; i<data.users.length ; i++){
							tmp+=" - "+i+"<br>";
							var tmpU=data.users[i];
							for( var objU in tmpU){
								tmp+="&nbsp;&nbsp;&nbsp;"+objU+":"+tmpU[objU]+"<br/>";
							}
						}
					}else{
						tmp+=obj+":"+data[obj]+"<br/>";
					}
				}
				$("#response").html(tmp);
			}else{
				$("#response").html('');
		        alert("\nStatus: " + status);
			}
    	}
	);
});
//----------------------------------------------------------------
</script>

</html>