## XForum - Apache .htaccess mod_rewrite

First at the prompt ie on ubuntu run the following:-

    $ sudo a2enmod rewrite 
    $ sudo service apache2 reload

The following .htaccess is purely fulled by example, it is set for the basename of 'chronicals'

    # XForum Service Mod Rewrite
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^chronocals(.*?)/(.*?)/(.*?)/images/(.*?)/(.*?)/(.*?)/(.*) modules/xforum/images/$4/$5/$6/$7
    RewriteRule ^chronocals(.*?)/(.*?)/(.*?)/images/(.*?)/(.*?)/(.*) modules/xforum/images/$4/$5/$6 
    RewriteRule ^chronocals(.*?)/(.*?)/(.*?)/images/(.*?)/(.*) modules/xforum/images/$4/$5 
    RewriteRule ^chronocals(.*?)/(.*?)/(.*?)/images/(.*) modules/xforum/images/$4 
    RewriteRule ^chronocals(.*?)/(.*?)/images/(.*?)/(.*?)/(.*?)/(.*) modules/xforum/images/$3/$4/$5/$6 
    RewriteRule ^chronocals(.*?)/(.*?)/images/(.*?)/(.*?)/(.*) modules/xforum/images/$3/$4/$5 
    RewriteRule ^chronocals(.*?)/(.*?)/images/(.*?)/(.*) modules/xforum/images/$3/$4 
    RewriteRule ^chronocals(.*?)/(.*?)/images/(.*) modules/xforum/images/$3 
    RewriteRule ^chronocals(.*?)/images/(.*?)/(.*?)/(.*?)/(.*) modules/xforum/images/$2/$3/$4/$5 
    RewriteRule ^chronocals(.*?)/images/(.*?)/(.*?)/(.*) modules/xforum/images/$2/$3/$4 
    RewriteRule ^chronocals(.*?)/images/(.*?)/(.*) modules/xforum/images/$2/$3
    RewriteRule ^chronocals(.*?)/images/(.*) modules/xforum/images/$2 
    RewriteRule ^chronocalsimages/(.*?)/(.*?)/(.*?)/(.*) modules/xforum/images/$1/$2/$3/$4 
    RewriteRule ^chronocalsimages/(.*?)/(.*?)/(.*) modules/xforum/images/$1/$2/$3 
    RewriteRule ^chronocalsimages/(.*?)/(.*) modules/xforum/images/$1/$2
    RewriteRule ^chronocalsimages/(.*) modules/xforum/images/$1 
    RewriteRule ^chronocals(.*?)/(.*?)/([0-9]+),([0-9]+),([0-9]+),(.*?),(.*?),([0-9]+),([0-9]+).html$ modules/xforum/viewforum.php?forum=$3&since=$4&start=$5&sortorder=$6&sortname=$7&mode=$8&type=$9 [L,NC,QSA]
    RewriteRule ^chronocals(.*?)/(.*?)/(.*?)/([0-9]+),([0-9]+),([0-9]+),([0-9]+),(.*?),(.*?).html$ modules/xforum/viewtopic.php?forum=$4&topic_id=$5&post_id=$6&start=$7&since=$8&order=$9 [L,NC,QSA]
    RewriteRule ^chronocals(.*?)/(.*?)/(.*?)/([0-9]+),([0-9]+),([0-9]+).html$ modules/xforum/viewtopic.php?forum=$4&topic_id=$5&post_id=$6 [L,NC,QSA]
    RewriteRule ^chronocals(.*?)/(.*?)/(.*?)/([0-9]+),([0-9]+),([a-zA-Z0-9]+).html$ modules/xforum/viewtopic.php?forum=$4&topic_id=$5&viewmode=$6 [L,NC,QSA]
    RewriteRule ^chronocals(.*?)/(.*?)/(.*?)/([0-9]+),([0-9]+).html$ modules/xforum/viewtopic.php?forum=$4&topic_id=$5 [L,NC,QSA]
    RewriteRule ^chronocals(.*?)/(.*?)/(.*?)/(.*?).php$ modules/xforum/$4.php [L,NC,QSA]
    RewriteRule ^chronocals(.*?)/(.*?)/newtopic,(.*?),([0-9]+).html$ modules/xforum/newtopic.php?op=$3&forum=$4 [L,NC,QSA]
    RewriteRule ^chronocals(.*?)/(.*?)/newtopic,([0-9]+).html$ modules/xforum/newtopic.php?forum=$3 [L,NC,QSA]
    RewriteRule ^chronocals(.*?)/(.*?)/reply,([0-9]+),([0-9]+),([0-9]+).html$ modules/xforum/reply.php?forum=$3&topic_id=$4&post_id=$5 [L,NC,QSA]
    RewriteRule ^chronocals(.*?)/(.*?)/poll,([a-zA-Z0-9]+),([0-9]+),([0-9]+).html$ modules/xforum/poll.php?op=$3&topic_id=$4&poll_id=$5 [L,NC,QSA]
    RewriteRule ^chronocals(.*?)/(.*?)/edit,([0-9]+),([0-9]+).html$ modules/xforum/edit.php?forum=$3&post_id=$4 [L,NC,QSA]
    RewriteRule ^chronocals(.*?)/(.*?)/archive,([0-9]+).html$ modules/xforum/archive.php?forum=$3 [L,NC,QSA]
    RewriteRule ^chronocals(.*?)/(.*?)/archive,([0-9]+),([0-9]+).html$ modules/xforum/archive.php?forum=$3&topic_id=$4 [L,NC,QSA]
    RewriteRule ^chronocals(.*?)/(.*?)/(.*?).php$ modules/xforum/$3.php [L,NC,QSA]
    RewriteRule ^chronocals/rss,([0-9]+),(.*?).rss$ modules/xforum/rss.php?c=$1&f=$2 [L,NC,QSA]
    RewriteRule ^chronocals/viewall,(.*?),([0-9]+),([0-9]+),([0-9]+),(.*?),(.*?).html$ modules/xforum/viewall.php?type=$1&mode=$2&start=$3&since=$4&sortname=$5&sortorder=$6 [L,NC,QSA]
    RewriteRule ^chronocals/viewpost,([0-9]+),([0-9]+),(.*?),([0-9]+),(.*?)html$ modules/xforum/viewpost.php?forum=$1&start=$2&order=$3&uid=$4&mode=$5&type=$6 [L,NC,QSA]
    RewriteRule ^chronocals/cat,([0-9]+).html$ modules/xforum/index.php?cat=$1 [L,NC,QSA]
    RewriteRule ^chronocals/(.*?).php$ modules/xforum/$1.php [L,NC,QSA]
    RewriteRule ^chronocals/search.html$ modules/xforum/search.php [L,NC,QSA]
    RewriteRule ^chronocals/index.html$ modules/xforum/index.php [L,NC,QSA]

## XForum - Getting to work

You of course with what a multiforum is have to unplug the cork of the bottle, with multithreading and multifields, in the projections and transposition, you will notice the whole thing throws errors at the start unless you drop the contents of:-

    function xforum_welcome()
    {
       // All the code removed from here...
    }

After that it is complete smooth sailing...