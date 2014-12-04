<html>
<head>
  <link rel="stylesheet" href="/bundles/newscoopgoogleeventsplugin/css/frontend.css">
</head>

<body>

<div id="nav">
   <div class="left-nav"><a href="{{ $prevPageUrl }}">Previous</a></div>
   <div class="center-nav"><span>found {{ $eventCount }} results</span></div>
   <div class="right-nav"><a href="{{ $nextPageUrl }}">Next</a></div>
</div>
<br class="clear">

<ul id="event-results-container">
{{ foreach $events as $event }}
    <li>
        <p><a href="/google-events/events/{{ $event->getId() }}">{{ $event->getSummary() }}</a></p>
        <p>Posted By: {{ $event->getCreatorEmail() }}</p>
        <p>On: {{ $event->getStart()|date_format:"Y-m-d" }}</p>
    </li>
{{ /foreach }}
</ul>

</body>
</html>
