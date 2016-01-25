<?
$db_host =      'samplepractice.c6yw4amhyw8o.us-west-2.rds.amazonaws.com:3306';     //RDS Endpoint...
$db_username =  'capstone2015';
$db_pass =      'capstone2015';
$db_name =      'capstone2015'; 
$con = mysql_connect("$db_host","$db_username","$db_pass", TRUE) or die(mysql_error());
mysql_select_db("$db_name") or die("no database by that name");

if($con)
{
?>
<!doctype html>
<html lang="en">
<head>
</head>
<body>
	<section class="Success">
        <h1>Success!</h1>
    </section>
</body>
</html>
<?
}else
{
?>
<!doctype html>
<html lang="en">
<head>
</head>
<body>
	<section class="Error">
        <h1>Error!</h1>
    </section>
</body>
</html>
<? 
} 
?>