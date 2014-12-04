<h4>google_event.tpl</h4>

<div>
  {{ $event->getId() }}
  <br />
  {{ $event->getDescription() }}
  <br />
  {{ $event->getStart()|date_format:"%Y-%m-%d %H:%M" }} -
  {{ $event->getEnd()|date_format:"%Y-%m-%d %H:%M"}}
  <br />
  {{ $event->getCreatorDisplayName() }}
  <br />
  {{ $event->getCreatorEmail() }}
  <br />
  {{ $event->getSummary() }}
  <br />
  {{ $event->getHtmlLink() }}
</div>
