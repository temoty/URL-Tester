<?php
/* This will test a url and return a response in a JSON string */
// if a POST value of 'page' is set, then do a JSON thing
if ( ($thepage = @$_POST['page'] ) ) {
	$result = get_url_status($thepage);
	$result = array('result', $result); 
	echo json_encode($result); 
	die(); 
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Multiple URL Tester</title>
<link href="https://fonts.googleapis.com/css?family=Oswald:300,700" rel="stylesheet">
<style>
*,
html,
body {
	font-family: 'Oswald', sans-serif;
	font-size: 18px;
	line-height: 170%;
}

.btn {
	border-radius: 60px;
	background-color: gold;
	width: 120px;
margin-bottom: 1em;
display: inline-block;}

.btn:hover {opacity: .8;}
#cancel { background-color: red; }

#test {
	background-color: green;
	color: white;
}

#output,
#list {
	margin: 0;
	width: 98%;
	height: 300px;
	font-size: 20px;
	line-height: 120%;
	overflow-x: hidden;
	overflow-y: scrolling;
	border: 1px solid grey;
	padding: .05em;
}
input.url {width: 98%;}
#output li {
	list-style: none;
	width: 100%;
	border-bottom: 1px dotted grey;
	white-space: nowrap;
	overflow: hidden;
}

#output li:nth-child(odd) { background-color: #eee; }
#output li a {text-decoration: none;}
#output li a:hover {text-decoration: underline; opacity: .8;}


.err { color: red; }

.hidden { display: none; }

.active { display: block; }
</style>
</head>

<body>
<h1>Multiple URL Tester</h1>
<p>Paste some URLs into the field...</p>
<form action="#" id="myform">
<p>
	<textarea id="list"></textarea>
	</p>
	<ul id="output" class="hidden">
	</ul>
	<p>
		<input class="btn" type="button" id="test" value="Test URLS" />
		<input class="btn" type="button" id="sort" value="Sort Result" />
		<input class="btn" type="button" id="cancel" value="Reset" />
	</p>
<p><input type="text" class="url" id="search" placeholder="search" /></p>
<p><input type="text" class="url" id="replace" placeholder="replace" /></p>
<p>		<input class="btn" type="button" id="search_replace" value="Search/Replace" /></p>
</form>
<script  type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="   crossorigin="anonymous"></script> 
<script type="text/javascript">

jQuery(document).ready(function($) {
	$('#list').show();
	$('#output').hide();
		
	$('#cancel').on('click',function(){
		$('#list').show();
		$('#output').hide();	
		$('#output').text('');	
	});
	
	$('#test').on('click',function(){
		$('#list').hide();
		$('#output').text('');	
		$('#output').show();	
		
		var thelist = $('#list').val();
		//console.log(thelist);
		thelist = thelist.split("\n");
		$(thelist).each(function (index, myval) { 
			//console.log('index: ' + index + ' myval: ' + myval);
			var testme = 'x' + myval;
			if ( testme.indexOf('http')<1) {
				$('#output').html( $('#output').html() + "<li><span class='err'>Missing http?</span> : <a href='" + myval + "' target='_blank'>" + myval + "</a></li>" );
			}
			else {
				if (myval) { process_url(myval);}
			}
		});
	});
	
	$('#sort').on('click',function(){
		$('#list').hide();
		$('#output').show();	
		var output = $("#output");
		output.children().detach().sort(function(a, b) {
		return $(a).text().localeCompare($(b).text());
		}).appendTo(output);
	});

	$('#search_replace').on('click',function(){
		$('#list').show();
		var mytext = $('#list').val();
		console.log(mytext);
		var mysearch = $('#search').val();
		console.log(mysearch);
		var myreplace = $('#replace').val();
		mytext = mytext.replace(new RegExp(mysearch, "gi"), myreplace);
		console.log(mytext);
		$('#list').val(mytext);
	
	});
	
	function process_url(myval) {
		$.ajax({
			method: "POST",
			url: "<?php echo $_SERVER['REQUEST_URI'];?>",
			data: { page: myval }
		})
		.done(function( data ) {
			data = JSON.parse(data);
			console.log( "data: " + data.result );
			console.log( "data: " + data[0] );
			console.log( "data: " + data[1] );
			var result = data[1];
			if (!result) {$result = 'Fail!';}
			if (result == 404) {result = '<span class="err">404</span>';}
			$('#output').html( $('#output').html() + '<li>' + result + " : <a href='" + myval + "' target='_blank'>" + myval + "</a></li>" );
		});
		
	}
	
});


</script>
</body>
</html>
<?php
// thanks to 
// https://stackoverflow.com/questions/408405/easy-way-to-test-a-url-for-404-in-php
function get_url_status($url, $timeout = 10) {
	$ch = curl_init();
	// set cURL options
	$opts = array(CURLOPT_RETURNTRANSFER => true, // do not output to browser
		CURLOPT_URL => $url,            // set URL
		CURLOPT_NOBODY => true,         // do a HEAD request only
		CURLOPT_TIMEOUT => $timeout);   // set timeout
		curl_setopt_array($ch, $opts);
	curl_exec($ch); // do it!
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE); // find HTTP status
	curl_close($ch); // close handle
	return $status; //or return $status;
	//example checking
	//if ($status == '302') { echo 'HEY, redirection';}

}

