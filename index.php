<?php $ini_array = parse_ini_file('apfollow.ini');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST["remote_follow"])) {
    // get the content from the POST
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

    // import the ActivityPub functions
    include 'includes/functions.php';

    // get the remote user's activitypub resource
    $activitypub_resource = get_activitypub_resource($remote_user, $remote_instance);

    // If the function did not succeed, we return an error that the user could not be found
    if (!$activitypub_resource["success"]) {
        http_response_code(500);
        header('Content-Type: text/plain');
        print $activitypub_resource["output"] ? $activitypub_resource["output"] : "Couldn't find user";
        exit;
    }

    // get the remote follow link
    $follow_url = get_remote_follow_link($activitypub_resource["output"], $_POST["remote_follow"]["local_id"]);

    // If the function did not succeed, we return an error that subscribe schema 1.0 is not supported
    if (!$follow_url) {
        http_response_code(500);
        header('Content-Type: text/plain');
        print "Instance does not support subscribe schema version 1.0.";
        exit;
    }

    // Redirect to the follow link
    header("Location: {$follow_url}", true, 302);
    exit();
} elseif (!empty($_GET["user"]) && !empty($_GET["instance"]) || !empty($_GET["href"])) {
    // if this is a get request with the appropriate parameters, we display the form

    // Open curl session
    $curl_session = curl_init();
    curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);

    // Allow redirects for webfinger lookup
    curl_setopt($curl_session, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl_session, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);

    if (empty($_GET["href"])) {
        $local_user = $_GET["user"];
        $local_instance = $_GET["instance"];

        // Use curl to find the user's profile link
        curl_setopt($curl_session, CURLOPT_URL, "https://{$local_instance}/.well-known/webfinger?resource=acct:{$local_user}@{$local_instance}");
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
            if (array_key_exists("url", $json_data["icon"])) {
                $local_icon = $json_data["icon"]["url"];
            }
        }
        if (array_key_exists("image", $json_data)) {
            if (array_key_exists("url", $json_data["image"])) {
                $local_image = $json_data["image"]["url"];
            }
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
