@component('mail::message')
# Daily Code Review Report

@foreach($reviews as $review)
### Code reviews assigned to {{ $review['name'] }}
@foreach($review['pull_requests'] as $pullRequest)
- **{{ $pullRequest['user']['name'] }}** assigned [{{ $pullRequest['title'] }}]({{ $pullRequest['html_url'] }})
@endforeach

@endforeach
@endcomponent
