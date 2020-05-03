<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once("../UserStories/User/LoadUser.php");
require_once("../UserStories/User/LoadUsers.php");
require_once("../UserStories/User/SaveUser.php");
require_once("../UserStories/User/DeleteUser.php");

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    Create();
}
else if ($_SERVER["REQUEST_METHOD"] == "PUT")
{
    Update();
}
else if ($_SERVER["REQUEST_METHOD"] == "DELETE")
{
    Delete();
}
else
{
    Get();
}

function Create()
{
    global $logger;
    $logger->info("User-erzeugen gestartet");

    $userObject = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() != JSON_ERROR_NONE)
    {
        $logger->error(json_last_error().json_last_error_msg().PHP_EOL.PHP_EOL);
        return;
    }

    $userFactory = new UserFactory();
    $user = $userFactory->convertToInstance($userObject);

    $saveUser = new SaveUser();
    $saveUser->setUser($user);

    if ($saveUser->run())
    {
        echo json_encode($saveUser->getUser());
    }
    else
    {
        http_response_code(500);
        echo json_encode($saveUser->getMessages());
    }

    $logger->info("User-erzeugen beendet");
}

function Update()
{
    global $logger;
    $logger->info("User-anhand-ID-aktualisieren gestartet");

    $userObject = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() != JSON_ERROR_NONE)
    {
        $logger->error(json_last_error().json_last_error_msg().PHP_EOL.PHP_EOL);
        return;
    }

    if (isset($_GET["Id"]))
    {
        $userObject["Id"] = $_GET["Id"];
    }

    $userFactory = new UserFactory();
    $user = $userFactory->convertToInstance($userObject);

    $saveUser = new SaveUser();
    $saveUser->setUser($user);

    if ($saveUser->run())
    {
        echo json_encode($saveUser->getUser());
    }
    else
    {
        http_response_code(500);
        echo json_encode($saveUser->getMessages());
    }

    $logger->info("User-anhand-ID-aktualisieren beendet");
}

function Delete()
{
    global $logger;
    $logger->info("User-anhand-ID-löschen gestartet");

    $loadUser = new LoadUser();

    if (isset($_GET["Id"]))
    {
        $loadUser->setId(intval($_GET["Id"]));
    }

    if ($loadUser->run())
    {
        $user = $loadUser->getUser();

        $deleteUser = new DeleteUser();
        $deleteUser->setUser($user);

        if ($deleteUser->run())
        {
            echo json_encode($user);
        }
        else
        {
            http_response_code(500);
            echo json_encode($deleteUser->getMessages());
        }
    }
    else
    {
        http_response_code(500);
        echo json_encode($loadUser->getMessages());
    }

    $logger->info("User-anhand-ID-löschen beendet");
}

function Get()
{
    global $logger;

    if (isset($_GET["Id"]))
    {
        $logger->info("User-anhand-ID-laden gestartet");
        $loadUser = new LoadUser();
        $loadUser->setId(intval($_GET["Id"]));

        if ($loadUser->run())
        {
            echo json_encode($loadUser->getUser());
        }
        else
        {
            http_response_code(500);
            echo json_encode($loadUser->getMessages());
        }

        $logger->info("User-anhand-ID-laden beendet");
    }
	else if (isset($_GET["Guid"]))
	{
        $logger->info("User-anhand-GUID-laden gestartet");
        $loadUser = new LoadUser();
        $loadUser->setGuid($_GET["Guid"]);

        if ($loadUser->run())
        {
            echo json_encode($loadUser->getUser());
        }
        else
        {
            http_response_code(500);
            echo json_encode($loadUser->getMessages());
        }

        $logger->info("User-anhand-GUID-laden beendet");
	}
    else
    {
        $logger->info("Users-laden gestartet");
        $loadUsers = new LoadUsers();

        if ($loadUsers->run())
        {
            echo json_encode($loadUsers->getUsers());
        }
        else
        {
            http_response_code(500);
            echo json_encode($loadUsers->getMessages());
        }

        $logger->info("Users-laden beendet");
    }
}
