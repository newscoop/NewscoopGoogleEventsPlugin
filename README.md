Newscoop GoogleEventsPluginBundle
===================

This Newscoop Plugin adds smarty functions and Admin tools to enable you to ingest, manage, and display Google calendar events in Newscoop.

Installation
-------------
Installation is a quick process:


1. How to install this plugin?
2. That's all!

### Step 1: How to install this plugin?
Run the command:
``` bash
$ php application/console plugins:install "newscoop/google-events-plugin-bundle"
$ php application/console assets:install public/
```
Plugin will be installed to your project's `newscoop/plugins/Newscoop` directory.

### Step 2: That's all!
Go to Newscoop Admin panel and then open `Plugins` tab. The Plugin will show up there. You can now use the plugin.


**Note:**

To update this plugin run the command:
``` bash
$ php application/console plugins:update "newscoop/google-events-plugin-bundle"
$ php application/console assets:install public/
```

To remove this plugin run the command:
``` bash
$ php application/console plugins:remove "newscoop/google-events-plugin-bundle"
```

Documentation:
-------

A more detailed documentation can be found [here](https://wiki.sourcefabric.org/display/NPS/Google+Events+Plugin).

### Google Events View

Provides endpoint, **/google-events/events** for viewing all google events (cached locally).  Loads template **Resources/views/GoogleEvents/google_events.tpl** or **_views/google_events.tpl** if it exists in the loaded theme.

Usage:
```smarty
  {{ foreach $events as $event }}
    <div>
      <a href="/google-events/events/{{ $event->getId() }}">{{ $event->getId() }}</a>
      <br />
      {{ $event->getSummary() }}
      <br />
      {{ $event->getStart()|date_format:"%Y-%m-%d %H:%M" }} -
      {{ $event->getEnd()|date_format:"%Y-%m-%d %H:%M"}}
    </div>
  {{ /foreach }}
```

### Google Event View

Provides endpoint, **/google-events/events/{id}** for viewing single google event (cached locally).  Loads template **Resources/views/GoogleEvents/google_event.tpl** or **_views/google_event.tpl** if it exists in the loaded theme.

Usage:
```smarty
  {{ $event->getId() }}
  {{ $event->getDescription() }}
  {{ $event->getStart()|date_format:"%Y-%m-%d %H:%M" }} -
  {{ $event->getEnd()|date_format:"%Y-%m-%d %H:%M"}}
  {{ $event->getCreatorDisplayName() }}
  {{ $event->getCreatorEmail() }}
  {{ $event->getSummary() }}
  {{ $event->getHtmlLink() }}
```

### Google Events Search

Note that this only searches the locally stored InstagramPhoto entities, it does NOT make a call to the Instagram Api

Provides endpoint **/google-events/search**, which takes the following params:

1. search - search string, matches against caption, username, tags, and locationName fields
2. perPage - number of records per page to send in the results
3. offset - the first record to start with (used for pagination)

Results are delivered to **_views/google_events_search_results.tpl**, if defined in your theme, or a default internal view is used.


### List Google Evenets Smarty Block


Provides a smarty block to list instagrams photos with a specfific hashtag.

Usage:
```smarty
{{ list_google_events length=30 }}
    {{ $event->getSummary() }}
    {{ $event->getStart()|date_format:"%Y-%m-%d %H:%M" }}
    {{ $event->getEnd()|date_format:"%Y-%m-%d %H:%M" }}
{{ /list_google_events }}
```

License
-------

This bundle is under the GNU General Public License v3. See the complete license in the bundle:

    LICENSE.txt

About
-------
This Bundle is a [Sourcefabric z.ú.](https://github.com/sourcefabric) initiative.
