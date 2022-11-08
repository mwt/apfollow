<?php $ini_array = parse_ini_file('apfollow.ini');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST["remote_follow"])) {
    // get the comment from the POST
    $acct_array = explode("@", $_POST["remote_follow"]["acct"]);
    // the user may input @user@domain.tld or user@domain.tld
    switch (count($acct_array)) {
        case 2:
            $remote_user = $acct_array[0];
            $remote_instance = $acct_array[1];
            break;
        case 3:
            $remote_user = $acct_array[1];
            $remote_instance = $acct_array[2];
            break;
        default:
            http_response_code(500);
            header('Content-Type: text/plain');
            print "Input not understood";
            exit;
    }

    // Use curl to find the remote subscription template file
    $curl_session = curl_init();
    curl_setopt($curl_session, CURLOPT_URL, "https://${remote_instance}/.well-known/webfinger?resource=acct:${remote_user}@${remote_instance}");
    curl_setopt($curl_session, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);

    $json_data = json_decode(curl_exec($curl_session), true);
    curl_close($curl_session);

    // if json parse fails, assume that the account does not exist
    // Mastodon returns invalid json while pleroma returns string
    if (empty($json_data)) {
        http_response_code(500);
        header('Content-Type: text/plain');
        print "Couldn't find user";
        exit;
    } elseif (!is_array($json_data)) {
        http_response_code(500);
        header('Content-Type: text/plain');
        print $json_data;
        exit;
    } elseif (!array_key_exists("links", $json_data)) {
        http_response_code(500);
        header('Content-Type: text/plain');
        print "Couldn't find user";
        exit;
    }

    // We need to parse to find the link with a rel equal to the schema for subscribe
    foreach ($json_data["links"] as $link) {
        if (array_key_exists("rel", $link) && $link["rel"] == "http://ostatus.org/schema/1.0/subscribe") {
            $subscribe_template = $link["template"];
            break;
        }
    }

    // Perform the redirect
    if (isset($subscribe_template)) {
        $follow_url = str_replace("{uri}", urlencode($_POST["remote_follow"]["local_id"]), $subscribe_template);
        header("Location: ${follow_url}", true, 302);
        exit();
    } else {
        http_response_code(500);
        header('Content-Type: text/plain');
        print "Instance does not support subscribe schema version 1.0.";
        exit;
    }

// if this is a get request with the appropriate parameters, we display the form
} elseif (!empty($_GET["user"]) && !empty($_GET["instance"]) || !empty($_GET["href"])) {
    // Open curl session
    $curl_session = curl_init();
    curl_setopt($curl_session, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);

    if (empty($_GET["href"])) {
        $local_user = $_GET["user"];
        $local_instance = $_GET["instance"];

        // Use curl to find the user's profile link
        curl_setopt($curl_session, CURLOPT_URL, "https://${local_instance}/.well-known/webfinger/?resource=acct:${local_user}@${local_instance}");
        $json_data = json_decode(curl_exec($curl_session), true);

        // if json parse fails, assume that the account does not exist
        // Mastodon returns invalid json while pleroma returns string
        if (empty($json_data)) {
            http_response_code(500);
            header('Content-Type: text/plain');
            print "Couldn't find user";
            exit;
        } elseif (!is_array($json_data)) {
            http_response_code(500);
            header('Content-Type: text/plain');
            print $json_data;
            exit;
        } elseif (!array_key_exists("links", $json_data)) {
            http_response_code(500);
            header('Content-Type: text/plain');
            print "Couldn't find user";
            exit;
        }

        // find the user's profile link in the array
        foreach ($json_data["links"] as $link) {
            if (array_key_exists("type", $link) && $link["type"] == "application/activity+json") {
                $profile_link = $link["href"];
                break;
            }
        }
    } else {
        // href was defined
        $profile_link = $_GET["href"];
        $local_instance = parse_url($profile_link, PHP_URL_HOST);
    }

    // make a request to the profile link
    curl_setopt($curl_session, CURLOPT_URL, $profile_link);
    curl_setopt($curl_session, CURLOPT_HTTPHEADER, ["Accept: application/activity+json"]);
    curl_setopt($curl_session, CURLOPT_FOLLOWLOCATION, true);
    $json_data = json_decode(curl_exec($curl_session), true);
    curl_close($curl_session);

    // if json parse fails, return error
    if (!empty($json_data) && is_array($json_data)) {
        if (array_key_exists("id", $json_data)) {
            $local_identifier = $json_data["id"];
        } else {
            // the user profile has no id
            http_response_code(500);
            header('Content-Type: text/plain');
            print "No id found for user profile.";
            exit;
        }
        if (array_key_exists("name", $json_data)) {
            $local_fullname = $json_data["name"];
        }
        if (array_key_exists("icon", $json_data)) {
            $local_icon = $json_data["icon"]["url"];
        }
        if (array_key_exists("image", $json_data)) {
            $local_image = $json_data["image"]["url"];
        }
        // if the user specified the id manually, we should set the username
        if (empty($local_user)) {
            if (array_key_exists("preferredUsername", $json_data)) {
                $local_user = $json_data["preferredUsername"];
            } else {
                $local_user = "unknown_user";
            }
        }
    } else {
        // do our best in the event that the request is unauthorized
        // the local identifier and json profile link should be the same
        $local_identifier = $profile_link;
    }

    include 'includes/fe-follow.php';
} else {
    include 'includes/fe-index.html';
}
