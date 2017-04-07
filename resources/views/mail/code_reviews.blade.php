@component('mail::message')
@if(!empty($reviews))
# Das Code Review Report

@foreach($reviews as $review)
### Code reviews assigned to {{ $review['name'] }}
@foreach($review['pull_requests'] as $pullRequest)
- **{{ $pullRequest['user']['name'] }}** assigned [{{ $pullRequest['title'] }}]({{ $pullRequest['html_url'] }}) on {{ $pullRequest['head']['repo']['name'] }}
@endforeach

@endforeach
@else
Looks like there is nothing assigned for code review today.
@endif
@endcomponent
