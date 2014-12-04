<h4>google_events.tpl</h4>

<p>output from $events set in controller</p>
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

<p>output from list_google_events smarty block</p>
{{ list_google_events length=30 }}
    {{ $event->getSummary() }}
    {{ $event->getStart()|date_format:"%Y-%m-%d %H:%M" }}
    {{ $event->getEnd()|date_format:"%Y-%m-%d %H:%M" }}
{{ /list_google_events }}

