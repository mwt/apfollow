<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta content='width=device-width, initial-scale=1' name='viewport'>
    <title>Follow <?=$local_user?></title>
    <meta content='#282c37' name='theme-color'>
    <style>
    <?php include 'includes/mastodon.css';?>
    </style>
</head>

<body class="modal-layout theme-default no-reduce-motion">
    <div class="container-alt">
        <div class="form-container">
            <div class="follow-prompt">
                <h2>You are going to follow:</h2>
                <div class="card h-card">
                    <a target="_blank" rel="noopener noreferrer" href="<?=$local_identifier?>">
                        <div class="card__img">
                            <img alt=""
                                src="<?=(isset($local_image)) ? $local_image : $ini_array["placeholder_image"]?>">
                        </div>
                        <div class="card__bar">
                            <div class="avatar">
                                <img alt="" class="u-photo"
                                    src="<?=(isset($local_icon)) ? $local_icon : $ini_array["placeholder_image"]?>"
                                    width="48" height="48">
                            </div>
                            <div class="display-name">
                                <bdi>
                                    <strong class="p-name"><?=(isset($local_fullname)) ? $local_fullname : $local_user?></strong>
                                </bdi>
                                <span>
                                    @<?=$local_user?>@<?=$local_instance?>
                                    <i data-hidden="true" class="fa fa-lock"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <form class="simple_form new_remote_follow" id="new_remote_follow" novalidate="novalidate" action=""
                enctype="multipart/form-data" accept-charset="UTF-8" method="post">
                <input type="hidden" name="remote_follow[local_id]" value="<?=$local_identifier?>">
                <div class="input string required remote_follow_acct"><input autocapitalize="none" autocorrect="off"
                        class="string required" placeholder="Enter your username@domain you want to act from"
                        type="text" name="remote_follow[acct]" id="remote_follow_acct"></div>
                <div class="actions">
                    <button name="button" type="submit" class="btn">Proceed to follow</button>
                </div>
                <p class="hint subtle-hint">
                    <strong>Why is this step necessary?</strong> <code><?=$local_instance?></code> might not be the server
                    where you are registered, so we need to redirect you to your home server first.
                    Don't have an account? You can <a href="https://joinmastodon.org/servers" target="_blank">sign up here</a>
                </p>
            </form>
        </div>
    </div>
</body>

</html>
