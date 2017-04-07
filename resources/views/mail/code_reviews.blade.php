@component('mail::message')
# Das Code Review Report

@foreach($reviews as $review)
### Code reviews assigned to {{ $review['name'] }}
@foreach($review['pull_requests'] as $pullRequest)
- **{{ $pullRequest['user']['name'] }}** assigned [{{ $pullRequest['title'] }}]({{ $pullRequest['html_url'] }}) on {{ $pullRequest['head']['repo']['name'] }}
@endforeach

@endforeach
@endcomponent
