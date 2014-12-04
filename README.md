NewscoopGoogleEventsPluginBundle
===================

This Newscoop Plugin adds smarty functions and Admin tools to enable you to ingest, manage, and display Google calendar events in Newscoop.

Google Events View
------------------------

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
Google Event View
------------------------

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

Google Events Search
------------------------

Note that this only searches the locally stored InstagramPhoto entities, it does NOT make a call to the Instagram Api

Provides endpoint **/google-events/search**, which takes the following params:

1. search - search string, matches against caption, username, tags, and locationName fields
2. perPage - number of records per page to send in the results
3. offset - the first record to start with (used for pagination)

Results are delivered to **_views/google_events_search_results.tpl**, if defined in your theme, or a default internal view is used.


List Google Evenets Smarty Block
------------------------

Provides a smarty block to list instagrams photos with a specfific hashtag.

Usage:
```smarty
{{ list_google_events length=30 }}
    {{ $event->getSummary() }}
    {{ $event->getStart()|date_format:"%Y-%m-%d %H:%M" }}
    {{ $event->getEnd()|date_format:"%Y-%m-%d %H:%M" }}
{{ /list_google_events }}
```
