<?php
// here i use util code externally
session_start();
require_once "pdo.php";
require_once "util.php";
if (!isset($_SESSION['name']) || strlen($_SESSION['name']) < 1 || !isset($_SESSION['user_id']) || strlen($_SESSION['user_id']) < 1)
{
    die('ACCESS DENIED');
    return;
}

//for Cancel button
if (isset($_POST['cancel']))
{
    header("Location: index.php");
    return;
}


/*  PROFILE   */
// handle incomimg data
if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']))
{
    // data validate for profile
    $msg = validateProfile();
    if (is_string($msg))
    {
        $_SESSION["error"] = $msg;
        header("Location: add.php");
        return;
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
        ':su' => $_POST['summary'])
    );
        $profile_id=$pdo->LastInsertId();
    /*  POSITION  */

    //for valodate position
    $msg = validatePos();
    if (is_string($msg))
    {
        $_SESSION["error"] = $msg;
        header("Location: add.php");
        return;
    }

    // Insert the position entries
    insertPositions($pdo,$profile_id);


    /*  EDUCATION  */
    //for valodate EDUCATION
    $msg = validateEdu();
    if (is_string($msg))
    {
        $_SESSION["error"] = $msg;
        header("Location: add.php");
        return;
    }
    insertEducation($pdo,$profile_id);
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
flashMessages();
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
