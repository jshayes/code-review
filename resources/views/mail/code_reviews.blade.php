@component('mail::message')
# Daily Code Review Report

@foreach($reviewers as $reviewer => $pullRequests)
### Code reviews assigned to {{ $reviewer }}
@foreach($pullRequests as $pullRequest)
- **{{ $pullRequest['user']['login'] }}** assigned [{{ $pullRequest['title'] }}]({{ $pullRequest['html_url'] }})
@endforeach

@endforeach

@endcomponent
