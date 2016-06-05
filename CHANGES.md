### 2.11.1
* profile: fixed fatal for new user edits

### 2.11.0
* login: drop login with email in favor of WP4.5, [see](https://core.trac.wordpress.org/ticket/9568)
* profile: no display name per blog on user admin pages
* profile: fixed not removing per site display name
* profile: fixed dropped text domain
* admin: style for signup widget
* setting up gulp

### 2.10.0
* moved to [Semantic Versioning](http://semver.org/)
* shipping gplugin!
* drop supporting the old versions of gPlugin

### 0.2.9
* bumped min php/wp/gplugin ver
* profile: fixed possible override
* login: disable email login for WP4.5

### 0.2.8
* login: hide last login if disabled
* login: reordering fields
* profile: style for the admin page
* profile: fewer meta queries

### 0.2.7
* admin: sort by register date
* admin: table styling
* cleanup: default user options for skipped meta

### 0.2.6
* cleanup: utilizing the new `insert_user_meta` filter to avoid storing default user meta, see [#31549](https://core.trac.wordpress.org/ticket/31549)

### 0.2.5
* check for min gplugin version
* admin: spam view for network admin users list table
* admin: sort by registration date on network admin users list table
* profile: override comment author

### 0.2.4
* fixed semantic on admin users bp list last active
* fixed late set current user display name

### 0.2.3
* public repo on [github](https://github.com/geminorum/gmember)

### 0.2.0
* rewrite using [gPlugin](https://github.com/geminorum/gplugin)
