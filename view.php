<?php
require_once "pdo.php";
require_once "util.php";

session_start();

if (!isset($_SESSION['name']) || strlen($_SESSION['name']) < 1||!isset($_SESSION['user_id']) || strlen($_SESSION['user_id']) < 1)
{
  die('ACCESS DENIED');
  return;
}

$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

$positions = loadPos($pdo, $_REQUEST['profile_id']);
// for Education data read
$school = loadEdu($pdo, $_REQUEST['profile_id']);

?>

<!DOCTYPE html>
<html>
<head><title>Sayed Makhdum Ullah-  autosdb</title>
<?php require_once "bootstrap.php"; ?>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container">
<h1>Profile Information</h1>

<?php
if (isset($_SESSION['name']))
{
    echo "<h3>Greetings: ";
    echo $_SESSION['name'];
    echo "</h3>\n";
}
flashMessages();

    echo "First Name:   ".htmlentities($row['first_name']);
    echo "<br>";
    echo "\n Last Name:".htmlentities($row['last_name']);
        echo "<br>";
    echo "\n Email:".htmlentities($row['email']) ;
        echo "<br>";
    echo "\n Headline:".(htmlentities($row['headline']));
        echo "<br>";
    echo "\n Summary:".htmlentities($row['summary']) ;
    echo "<br>";
    echo "<h4>Education</h4>";
        if ( $school == false ) {
          echo '<p style="color:red">No position defined</p>';
          }else
            {
         foreach($school as $row) {
         echo ("  <ul><li>"."Year: ".htmlentities($row['year'])."   Name:".htmlentities($row['name'])." </li></ul>");
         }}
    echo "<br>";
    echo "<h4>Position</h4>";
    if ( $positions == false ) {
      echo '<p style="color:red">No position defined</p>';
      }else
        {
     foreach($positions as $row) {
     echo ("  <ul><li>"."Year: ".htmlentities($row['year'])."   Rank:".htmlentities($row['rank'])."   Description: ".htmlentities($row['description'])."</li></ul>");
     }}
?>
<p><a href="index.php">Done</a></p>

  </div>
</body>
</html>
