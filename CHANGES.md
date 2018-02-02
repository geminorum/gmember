### 2.14.2
* main/main: bail if gNetwork exists!
* main/admin: :warning: current time for human time diff
* main/login: :warning: current time for human time diff
* main/login: :new: logout after url option
* main/login: :warning: current super admin can change his nickname
* main/signup: normalize register ip before storeing

### 2.14.1
* main/admin: ip column on last signup widget
* main/admin: missing ago on timestamp
* main/network: :warning: correct spam count storing
* main/profile: change ordering for custom display name, [see](https://make.wordpress.org/core/?p=20592)

### 2.14.0
* main/network: date format helper
* main/admin: rethinking admin widget footer
* main/admin: disable widget if no last login stored
* main/cleanup: skip empty locale meta
* main/cleanup: skip empty contact method meta
* main/cleanup: prevent bp last activity back-comp meta, [see](http://wp.me/pLVLj-gc)
* main/profile: default contact methods removed
* main/profile: option to disable color schemes
* main/profile: option to include user display name in the search
* main/signup: default color scheme

### 2.13.0
* all: moved to gPlugin html class generators
* main/network: internal api for lookup ip
* main/network: spam count event
* main/admin: rethinking signup/login widget columns
* main/admin: re-design for after dashboard widgets
* main/admin: using wp helper to total user count
* main/admin: correct datetime from gmt
* main/admin: correct last logins order
* main/admin: missed constants for meta key
* main/admin: default/sort by registration date on users table list
* main/groups: removed!
* main/login: correct datetime from gmt
* main/signup: extra check for custom signup url
* main/signup: signup after url

### 2.12.0
* admin: last logins widget
* login: correct redirect to home/admin
* styles: re-organizing

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
