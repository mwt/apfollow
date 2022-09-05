# AP Follow

## Basic usage

This is a remote follow tool for ActivityPub servers. With this tool, you can share links and buttons that allow people to follow you from their own ActivityPub instance. You can use by passing query parameters like so:

`https://apfollow.mwt.me/?user={username}&instance={domain.tld}`

[Here is an example page](https://apfollow.mwt.me/?user=mwt&instance=mathstodon.xyz).


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


## Why do I need this?

The most popular ActivityPub implementations (such as Mastodon, Pleroma, and Pixelfed) already allow for remote follow links. For example, Mastodon provides a remote follow interface at `/users/{username}/remote_follow` on each instance [(an example)](https://mathstodon.xyz/users/mwt/remote_follow). You can share this link to allow people to follow you.

Many other implementations of ActivityPub lack this feature. [The WordPress plugin](https://github.com/pfefferle/wordpress-activitypub) is an example. Without this feature, the only way for users to follow your content is for them to manually search for your account handle.

Moreover, it is nice when making/using templates to have a standard follow link that can be used for any ActivityPub implementation.


## License

This tool shamelessly uses Mastodon frontend elements. Because Mastodon is licensed under the AGPL-3.0, this software also must follow this license.
