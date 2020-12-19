<?php
// no util xcode is used .
require_once "pdo.php";
session_start();
// Demand a GET parameter
if (!isset($_SESSION['name']) || strlen($_SESSION['name']) < 1 || !isset($_SESSION['user_id']) || strlen($_SESSION['user_id']) < 1)
{
    die('ACCESS DENIED');
    return;
}
if (isset($_POST['cancel']))
{
    // Redirect the browser to view.php
    header("Location: index.php");
    return;
}


// begain to dta _processes
if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']))
{
    if (strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1)
    {
        $_SESSION["error"] = "All fields are required";
        header("Location: add.php");
        return;
    }

    $email = $_POST["email"];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        $_SESSION["error"] = "Email must have an at-sign (@)";
        header("Location: add.php");
        return;
    }
    // valodate position
    for ($i = 1;$i <= 9;$i++)
    {
        if (!isset($_POST['year' . $i])) continue;
        if (!isset($_POST['desc' . $i])) continue;
        $year = $_POST['year' . $i];
        $desc = $_POST['desc' . $i];
        if (strlen($year) == 0 || strlen($desc) == 0)
        {
            $_SESSION["error"] = "All fields required!";
            header("Location: add.php");
            return;
        }
        if (!is_numeric($year))
        {
            $_SESSION["error"] = "Position year must be numeric!";
            header("Location: add.php");
            return;
        }
    }

    for ($i = 1;$i <= 9;$i++)
    {
        if (!isset($_POST['edu_year' . $i])) continue;
        if (!isset($_POST['edu_school' . $i])) continue;
        $year = $_POST['edu_year' . $i];
        $school = $_POST['edu_school' . $i];
        if (strlen($year) == 0 || strlen($school) == 0)
        {
            $_SESSION["error"] = "All fields required!";
            header("Location: add.php");
            return;
        }
        if (!is_numeric($year))
        {
            $_SESSION["error"] = "edu_year year must be numeric!";
            header("Location: add.php");
        }
    }

    $stmt = $pdo->prepare('INSERT INTO profile
          (user_id, first_name, last_name, email, headline, summary)
          VALUES ( :uid, :fn, :ln, :em, :he, :su)');
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary']
    ));
    $profile_id = $pdo->LastInsertId();

    // insert position entity
    $rank = 1;
    for ($i = 1;$i <= 9;$i++)
    {
        if (!isset($_POST['year' . $i])) continue;
        if (!isset($_POST['desc' . $i])) continue;
        $year = $_POST['year' . $i];
        $desc = $_POST['desc' . $i];
        $stmt = $pdo->prepare('INSERT INTO position
           (profile_id, rank, year, description)
           VALUES ( :pid, :rank, :year, :desc)');
        $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc,
        ));
        $rank++;
    }
    ///insert to education
    $rank = 1;
    for ($i = 1;$i <= 9;$i++)
    {
        if (!isset($_POST['edu_year' . $i])) continue;
        if (!isset($_POST['edu_school' . $i])) continue;
        $year = $_POST['edu_year' . $i];
        $school = $_POST['edu_school' . $i];
        // look up institution is here:
        $institution_id = false;
        $stmt = $pdo->prepare('SELECT institution_id from institution
            WHERE name=:name');
        $stmt->execute(array(
            ':name' => $school
        ));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) $institution_id = $row[institution_id];

        // if there is no instituation name foud so now insert it
        if ($row === false)
        {
            $stmt = $pdo->prepare('INSERT INTO institution (name) VALUES (:name) ');
            $stmt->execute(array(
                ':name' => $school
            ));
            $institution_id = $pdo->lastInsertId();
        }
        // now finally insert in the EDUCATION
        $stmt = $pdo->prepare('INSERT INTO Education
              (profile_id, rank, year, institution_id)
              VALUES ( :pid, :rank, :year, :iid)');
        $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':iid' => $institution_id
        ));
        $rank++;
    }


    $_SESSION["success"] = "Profile added.";
    header("Location: index.php");
    return;
}
?>


<!DOCTYPE html>
<html>
<head>
<title>Sayed Makhdum Ullah- autosdb</title>
<?php require_once "head.php"; ?>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<?php
if (isset($_SESSION['name']))
{
    echo "<h3>Welcome: ";
    echo $_SESSION['name'];
    echo "</h3>\n";
}
if (isset($_SESSION["error"]))
{
    echo ('<p style="color:red">' . $_SESSION["error"] . "</p>\n");
    unset($_SESSION["error"]);
}
?>


<h1>Adding Profile for <?=htmlentities($_SESSION['name']); ?></h1>
<form method="post">
<p>First Name:
<input type="text" name="first_name" size="60"/></p>
<p>Last Name:
<input type="text" name="last_name" size="60"/></p>
<p>Email:
<input type="text" name="email" size="30"/></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80"/></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80"></textarea>
<p>
Education: <input type="submit" id="addEdu" value="+"/>
<div id="edu_fields">
</div>
</p>
<p>
Position: <input type="submit" id="addPos" value="+"/>
<div id="position_fields">
</div>
</p>
<p>
<input type="submit" value="Add"/>
<input type="submit" name="cancel" value="Cancel"/>
</p>
</form>


<script>
countPos = 0;
countEdu = 0;

// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
    window.console && console.log('Document ready called');

    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" onclick="$(\'#position'+countPos+'\').remove();return false;"><br>\
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
    });

    $('#addEdu').click(function(event){
        event.preventDefault();
        if ( countEdu >= 9 ) {
            alert("Maximum of nine education entries exceeded");
            return;
        }
        countEdu++;
        window.console && console.log("Adding education "+countEdu);

        $('#edu_fields').append(
            '<div id="edu'+countEdu+'"> \
            <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
            <input type="button" value="-" onclick="$(\'#edu'+countEdu+'\').remove();return false;"><br>\
            <p>School: <input type="text" size="80" name="edu_school'+countEdu+'" class="school" value="" />\
            </p></div>'
        );

        $('.school').autocomplete({
            source: "school.php"
        });

    });

});

</script>
</div>
</body>
</html>
