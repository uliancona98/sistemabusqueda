<html>

<head>
	<title>Busqueda</title>
</head>

<body>
	<h1>Busqueda</h1>
	<form action="search.php" method="get">
		<label>Query</label>
		<input type="text" size="30" name="query" value="*:*" /><br><br>
		<input type="submit" />
	</form>
	<p>
	Notes:<br>
	la sintaxis de solr es:<br>
	<b>atributo:valor</b><br>
	Ejemplo 1: if you want to search by "name" for "fast" you type:<br>
	<b>name:fast</b><br>
	Example 2: if you want to display all results you type:<br>
	<b>*:*</b>
	</p>
</body>

</html>