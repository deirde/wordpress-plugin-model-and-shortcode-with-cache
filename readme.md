WORDPRESS PLUGIN - BE MODEL AND SHORTCODE WITH CACHE
============================================
A WordPress plugin to transform theme views into cachable shortcodes.

Usage
====================

Menus
--------------------
```
<?php
global $BeModelMenuWithCache;
foreach ($BeModelMenuWithCache->wpGetNavMenuItems('menu-id') as $item) {
?>
    <li>
        <a href="<?php echo $item['url']; ?>" title="<?php echo $item['title']; ?>">
            <?php echo $item['title']; ?>
        </a>
    </li>
<?php } ?>
```

The output of is saved into a PERMANENT cache.
The cache is automatically flushed every time that the menu is updated.

Posts
--------------------
```
<?php
global $BeModelPostWithCache;
$params = ['post__in' => [1, 2], 'category_name' => 'news', 'ttl' => 3600, 'lazyLoad' => true]
$BeModelPostWithCache->getPosts($params);
?>

```

The output of is saved into a TIMED cache.
The cache is automatically flushed every time that the a post is created, updated or deleted.

`ttl` is the duration of the cache, in seconds.
The cache is flushed when a post in backend is created, updated or deleted and it is created again when the function is executed.
Avoid this parameter (or set it to `0`) to disable the cache.

`lazyLoad`, optional parmeter disabled by default.
If on `true` it makes the cache regenereting when a post in backend is created, updated or deleted.

To manually flush the cache: ```$BeModelPostWithCache->wpFlushQuery($params);```

Shortcodes
--------------------
```[be_shortcode view="/shortcode/my-file.php" ttl="3600"]</code>```

In the above example, the output of the file `my-file.php`, in the folder `shortcode` of the active theme will be served and cached for 3600 seconds.
Avoid the parameter `ttl` (or set it to `0`) to disable the cache.

Add any parameters, example:
<code>[be_shortcode view="/shortcode/my-file.php" ttl="3600" a="b" c="d"]</code>

All the parameters are accessible in `<theme>/shortcode/my-file.php` from the array `$params`, in this case `<?php echo $params['a']; ?>` will return `b`.
 