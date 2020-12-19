<?php
// flas message
function flashMessages()
{

    if (isset($_SESSION['error']))
    {
        echo '<p style="color:red">' . $_SESSION['error'] . "</p>\n";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success']))
    {
        echo '<p style="color:green">' . $_SESSION['success'] . "</p>\n";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['message']))
    {
        echo '<p style="color:blue">' . $_SESSION['message'] . "</p>\n";
        unset($_SESSION['success']);
    }
}

/*  PROFILE   */
function validateProfile()
{
    // data validate for profile
    if (strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1)
    {

        return "All fields are required";
    }

    if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL))
    {

        return "Email must have an at-sign (@)";
    }
    return true;
}

function validatePos()
{
    for ($i = 1;$i <= 9;$i++)
    {
        if (!isset($_POST['year' . $i])) continue;
        if (!isset($_POST['desc' . $i])) continue;
        $year = $_POST['year' . $i];
        $desc = $_POST['desc' . $i];
        if (strlen($year) == 0 || strlen($desc) == 0)
        {
            return "All fields required!";
        }
        if (!is_numeric($year))
        {
            return "Position year must be numeric!";
        }
    }
    return true;
}

function validateEdu()
{
    for ($i = 1;$i <= 9;$i++)
    {
        if (!isset($_POST['edu_year' . $i])) continue;
        if (!isset($_POST['edu_school' . $i])) continue;
        $year = $_POST['edu_year' . $i];
        $school = $_POST['edu_school' . $i];
        if (strlen($year) == 0 || strlen($school) == 0)
        {
            return "All fields required!";
        }
        if (!is_numeric($year))
        {
            return "Education year year must be numeric!";
        }
    }
    return true;
}

function loadPos($pdo, $profile_id)
{
    $stmt1 = $pdo->prepare("SELECT * FROM position where profile_id = :xyz ORDER BY rank");
    $stmt1->execute(array(
        ":xyz" => $profile_id
    ));
    $positions = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    return $positions;
}
function loadEdu($pdo, $profile_id)
{
    $stmt1 = $pdo->prepare("SELECT year, name FROM Education join institution
     ON Education.institution_id = institution.institution_id
     where profile_id = :xyz ORDER BY rank");
    $stmt1->execute(array(
        ":xyz" => $profile_id
    ));
    $educations = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    return $educations;
}

function insertPositions($pdo, $profile_id)
{
    $rank = 1;
    for ($i = 1;$i <= 9;$i++)
    {
        if (!isset($_POST['year' . $i])) continue;
        if (!isset($_POST['desc' . $i])) continue;
        $year = $_POST['year' . $i];
        $desc = $_POST['desc' . $i];

        $stmt = $pdo->prepare('INSERT INTO Position
             (profile_id, rank, year, description)
         VALUES ( :pid, :rank, :year, :desc)');
        $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc
        ));
        $rank++;
    }
}

function insertEducation($pdo, $profile_id)
{
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

}

?>
