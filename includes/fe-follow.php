<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta content='width=device-width, initial-scale=1' name='viewport'>
    <title>Follow <?=$local_user?></title>
    <meta content='#212529' name='theme-color'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <style>.follow-container{max-width: 425px}</style>
</head>

<body class="bg-dark text-light">
    <div class="container follow-container">
        <h2 class="text-center my-3 fs-5">You are going to follow:</h2>
        <div class="card bg-dark">
            <a class="text-decoration-none" target="_blank" rel="noopener noreferrer" href="<?=$local_identifier?>">
                <?php if (isset($local_image)) {?>
                <img class="card-img-top d-none d-sm-block" alt="" src="<?=$local_image?>">
                <?php } ?>
                <div class="card-header d-flex">
                    <img alt="" class="rounded" src="<?=(isset($local_icon)) ? $local_icon : $ini_array["placeholder_image"]?>" width="48" height="48">
                    <div class="mx-3">
                        <strong class="p-name text-light"><?=(isset($local_fullname)) ? $local_fullname : $local_user?></strong>
                        <span class="d-block text-white-50">@<?=$local_user?>@<?=$local_instance?></span>
                    </div>
                </div>
            </a>
            <div class="card-body">
                <form novalidate="novalidate" action="" enctype="multipart/form-data" accept-charset="UTF-8" method="post">
                    <div class="mb-3">
                        <input type="hidden" name="remote_follow[local_id]" value="<?=$local_identifier?>">
                        <input autocapitalize="none" autocorrect="off" class="form-control bg-dark text-light border-secondary" placeholder="Enter your username@domain you want to follow from" type="text" name="remote_follow[acct]" id="remote_follow_acct">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Proceed to follow</button>
                </form>
            </div>
            <div class="card-footer">
                <p class="card-text text-white-50 text-center lh-sm">
                    <small>Why is this step necessary? <code><?=$local_instance?></code> might not be the server where you are registered, so we need to redirect you to your home server first.</small>
                </p>
            </div>
        </div>
    </div>
</body>

</html>
