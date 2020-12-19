<?php
session_start();
require_once "pdo.php";
require_once "util.php";
$stmt = $pdo->query("SELECT * FROM profile ORDER BY profile_id");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head><title> Sayed Makhdum Ullah-  autosdb</title>
<?php require_once "head.php"; ?>
<?php require_once "bootstrap.php"; ?>
</head>
<body>

<div class="container">
<h1>Js01 Database</h1>
<?php
if (isset($_SESSION['name']))
{
    echo "<h3>Greetings: ";
    echo $_SESSION['name'];
    echo "</h3>\n \n";
}

flashMessages();

if (!isset($_SESSION['name']) || strlen($_SESSION['name']) < 1||!isset($_SESSION['user_id']) || strlen($_SESSION['user_id']) < 1)
{ echo ("<h1>Welcome to the makhdum's profile management</h1>");
  echo ('<p><a href="login.php">Please log in</a></p>');
  echo ('<p>Attempt to go to <a href="add.php">add.php</a> and <a href="view.php">view.php</a> without logging in - it should fail with an error message.
  </p>');
  return;
}
if ($rows==false){
  echo "No rows found";
} else
{
echo('<table border="1">'."\n");
echo "<tr>";
  echo "<th>Name</th>";
  echo "<th>Headline</th>";
  echo "<th>Action</th>";
echo "</tr>";
foreach($rows as $row) {
    echo "<tr><td>";
    echo('<a href="view.php?profile_id=  '.$row['profile_id'].' "> ');
    echo htmlentities($row['first_name'])." ".htmlentities($row['last_name'])."</a>" ;
    echo("</td><td>");
    echo(htmlentities($row['headline']));
    echo("</td><td>");
    echo('<a href="edit.php?profile_id=  '.$row['profile_id'].'  ">Edit</a> ');
    echo('<a href="delete.php?profile_id=  '.$row['profile_id'].'  ">Delete</a>');
    echo("</td></tr>\n");
}
}
echo('</table>');
?>
<p>
<p><a href="add.php">Add New Entry</a></p>
<p><a href="Logout.php">Logout</a></p>
</p>
</div>
</body>
</html>
