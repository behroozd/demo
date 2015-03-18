<html>
	<head>
		<script src='https://cdn.firebase.com/js/client/2.2.1/firebase.js'></script>
		<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'></script>
	</head>
	<body>
		<div id='messagesDiv'></div>
		<input type='text' id='nameInput' placeholder='Name'>
		<input type='text' id='messageInput' placeholder='Message'>	
		<script>
			var myDataRef = new Firebase('https://qccht4wammb.firebaseio-demo.com/');
		//	var myDataRef = new Firebase('https://resplendent-fire-9392.firebaseio.com/');
		
			$('#messageInput').keypress( function( e ){
				if( e.keyCode == 13 ){
					var name = $('#nameInput').val();
					var text = $('#messageInput').val();
			
					myDataRef.set({name: name, text: text});
					myDataRef.set('User ' + name + ' says ' + text);
			
					myDataRef.push({name: name, text: text});
					$('#messageInput').val('');
				}
			});
		
			myDataRef.on('child_added', function( snapshot ){
				var message = snapshot.val();
				displayChatMessage( message.name, message.text );
			});
			
			function displayChatMessage( name, text ){
				$('<div/>').text(text).prepend($('<em/>').text(name+': ')).appendTo($('#messagesDiv'));
				$('#messagesDiv')[0].scrollTop = $('#messagesDiv')[0].scrollHeight;
			};
		</script>
	</body>
</html>