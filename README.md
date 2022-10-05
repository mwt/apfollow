# AP Follow

## Basic usage

This is a remote follow tool for ActivityPub servers. With this tool, you can share links and buttons that allow people to follow you from their own ActivityPub instance. You can use by passing query parameters like so:

`https://apfollow.mwt.me/?user={username}&instance={domain.tld}`

[Here is a follow page generator](https://apfollow.mwt.me/)

[Here is an example follow page](https://apfollow.mwt.me/?user=mwt&instance=mathstodon.xyz).


## Advanced usage

The application does not rely on client-side javascript or relative links. So you can reverse proxy the service on your own domain. For example, you could add the following to an Apache `.htaccess` file to allow follows on the same paths used by Mastodon.

```apache
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^users/(.*)/remote_follow$ "https://apfollow.mwt.me/?user=$1&instance=yourdomain.com" [P,L]
</IfModule>
```
Similarly in Nginx:
```nginx
location ~ ^/users/(.*)/remote_follow$ {
    proxy_pass https://apfollow.mwt.me/?user=$1&instance=yourdomain.com;
}
```

This is beneficial because your follow links can be picked up by [the simplified federation browser extension](https://github.com/rugk/mastodon-simplified-federation/). Which will skip users needing to fill in their handle. 

## Example follow buttons

Just put your username and instance in the `href` field at the beginning of these links to get a follow button that you can put on your website.


### Gray AP button

![gray-ap-button-image](assets/ap-follow.png)

```html
<a href="https://apfollow.mwt.me/?user={username}&instance={domain.tld}" style="display:inline-block;color:#fff;text-decoration:none;font-size:14px;line-height:32px;font-weight:500;background:#6d6d6d;border-radius:4px;padding:4px 18px 4px 16px;font-family:Roboto,sans-serif">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2032 1536" style="margin-right:6px;vertical-align:text-top" height="14px"><path d="M923.767 256L0 789.321v213.321L738.999 576v853.321L923.767 1536zm184.768 0v213.321L1847.533 896l-738.998 426.642V1536l923.766-533.358v-213.32zm0 426.642v426.68L1478.034 896zM554.267 896l-369.536 213.321 369.536 213.321z" fill="#fff" fill-rule="evenodd"></path></svg>
    Follow me
</a>
```


### Pink AP button

![pink-ap-button-image](assets/ap-follow2.png)

```html
<a href="https://apfollow.mwt.me/?user={username}&instance={domain.tld}" style="display:inline-block;color:#fff;text-decoration:none;font-size:14px;line-height:32px;font-weight:500;background:#f1007e;border-radius:4px;padding:4px 18px 4px 16px;font-family:Roboto,sans-serif">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2032 1536" style="margin-right:6px;vertical-align:text-top" height="14px"><path d="M923.767 256L0 789.321v213.321L738.999 576v853.321L923.767 1536zm184.768 0v213.321L1847.533 896l-738.998 426.642V1536l923.766-533.358v-213.32zm0 426.642v426.68L1478.034 896zM554.267 896l-369.536 213.321 369.536 213.321z" fill="#fff" fill-rule="evenodd"></path></svg>
    Follow me
</a>
```


### Mastodon button

![mastodon-button-image](assets/mastodon-follow.png)

```html
<a href="https://apfollow.mwt.me/?user={username}&instance={domain.tld}" style="display:inline-block;color:#fff;text-decoration:none;font-size:14px;line-height:32px;font-weight:500;background:#2b90d9;border-radius:4px;padding:0 18px 0 16px;font-family:Roboto,sans-serif">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1536 1792" style="margin-right:6px;vertical-align:middle;padding:10px 0 12px 0" height="18px"> <path d="M1503.302 1111.386c-22.579 116.159-202.224 243.284-408.55 267.921-107.588 12.837-213.519 24.636-326.476 19.455-184.728-8.463-330.494-44.092-330.494-44.092 0 17.983 1.11 35.106 3.328 51.12 24.015 182.308 180.772 193.228 329.261 198.32 149.872 5.127 283.321-36.951 283.321-36.951l6.157 135.491s-104.827 56.293-291.574 66.646c-102.974 5.66-230.836-2.59-379.759-42.009C65.529 1641.797 10.219 1297.502 1.482 948.17-1.11 844.449.485 746.646.49 664.847.5 307.631 234.539 202.924 234.539 202.924c118.011-54.199 320.512-76.99 531.033-78.71h5.173c210.52 1.721 413.152 24.511 531.157 78.71 0 0 234.04 104.706 234.04 461.923 0 0 2.935 263.556-32.64 446.539zm-243.429-418.827c0-88.4-21.711-159.35-67.71-210.618-46.63-51.972-107.687-78.613-183.47-78.613-87.699 0-154.104 33.703-198.002 101.121L768 576l-42.683-71.55c-43.907-67.42-110.313-101.124-198.003-101.124-75.792 0-136.849 26.642-183.47 78.614-45.21 51.973-67.718 122.219-67.718 210.618v432.53h171.359V705.273c0-88.498 37.234-133.415 111.713-133.415 82.35 0 123.63 53.283 123.63 158.646v229.788h170.35V730.505c0-105.363 41.272-158.646 123.62-158.646 74.478 0 111.715 44.917 111.715 133.415v419.816h171.358V692.56z" fill="#fff"></path></svg>
    Follow me
</a>
```


## Why do I need this?

The most popular ActivityPub implementations (such as Mastodon and Pleroma) have remote follow implementations. Mastodon provides a remote follow interface at `/users/{username}/remote_follow` on each instance [(an example)](https://mathstodon.xyz/users/mwt/remote_follow). You can share this link to allow people to follow you. However, an actual follow link does not exist in most other implementations. For example, Pleroma uses a POST request to start the flow from the remote follow button. Therefore, it not possible to provide a direct link.

Many other implementations of ActivityPub lack any builtin remote follow support. [The WordPress plugin](https://github.com/pfefferle/wordpress-activitypub) and Pixelfed are examples. Without this feature, the only way for users to follow your content is for them to manually search for your account handle.

Moreover, it is nice when making/using templates to have a standard follow link that can be used for any ActivityPub implementation.


## License and credits

This project is inspired by [this blog post by Hugh Rundle](https://www.hughrundle.net/how-to-implement-remote-following-for-your-activitypub-project/) which documents the procedure for remote follows.
