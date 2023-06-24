<?php
// Define functions for getting the remote follow links


/*
    Given a remote user and instance, return an array representing the
    activitypub resource for that user if it exists, otherwise return False.
*/
function get_activitypub_resource(string $remote_user, string $remote_instance)
{
    // Use curl to find the remote subscription template file
    $curl_session = curl_init();
    curl_setopt($curl_session, CURLOPT_URL, "https://{$remote_instance}/.well-known/webfinger?resource=acct:{$remote_user}@{$remote_instance}");
    curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);

    // Allow redirects for webfinger lookup
    curl_setopt($curl_session, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl_session, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);

    // Allow redirects for webfinger lookup
    curl_setopt($curl_session, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl_session, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);

    $json_data = json_decode(curl_exec($curl_session), true);
    curl_close($curl_session);

    // if json parse fails, assume that the account does not exist
    // Mastodon returns invalid json while pleroma returns string
    if (empty($json_data)) {
        return array("success" => False, "output" => False);
    } elseif (!is_array($json_data)) {
        return array("success" => False, "output" => $json_data);
    }

    // if json parse succeeds, return the json
    return array("success" => True, "output" => $json_data);
}

/*
    Given the output of get_activitypub_resource, return the remote follow link
    if supported by the remote server, otherwise return False.
*/
function get_remote_follow_link(array $activitypub_resource_output, string $local_id)
{
    // fail if no links in the json
    if (!array_key_exists("links", $activitypub_resource_output)) {
        return False;
    }

    // find the user's profile link in the array
    foreach ($activitypub_resource_output["links"] as $link) {
        if (array_key_exists("rel", $link) && $link["rel"] == "http://ostatus.org/schema/1.0/subscribe") {
            // replace the template with the user's local id
            $follow_url = str_replace("{uri}", urlencode($local_id), $link["template"]);
            return $follow_url;
        }
    }

    return False;
}
