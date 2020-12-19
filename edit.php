<?php
require_once "pdo.php";
require_once "util.php";
session_start();
if (!isset($_SESSION['name']) || strlen($_SESSION['name']) < 1 || !isset($_SESSION['user_id']) || strlen($_SESSION['user_id']) < 1)
{
    die('ACCESS DENIED');
    return;
}

// Guardian: Make sure that profile_id is present
if (!isset($_REQUEST['profile_id']))
{
    $_SESSION["error"] = "Missing profile_id";
    header('Location: index.php');
    return;
}

//for Cancel button
if (isset($_POST['cancel']))
{
    header("Location: index.php");
    return;
}

// for profile data read
$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz and user_id=:uid");
$stmt->execute(array(
    ":xyz" => $_REQUEST['profile_id'],
    ":uid" => $_SESSION['user_id']
));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ($profile === false)
{
    $_SESSION['error'] = "Could Not load Profile";
    header('Location: index.php');
    return;
}
// store profile data
$fn = htmlentities($profile['first_name']);
$ln = htmlentities($profile['last_name']);
$em = htmlentities($profile['email']);
$he = htmlentities($profile['headline']);
$su = htmlentities($profile['summary']);
$pi = htmlentities($profile['profile_id']);

// for position data read
$positions = loadPos($pdo, $_REQUEST['profile_id']);
// for Education data read
$school = loadEdu($pdo, $_REQUEST['profile_id']);



/*  PROFILE   */
// handle incomimg data
if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']))
{
    // data validate for profile
    $msg = validateProfile();
    if (is_string($msg))
    {
        $_SESSION["error"] = $msg;
        header("Location: edit.php?profile_id=" . $_POST['profile_id']);
        return;
    }

    // update data to profile
    $sql = "UPDATE profile SET first_name = :first_name, last_name = :last_name,
            email = :email, headline = :headline, summary=:summary
            WHERE profile_id = :profile_id AND user_id=:uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':first_name' => $_POST['first_name'],
        ':last_name' => $_POST['last_name'],
        ':email' => $_POST['email'],
        ':headline' => $_POST['headline'],
        ':summary' => $_POST['summary'],
        ':profile_id' => $_REQUEST['profile_id']
    ));

    /*  POSITION  */

    //for valodate position
    $msg = validatePos();
    if (is_string($msg))
    {
        $_SESSION["error"] = $msg;
        header("Location: edit.php?profile_id=" . $_REQUEST['profile_id']);
        return;
    }

    // update data to profile position
    // Clear out the old position entries
    $stmt = $pdo->prepare('DELETE FROM Position
    WHERE profile_id=:pid');
    $stmt->execute(array(
        ':pid' => $_REQUEST['profile_id']
    ));

    // Insert the position entries
    insertPositions($pdo,$_REQUEST[profile_id]);

    /*  EDUCATION  */
    //for valodate EDUCATION
    $msg = validateEdu();
    if (is_string($msg))
    {
        $_SESSION["error"] = $msg;
        header("Location: edit.php?profile_id=" . $_REQUEST['profile_id']);
        return;
    }

    // Clear out the old Education entries
    $stmt = $pdo->prepare('DELETE FROM Education
      WHERE profile_id=:pid');
    $stmt->execute(array(
        ':pid' => $_REQUEST['profile_id']
    ));

    // Insert the Education entries
    insertEducation($pdo,$_REQUEST[profile_id]);

    $_SESSION['success'] = 'Record updated';
    header('Location: index.php');
    return;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Sayed Makhdum Ullah- autosdb</title>
<?php require_once "bootstrap.php"; ?>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container">
<h1>Editing profile for <?=htmlentities($_SESSION['name']); ?> </h1>
<?php
flashMessages();
?>
<form method="post" action="edit.php">
<input type="hidden" name="profile_id" value="<?=htmlentities($_GET['profile_id']) ?>">
<p>first_name:
<input type="text" name="first_name" size="60" value="<?=$fn ?>"></p>
<p>last_name:
<input type="text" name="last_name" size="60" value="<?=$ln ?>"></p>
<p>email:
<input type="text" name="email" size="30" value="<?=$em ?>"></p>
<p>headline:
<input type="text" name="headline" size="60" value="<?=$he ?>"></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80" value=""><?=$su ?></textarea></p>


<?php
// for displying Education entries:
echo '<p>Education: <input type="submit" id="addEdu" value="+"/>' . "\n";
echo '<div id="edu_fields">' . "\n";
$countEdu = 0;
if ($school == false)
{
    echo '<p style="color:blue">No education Retrived for this profile yet.You can add now</p>';
}
else
{
    foreach ($school as $row)
    {
        $countEdu++;
        echo '<div id="position' . $countEdu . '"> ';
        echo ('<p>Year: <input type="text" name="edu_year' . $countEdu . '" size="10" value="' . htmlentities($row['year']) . '">' . "\n");
        echo ('<input type="button" value="-" ');
        echo ('onclick="$(\'#position' . $countEdu . '\').remove();return false;">' . "\n");
        echo "</p>\n";
        echo ('<p>School: <input type="text" name="edu_school' . $countEdu . '"  size="40" value="' . htmlentities($row['name']) . '">' . "\n");
        echo '</div>' . "\n";
    }
}
echo "</div>\n";


// for displaying Position entries:
echo '<p>Position: <input type="submit" id="addPos" value="+"/>' . "\n";
echo '<div id="position_fields">' . "\n";
$countPos = 0;
if ($positions == false)
{
    echo '<p style="color:blue">No position Retrived for this profile yet.You can add now</p>';
}
else
{
    foreach ($positions as $row)
    {
        $countPos++;
        echo '<div id="position' . $countPos . '"> ';
        echo ('<p>Year: <input type="text" name="year' . $countPos . '" size="10" value="' . htmlentities($row['year']) . '">' . "\n");
        echo ('<input type="button" value="-" ');
        echo ('onclick="$(\'#position' . $countPos . '\').remove();return false;">' . "\n");
        echo "</p>\n";
        echo ('<textarea name="desc' . $countPos . '" rows="8" cols="80">' . htmlentities($row['description']) . "\n" . '</textarea></p>');
        echo '</div>' . "\n";
    }
}
echo "</div>\n";
?>

</p>
<p><input type="submit" value="Save"/>
<input type="submit" name="cancel" value="Cancel"/>
</p>
</form>


<!--creating new append both EDUCATION and POSITION related fields-->
<script>
countPos = <?=$countPos ?>;
countEdu= <?=$countEdu ?>;
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
<!-- HTML with Substitution hot spots -->
<script id="edu-template" type="text">
  <div id="edu@COUNT@">
    <p>Year: <input type="text" name="edu_year@COUNT@" value="" />
    <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br>
    <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
    </p>
  </div>
</script>
</div>
</body>
</html>
