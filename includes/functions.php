<?php
// Define functions for getting the remote follow links

/*
    Use the host-meta file to find the activitypub lrdd template for the remote
    instance. Return the template string if it exists, otherwise return False.
*/
function get_activitypub_lrdd(string $remote_instance): string|false
{
    // Use curl to find the host-meta file
    $curl_session = curl_init();
    curl_setopt($curl_session, CURLOPT_URL, "https://{$remote_instance}/.well-known/host-meta");
    curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);

    // Allow redirects for webfinger lookup
    curl_setopt($curl_session, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl_session, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);

    $xml_data = simplexml_load_string(curl_exec($curl_session));
    curl_close($curl_session);

    // if xml parse fails, return False
    if (empty($xml_data)) {
        return False;
    }

    // find the lrdd template in the xml
    foreach ($xml_data->Link as $link) {
        if ($link->attributes()->rel == "lrdd") {
            return (string) $link->attributes()->template;
        }
    }

    // if no lrdd template is found, return False
    return False;
}

/*
    Given a remote user and instance, return an array representing the
    activitypub resource for that user if it exists, otherwise return False.
*/
function get_activitypub_resource(string $remote_user, string $remote_instance): array
{
    // Get the lrdd template
    $activitypub_lrdd = get_activitypub_lrdd($remote_instance);

    if ($activitypub_lrdd === False) {
        // if the lrdd template does not exist, use the default template
        $resource_url = "https://{$remote_instance}/.well-known/webfinger?resource=acct:{$remote_user}@{$remote_instance}";
    } else {
        // if the lrdd template exists, use it to find the resource url
        $resource_url = str_replace("{uri}", "acct:{$remote_user}@{$remote_instance}", $activitypub_lrdd);
    }

    // Use curl to find the remote subscription template file
    $curl_session = curl_init();
    curl_setopt($curl_session, CURLOPT_URL, $resource_url);
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
function get_remote_follow_link(array $activitypub_resource_output, string $local_id): string|false
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
